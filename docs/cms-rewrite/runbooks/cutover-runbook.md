# Cutover runbook

Step-by-step procedure for the bockavel cutover day. To be finalized at the start of Epic 11; rehearsed at least once on staging before prod.

## T-7 days

- [ ] Stakeholder go/no-go meeting; lock cutover date.
- [ ] Editorial freeze date communicated to content owners.
- [ ] Content owner UAT signoff received.
- [ ] All sprints 1–10 PRs merged and on `main`.

## T-48h

- [ ] Lower DNS TTL on bockavel domain to 300s (so the swap propagates fast).
- [ ] Final ETL dry-run against fresh prod snapshot — capture row counts.
- [ ] Backup legacy DB + uploaded files (3 copies: local, S3, off-site).
- [ ] Production env vars verified.
- [ ] SSL cert valid and renewing automatically.
- [ ] Email DNS (SPF, DKIM, DMARC) configured on new app.
- [ ] Sentry / error monitoring enabled in prod.

## T-24h

- [ ] Editorial freeze begins. Content owners stop editing.
- [ ] Communicate freeze in admin login banner on legacy.

## T-0 (cutover day)

1. [ ] Take final legacy DB snapshot (after freeze, no further edits expected).
2. [ ] Run final ETL → `bockavel_new` on prod.
3. [ ] Sanity-check counts vs. dry-run reconciliation report.
4. [ ] Smoke-test prod URL via local hosts file pointing at new server.
   - Public pages render
   - Login works for at least 3 representative members
   - Forms submit + email arrives
   - Restricted file download works (with + without correct group)
   - Admin (Twill) login + edit flow
5. [ ] Swap DNS A/AAAA record to new server.
6. [ ] Watch propagation (`dig`, multiple resolvers).
7. [ ] Verify 301 redirects for top-traffic legacy URLs.
8. [ ] Tail Sentry / Laravel logs.
9. [ ] Editorial team verifies a sample of pages + posts a new test.

## Hour 0–24 post-cutover

- [ ] Monitor Sentry; triage any errors.
- [ ] Watch 404 logs; add missing redirects.
- [ ] Respond to editorial / member reports.

## Week 1–2 post-cutover

- [ ] Legacy site stays online read-only at `legacy.bockavel.se` (or similar) as safety net.
- [ ] Continue 404 + redirect monitoring.
- [ ] Address feedback.

## T+14 days

- [ ] Decommission legacy site.
- [ ] Archive `bockavel_legacy` DB + file backups long-term.
- [ ] Restore DNS TTL to normal value.
- [ ] Post-mortem / retrospective.
