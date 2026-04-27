# Epic 10 — Data Migration (ETL)

**Sprint:** 6 (parallel with Epic 9)
**Goal:** complete prod data migration runbook tested on a snapshot.

Operational steps live in `runbooks/etl-runbook.md`. Field-level mapping in `specs/schema-mapping.md`.

## Checklist

- [ ] Build `php artisan migrate:from-pyrocms` orchestrator command
- [ ] Migrate users → members
- [ ] Migrate groups + memberships
- [ ] Migrate pages (with Lex transform)
- [ ] Migrate page chunks → blocks
- [ ] Migrate blog posts (with Lex transform)
- [ ] Migrate blog categories + tags
- [ ] Migrate comments
- [ ] Migrate files (move to disk, create RestrictedFile records as needed)
- [ ] Migrate ads (from legacy `bockavel_ads` Streams table → `Ad` + image disk transfer; preserve `created_by` → member relationship)
- [ ] Migrate ad categories (from legacy `ad_type` field values)
- [ ] Migrate news
- [ ] Migrate forms + submissions
- [ ] Migrate snippets
- [ ] Migrate calendar entries (legacy `calendar` Streams stream → `Calendar` module; preserve `page` browser linkage)
- [ ] Migrate sponsors (verify legacy storage form during audit — Streams stream, settings rows, or hardcoded — then migrate to `Sponsor` module; transfer logo files to public disk)
- [ ] Migrate email templates (legacy `default_email_templates` or equivalent → `EmailTemplate` module; **preserve slugs exactly** — call sites depend on them)
- [ ] Migrate settings + variables
- [ ] Build URL → 301 redirect map → `specs/url-redirects.md`
- [ ] Implement `RedirectController`
- [ ] Reconciliation report (counts, sample diffs vs. legacy)
- [ ] Dry-run validation against full prod snapshot
- [ ] ETL runbook → `runbooks/etl-runbook.md`
