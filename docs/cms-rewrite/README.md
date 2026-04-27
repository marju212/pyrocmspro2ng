# bockavel CMS rewrite — docs

Project-meta documentation for rewriting the `bockavel` site off PyroCMS 2.x onto Twill 3 + Livewire 3 + Tailwind. Other four sites in the multi-site stay on PyroCMS for now.

When the new app is scaffolded at `~/Herd/bockavel/`, copy this folder there as `~/Herd/bockavel/docs/`. It lives here on the `cms-rewrite-estimate` branch as the planning deliverable.

## Layout

- `decisions/` — architecture decision records. Start with `0001-twill-as-cms.md`.
- `epics/` — one file per work epic. Each is a tickable checklist; tick during execution.
- `specs/` — reference material gathered before / during execution (schema map, form audit, Lex audit, redirect map, theme design, member rules, theme audit).
- `runbooks/` — operational procedures (cutover, ETL, backups, fork patching).
- `00-setup.md` — Epic 1 setup runbook (operational steps to bootstrap the dev environment).

## Index

### Decisions
- [0001 — Twill as CMS](decisions/0001-twill-as-cms.md)

### Epics
- [00 — Spikes & Audits](epics/00-spikes-audits.md)
- [01 — Foundation & Setup](epics/01-foundation.md)
- [02 — Twill Modules](epics/02-twill-modules.md)
- [03 — Editorial Blocks](epics/03-editorial-blocks.md)
- [04 — Member System & Auth](epics/04-member-system.md)
- [05 — Restricted Content](epics/05-restricted-content.md)
- [06 — Custom Logic](epics/06-custom-logic.md)
- [07 — Public Theme (Modernized Rebuild)](epics/07-public-theme.md)
- [08 — PyroForms Parity](epics/08-pyroforms-parity.md)
- [09 — Lex Content Migration](epics/09-lex-migration.md)
- [10 — Data Migration (ETL)](epics/10-data-etl.md)
- [11 — QA & Cutover](epics/11-qa-cutover.md)
- [12 — Paid Memberships (optional, Phase 2)](epics/12-paid-memberships.md)

### Specs
- [Schema mapping](specs/schema-mapping.md) — legacy table → Twill module/field map
- [PyroForms audit](specs/pyroforms-spec.md) — every prod form audited
- [Member rules](specs/member-rules.md) — group / profile logic from legacy
- [URL → 301 redirect map](specs/url-redirects.md)
- [Lex tag audit](specs/lex-tag-audit.md) — Lex tags in body content + target block type
- [Email templates](specs/email-templates.md) — transactional email catalog + Mailable-by-slug architecture
- [Theme design](specs/theme-design.md) — modernized design direction
- [Theme audit](specs/theme-audit.md) — inventory of legacy theme views

### Runbooks
- [Cutover runbook](runbooks/cutover-runbook.md)
- [Twill fork patch workflow](runbooks/twill-fork-patch.md)
- [Backup + restore](runbooks/backup-restore.md)
- [ETL runbook](runbooks/etl-runbook.md)

## How to use

1. Read `decisions/0001-twill-as-cms.md` for full plan + rationale.
2. Pick the active epic; work the checklist top-to-bottom; tick items as PRs merge.
3. Specs are filled in *before* their consuming epic runs (e.g. `lex-tag-audit.md` before Epic 9).
4. Runbooks are filled in *during* their epic and used at cutover.
