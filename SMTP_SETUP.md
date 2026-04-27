# SMTP setup — Loopia DNS + SMTP2GO

End-to-end setup for transactional mail on `svenskagetavelsforbundet.se`,
where the domain's DNS is hosted at Loopia and outbound mail is sent via
SMTP2GO. The PyroCMS code side (env-driven SMTP config + STARTTLS) is
already in place; this document only covers the DNS work needed for
inbox placement.

## Why this is needed

Mail flow already works — `MY_Email` reads the `MAIL_*` keys from `.env`
(see `.env.development.example`) and routes through SMTP2GO with
STARTTLS. Test sends are accepted and delivered.

What's missing is **sender authentication** — the DNS records that prove
to receiving servers (Gmail / Microsoft 365 / Yahoo / etc.) that mail
claiming to come from `svenskagetavelsforbundet.se` is actually
authorized. Without these records, increasing volumes of legitimate
mail get downgraded to spam, and from 2024 onwards Gmail and Yahoo
enforce these requirements strictly for any domain sending more than a
trickle of messages per day.

## Current DNS state (as observed 2026-04-27)

```
SPF:    TWO records published (RFC violation — multiple SPF causes PermError)
        1) v=spf1 include:spf.loopia.se -all
        2) v=spf1 include:_spf.subdomain.svenskagetavelsforbundet.se ~all
                  ↑ resolves to nothing — dead include
DKIM:   no selectors found
DMARC:  not published
MX:     mailcluster.loopia.se / mail2.loopia.se  (Loopia receives mail)
NS:     ns1.loopia.se / ns2.loopia.se            (Loopia hosts DNS)
```

## Step 1 — Generate DKIM in SMTP2GO

1. Log in at https://app.smtp2go.com/
2. **Settings → Sender Domains**
3. Add `svenskagetavelsforbundet.se` if not already listed; otherwise
   open the existing entry.
4. SMTP2GO will display 2–3 records to publish. They follow one of two
   patterns; copy whichever you see verbatim:
   - **CNAME pattern (most common):**
     ```
     s1._domainkey.svenskagetavelsforbundet.se  →  s1._domainkey.smtp2go.net
     s2._domainkey.svenskagetavelsforbundet.se  →  s2._domainkey.smtp2go.net
     ```
     (selector names `s1` / `s2` vary per account — use what SMTP2GO shows)
   - **TXT pattern (older accounts):**
     ```
     k1._domainkey.svenskagetavelsforbundet.se  TXT
       "v=DKIM1; k=rsa; p=MIIBIjANBg…"  (long base64 key)
     ```
5. Leave the SMTP2GO tab open — you'll click **Verify** here after
   Loopia propagates.

## Step 2 — Loopia DNS edits

Log into **Loopia Kundzon → DNS-redigerare** and select
`svenskagetavelsforbundet.se`.

### 2a. Fix SPF (delete one, replace the other)

Two TXT records currently exist at the root. Replace them with one.

| Action  | Subdomän       | Typ | Värde |
|---------|----------------|-----|-------|
| DELETE  | `@` (or empty) | TXT | `v=spf1 include:_spf.subdomain.svenskagetavelsforbundet.se ~all` |
| REPLACE | `@` (or empty) | TXT | `v=spf1 include:spf.loopia.se include:spf.smtp2go.com ~all` |

The replacement keeps Loopia's outbound mail authorized (the MX is at
Loopia) AND adds SMTP2GO. Use `~all` (softfail) initially; tighten to
`-all` after a few weeks of clean DMARC reports.

### 2b. Add DKIM (records from Step 1)

For each record SMTP2GO showed you. **In Loopia, the "Subdomän" field
takes only the part before `svenskagetavelsforbundet.se`** — for
`s1._domainkey.svenskagetavelsforbundet.se` enter just `s1._domainkey`.

CNAME records (most common):

| Subdomän        | Typ   | Värde                            | TTL  |
|-----------------|-------|----------------------------------|------|
| `s1._domainkey` | CNAME | `s1._domainkey.smtp2go.net.`     | 3600 |
| `s2._domainkey` | CNAME | `s2._domainkey.smtp2go.net.`     | 3600 |

(Substitute the actual selectors and target hostname SMTP2GO gave you.
The trailing dot is important if Loopia accepts it; drop it if Loopia's
editor complains.)

If SMTP2GO gave TXT records instead, publish those at the same
subdomains (`k1._domainkey`, etc.) with the full `v=DKIM1; k=rsa; p=…`
value as a single TXT string.

### 2c. Add DMARC

| Subdomän | Typ | Värde | TTL |
|----------|-----|-------|-----|
| `_dmarc` | TXT | `v=DMARC1; p=none; rua=mailto:webmaster@svenskagetavelsforbundet.se; pct=100; adkim=r; aspf=r` | 3600 |

`p=none` = monitor only, no enforcement. Aggregate XML reports arrive
daily at the `rua=` address from Gmail / Yahoo / Microsoft. Read those
for 2–4 weeks; once every legitimate sender is reporting `pass`,
tighten:

1. `p=quarantine; pct=10` — start enforcing on 10% of failing mail
2. `p=quarantine; pct=100` — full quarantine
3. `p=reject` — reject failing mail outright

## Step 3 — Verify after propagation

Loopia DNS typically propagates in 5–15 minutes; up to 4 hours in rare
cases. Once records are live:

```bash
dig +short svenskagetavelsforbundet.se TXT
dig +short _dmarc.svenskagetavelsforbundet.se TXT
dig +short s1._domainkey.svenskagetavelsforbundet.se CNAME    # adjust selector
dig +short s2._domainkey.svenskagetavelsforbundet.se CNAME
```

Expected output:

- One SPF TXT containing `include:spf.smtp2go.com`
- One DMARC TXT starting with `v=DMARC1`
- One CNAME per DKIM selector resolving to `*.smtp2go.net`

Then in SMTP2GO **Sender Domains** click **Verify**. The domain status
flips from "Pending" to "Verified" once DKIM is reachable.

## Step 4 — End-to-end mail-auth test

After SMTP2GO marks the domain verified, send a test through the live
PyroCMS mail path (admin → Settings → Email → Send test mail, or any
password-reset trigger). On the receiving end (Gmail in particular)
inspect message headers; you should see:

```
Authentication-Results: mx.google.com;
       dkim=pass header.i=@svenskagetavelsforbundet.se header.s=s1
       spf=pass smtp.mailfrom=webmaster@svenskagetavelsforbundet.se
       dmarc=pass header.from=svenskagetavelsforbundet.se
```

All three on `pass` = inbox-eligible from now on.

## Action checklist

- [ ] SMTP2GO dashboard — copy DKIM record values
- [ ] Loopia — delete dangling `_spf.subdomain…` SPF record
- [ ] Loopia — replace remaining SPF with merged Loopia + SMTP2GO record
- [ ] Loopia — add DKIM CNAMEs (or TXT) from SMTP2GO
- [ ] Loopia — add `_dmarc` TXT with `p=none`
- [ ] Wait 15 min, run `dig` checks
- [ ] SMTP2GO — click **Verify** on the sender domain
- [ ] Send a real test mail and inspect Authentication-Results header
- [ ] After 2–4 weeks of clean DMARC reports, tighten `p=quarantine` then `p=reject`

## Code-side changes already shipped

These are already in the repo and need no manual action:

- `system/cms/libraries/MY_Email.php` — env-driven SMTP config with
  `smtp_crypto = 'tls'` (STARTTLS), 15 s timeout, recipient validation
- `.env.development.example` / `.env.production.example` — documented
  `MAIL_SMTP_CRYPTO`, `MAIL_SMTP_TIMEOUT`, and the rest of the `MAIL_*`
  override keys
- The `.env` on this machine already contains the SMTP2GO credentials
  and `MAIL_SMTP_CRYPTO=tls`; deployments need the same values populated
  out-of-band (`.env` is gitignored)

## Reference — what each record does

| Record | Purpose | Failure mode without it |
|--------|---------|-------------------------|
| **SPF** | Lists IPs/hosts authorized to send mail using this domain | Receivers can't validate origin → softfail spam scoring |
| **DKIM** | Cryptographically signs outbound mail; receiver verifies via published public key | No tamper-proof signature → biggest deliverability hit, especially on Gmail |
| **DMARC** | Tells receivers what to do when SPF/DKIM fail, and where to report | Without it, receivers apply their own (typically harsh) heuristics, and you get no visibility into who's spoofing your domain |
