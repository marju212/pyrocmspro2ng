# Epic 11 — QA & Cutover

**Sprint:** 7
**Goal:** site shipped, DNS swapped, legacy retired.

Cutover-day procedure: `runbooks/cutover-runbook.md`.

## Checklist

### QA
- [ ] Cross-browser smoke test (Safari, Firefox, Chrome, Edge)
- [ ] Mobile device test (iOS Safari, Android Chrome)
- [ ] Full Lighthouse audit (perf + a11y + SEO + best practices)
- [ ] Security review (CSP, CSRF, file upload validation, XSS, SQL injection on raw queries)
- [ ] Content owner UAT (1-week window)
- [ ] Final design review

### Pre-cutover checklist
- [ ] DNS TTL lowered to 300s (48h prior)
- [ ] Editorial freeze announcement
- [ ] Final ETL dry-run on fresh prod snapshot
- [ ] Backup of legacy DB + uploaded files
- [ ] Production env vars verified
- [ ] Email DNS records (SPF, DKIM, DMARC) configured
- [ ] SSL certificate valid + auto-renewal

### Cutover day
- [ ] Final ETL against frozen snapshot
- [ ] Smoke test on prod URL via hosts file
- [ ] DNS swap
- [ ] Verify 301 redirects
- [ ] Monitor Sentry / logs
- [ ] Editorial team verification

### Post-cutover
- [ ] Keep legacy site read-only for 2 weeks
- [ ] Monitor 404 logs for missed redirects
- [ ] Address feedback
- [ ] Decommission legacy site
- [ ] Cutover runbook → `runbooks/cutover-runbook.md`
