# Epic 2 — Twill Modules

**Sprints:** 2 (Stage 2a) + 3 partial (Stage 2b) — parallel workspaces within each stage.
**Goal:** all content-type modules scaffolded with admin + frontend rendering.

**Sequencing:** Build the **Page** module first as the template (uses the Sprint 0 spike output as a starting point). Then fan out:

- **Stage 2a — foundation modules (no deps):** Sprint 2, parallel after Page lands. Required by other modules + by Epic 4 auth.
- **Stage 2b — content modules (depend on 2a):** Sprint 3, parallel. Each references a 2a module via browser / relation.

## Stage 2a — Page first, then foundation modules

- [ ] **Page module** (template — build first; Sprint 0 spike output is the starting point)
  - [ ] `php artisan twill:make:module Page`
  - [ ] Blueprint fields: title, slug, body (blocks), visibility, required_groups, parent_id, SEO
  - [ ] Browser fields for related Pages, Files
  - [ ] Slug-based hierarchical routing
  - [ ] Public Blade view rendering blocks
  - [ ] Pest test: CRUD + access control
  - [ ] Document field mapping in `specs/schema-mapping.md`
- [ ] **Member module** (Twill module + `Member` model extending Authenticatable; group relation). **Required by Epic 4 auth in Sprint 3.**
- [ ] **Group module** (members + permissions config)
- [ ] **Setting module** (or use spatie/laravel-settings)
- [ ] **Snippet module**
- [ ] **EmailTemplate module** — slug-resolved transactional email templates. Fields: slug (immutable), subject, body, from_name, from_email; multi-locale. See `specs/email-templates.md`. **Required by Epic 4 (`new_member`, `forgotten_password`, `password_changed`, `activation`), Epic 6 (ad emails), Epic 8 (form emails).**
- [ ] **AdCategory module** (CRUD; required by Ad module in Stage 2b)
- [ ] **News module** (no module deps)
- [ ] **Form module** (definition + JSON field schema; no module deps — `FormSubmission` references it from Stage 2b)

## Stage 2b — content modules (depend on 2a)

- [ ] **BlogPost module** (mirror Page pattern + categories + tags + comments)
- [ ] **FormSubmission module** (entries with JSON payload — refs Form)
- [ ] **Ad module** (refs Member, AdCategory)
- [ ] **Calendar module** — page-attached events (refs Page). Fields: title, date (datetime), page browser, body, optional location. Frontend agenda partial queries `WHERE date > now()`. See `specs/schema-mapping.md` for legacy field verification.
- [ ] **Sponsor module** — sidebar sponsor placements (uses Twill media). Fields: logo (media), name, link, sort_order, sidebar tag (`left | right | both`). Verify legacy storage form during ETL audit.
- [ ] **RestrictedFile module** (private disk + browser to Group — refs Group)
