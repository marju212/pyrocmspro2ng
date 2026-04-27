# Email templates

Admin-editable transactional emails. Replaces PyroCMS's slug-based `Events::trigger('email', ['slug' => '...'])` mechanism (see `legacy/addons/bockavel/modules/member/events.php` for the existing pattern).

## Architecture

- **Storage:** `EmailTemplate` Twill module — fields: `slug` (immutable, unique), `subject`, `body` (rich text or markdown), `from_name` (optional override), `from_email` (optional override), `available_variables` (info-only field documenting interpolatable variables for editors).
- **Resolution:** `App\Mail\TemplatedMail` — a single Mailable that loads an `EmailTemplate` by slug, interpolates variables, and renders.
- **Sending:** `Mail::to($recipient)->send(new TemplatedMail('new_member', $variables))`.
- **Variables:** Blade-syntax inside template body (e.g., `Hej {{ $first_name }}`). Each template has a registered variable schema in code so missing-variable bugs surface at boot, not first send.
- **Multi-locale:** Twill multi-locale fields per template — render in recipient's locale at send time.
- **Send via queue** (Laravel mail queue) so admin actions never block on SMTP.

## Templates to migrate

Audit the legacy `default_email_templates` (or equivalent) table during Epic 10 ETL plus every `Events::trigger('email', ['slug' => ...])` call site in `legacy/`. Preserve slugs **exactly** so any code referencing them keeps working.

- [ ] `new_member` — registration confirmation (triggered in `addons/bockavel/modules/member/events.php`)
- [ ] `activation` — email verification (Ion Auth default)
- [ ] `forgotten_password` — password reset request
- [ ] `password_changed` — confirmation after password reset
- [ ] `new_comment` — admin notification on comment posted
- [ ] `comment_approved` — author notification when comment approved
- [ ] `pyroforms_notification` — generic form-submission notification to recipients (per-form override slug allowed)
- [ ] `pyroforms_response` — auto-reply to form submitter (optional per form)
- [ ] `ad_contact_seller` — visitor → ad owner contact-form delivery
- [ ] `ad_published` — admin → member when an ad goes live
- [ ] `ad_expired` — member when an ad nears / passes expiration
- [ ] `backup_success` / `backup_failed` — ops notifications (or rely on spatie/laravel-backup's built-in)
- [ ] [enumerate the rest from legacy audit]

## Twill admin UX

- Templates seeded at install — slug field locked once seeded (deletes break send-by-slug).
- Per-template fields: subject, body, optional `from_name` / `from_email`, optional `reply_to`.
- Multi-locale tab per template.
- "Send test" button → sends to logged-in admin's email with sample variables.
- Show variable schema next to the body editor so editors know what's available.

## Implementation notes

- Variable schema lives in a PHP registry (e.g., `App\Mail\TemplateRegistry::variablesFor('new_member')`); call sites validate against it before send.
- Failed sends: retry via Laravel queue; surface failures in admin notifications.
- Don't ship a "create new template" UI — adding a new template = code change (registers slug + variable schema) + a deploy. Editors only edit existing templates.
- Spam compliance: transactional templates are exempt from unsubscribe rules under most regs but include physical sender address. Non-transactional ones (newsletters, marketing) get an unsubscribe footer — out of scope here.
