# ADR 0001 — Twill 3 (Laravel) + Livewire as bockavel's CMS

**Status:** Accepted
**Date:** 2026-04-27
**Decision owner:** marcus@junstrom.se

## Context

bockavel is one of five sites in a PyroCMS 2.x multi-site installation. The goal: rewrite **only bockavel** onto a modern PHP stack at content + functional parity, with a **modernized theme** (same structural IA, fresh design — not pixel-faithful to style334). The other 4 sites stay on PyroCMS for now.

## Decision

**Twill 3.x (Laravel-native CMS) + Livewire 3 + Tailwind, with `legacy/` PyroCMS as read-only reference.**

### Why this stack

- **Laravel ecosystem = long-term safety.** Even if Twill upstream slows, the underlying app is plain Laravel 11/12 — large dev pool, modern tooling, well-understood patterns, low risk of dead-end.
- **Free, MIT-licensed.** No annual fees.
- **Forkable.** When you hit a bug Twill upstream hasn't fixed, fork the repo and have Claude Code patch it; deploy from your fork via Composer VCS.
- **Blocks system fits the Lex problem cleanly.** Editorial Lex tags in WYSIWYG content migrate into typed blocks instead of needing a runtime parser.
- **Livewire frontend is fully native** — Twill is a CMS toolkit, not a frontend framework. Public site is plain Laravel + Blade + Livewire.

### Twill maintenance status (verified 2026-04-27)

Repo is alive but in maintenance mode. Last commit ~2 weeks ago, last release 3.5.3 in Jan 2026 (~3 mo gap). Recent activity: dependency bumps, security patches (Axios supply-chain mitigation merged within days), small bug fixes. AREA 17 (creators) keeps the lights on for their commercial work. Acceptable for bockavel's scope; not a high-velocity project.

## Scope inventory (bockavel)

- **Theme:** `addons/bockavel/themes/style334/` — ~74 view files, LESS + Bootstrap + jQuery 2.1.3
- **Site-specific modules:** `addons/bockavel/modules/{ad,member,news}/` — ~1.5k PHP lines:
  - `ad` — **member-driven classifieds** (members post items for sale: title, type, price, location, images; visitors contact seller; member-only "Mina Annonser" page; owner-only edit/delete)
  - `member` — member directory with group filtering
  - `news` — admin-published news listing
- **Site-specific features defined outside the modules dir:**
  - `calendar` — page-attached events. Streams stream queried by `addons/bockavel/plugins/helpers.php` (`isPageInCalendar`, `calendarNav`); rendered on home via `partials/home/agenda.html`. Migrates to a `Calendar` Twill module + `agenda_block`.
  - `sponsors` — sidebar sponsor placements (left/right) on content pages. Storage form to be confirmed during audit; migrates to a `Sponsor` Twill module.
  - Transactional emails — admin-editable templates resolved by slug via `Events::trigger('email', ['slug' => ...])` (e.g., `new_member`, `forgotten_password`, `pyroforms_notification`, `ad_contact_seller`). Migrates to an `EmailTemplate` Twill module + `App\Mail\TemplatedMail` resolver. See `specs/email-templates.md`.
  - Swedish account-flow URLs — `/registrerad/{id}` (post-registration confirmation) and `/valkommen` (post-activation welcome) wired into the Ion Auth flow via `addons/bockavel/modules/member/events.php`. Preserved verbatim in the new app.
- **Shared addons in use:** `api`, `backups`, `pyroforms`, `secure`, `snippets`
- **Custom Streams field types:** `phone`, `page`, `regex`, `textarea_limited`, `multiple_images`
- **Languages:** Swedish + English
- **Database:** 1 site DB (one prefix in the multi-site MySQL)
- **Core CMS surface to replace:** Pages, Blog, Files, Users/Groups/Permissions, Navigation, Templates/Snippets, Widgets, Search, Sitemap, Redirects, Settings/Variables, Contact, Keywords, Maintenance, Comments, WYSIWYG, Email templates (transactional).
- **Integrations:** S3, Dropbox, Email (via backups + forms). No payments / e-commerce / social login / search service / CRM.

## Stack

### Backend / CMS

| Concern | Package |
|---|---|
| CMS / admin | Twill 3.x |
| Auth + roles | Twill built-in |
| Media | Twill Media Library (Imgix-compatible) |
| Backups | spatie/laravel-backup |
| Sitemap / redirects | spatie/laravel-sitemap, spatie/laravel-redirects |
| Search | Laravel Scout (driver TBD: database / Meilisearch) |
| API | Laravel Sanctum (for the legacy `api` module replacement) |
| Translations | Twill multi-locale + Laravel localization |
| Forms (PyroForms parity) | Custom Twill module + Livewire form components |

### Frontend / public site

- Blade + Livewire 3 + Alpine
- Tailwind + Vite

### Infrastructure

- Twill installed via Composer from a private fork (`junstrom/twill` or similar) so bug-patches are immediate
- Standard Laravel deploy (Laravel Forge / Ploi / Envoyer / plain SSH — TBD)

## Effort breakdown (single senior dev)

| Phase | Weeks |
|---|---|
| Spikes & audits (Twill module / theme / pages-ETL+Lex / PyroForms; specs filled) | ~1 |
| Foundation (Laravel + Twill + Livewire + Tailwind, Herd setup, fork created, CI) | ~1 |
| Twill modules — Stage 2a (Page, Member, Group, Setting, Snippet, EmailTemplate, AdCategory, News, Form) | ~1 |
| Twill modules — Stage 2b (BlogPost, FormSubmission, Ad, Calendar, Sponsor, RestrictedFile) | ~1 |
| Custom blocks — base + module-dependent (Stages 3a + 3b) | 3 – 5 d |
| Custom logic (member directory, member classifieds CRUD + listing + contact, news listing, API key auth + logging) | ~2 |
| Public theme — modernized rebuild (Blade + Livewire + Tailwind) | 2 – 3 |
| PyroForms parity (form schema + Livewire form rendering + email routing + uploads) | ~1 |
| Data migration (PyroCMS DB → Twill modules, Lex tags → blocks) | 1.5 – 2 |
| QA + UAT + cutover | 2 |
| **Total** | **~12.5 – 15 weeks** |

**Solo dev: ~3 – 3.75 months. With Claude Code parallel workspaces: ~9 – 10 weeks elapsed.** Two devs in parallel: ~5 – 6 weeks elapsed.

## Project structure

```
~/Herd/bockavel/                     ← Claude Code workspace root
├── .claude/
│   └── settings.json                ← per-project permissions, hooks
├── CLAUDE.md                        ← conventions, package list, migration map
├── README.md
├── legacy/                          ← PyroCMS, git submodule of existing repo (read-only reference)
├── app/                             ← new Laravel + Twill project
│   ├── app/
│   ├── routes/
│   ├── resources/
│   └── ...
├── docs/                            ← specs Claude reads (this folder, copied here)
├── tools/                           ← ETL scripts (Artisan), screenshot diffs
└── db/
    └── legacy-snapshot.sql.gz       ← refreshed weekly from prod
```

**Single git repo** (umbrella) with `legacy/` as a submodule. One Claude Code workspace sees both codebases — agents grep `legacy/` for behaviour while writing in `app/`.

### Twill fork strategy

```bash
# Fork area17/twill on GitHub → e.g. junstrom/twill
# In app/composer.json:
"repositories": [{
    "type": "vcs",
    "url": "https://github.com/junstrom/twill"
}],
"require": {
    "area17/twill": "dev-bockavel-main"
}
```

Workflow when a Twill bug is hit:

1. Branch on the fork
2. Have Claude Code reproduce + write failing test + fix
3. Tag internal version
4. Bump composer in `app/`
5. Optionally upstream as PR to area17/twill

See `runbooks/twill-fork-patch.md`.

### Herd configuration

| Site | Path | PHP | Domain |
|---|---|---|---|
| Legacy | `~/Herd/bockavel/legacy` | 7.4 | `bockavel-legacy.test` |
| New app | `~/Herd/bockavel/app` | 8.3 | `bockavel.test` |

### Local databases (Herd MySQL or DBngin)

| DB | Purpose |
|---|---|
| `bockavel_legacy` | Restored from prod snapshot. Refreshed weekly. |
| `bockavel_new` | Target DB. ETL'd from `bockavel_legacy` by scripts in `tools/`. |

### Migration / cutover strategy

**Big-bang.** Build to staging. 24–48h editorial freeze. Run final ETL against frozen snapshot. Swap DNS. Keep legacy site available read-only for ~2 weeks as safety net. See `runbooks/cutover-runbook.md`.

## Lex content migration → Twill blocks

Lex tags live in two places, handled differently:

**1. Theme template tags** (`{{ navigation:links }}`, `{{ pages:children }}`, etc.) — gone, replaced by Blade includes / Livewire components during the theme rebuild. No data migration concern.

**2. Lex tags embedded in editorial content** (page bodies, blog posts, page chunks, snippets) — migrate into Twill blocks during ETL.

| Lex tag pattern | Migration target |
|---|---|
| `{{ pages:link id="X" }}` | Static-resolve to `<a href="/slug">title</a>` in a text block |
| `{{ settings:* }}`, `{{ variables:* }}` | Static-resolve to literal value in text block |
| `{{ pyroforms:form id="X" }}` | Convert to typed `form_block` referencing migrated form |
| `{{ snippets:foo }}` | Inline snippet content, OR convert to typed `snippet_block` |
| `{{ blog:recent }}` and similar dynamic listings | Convert to typed `recent_posts_block` rendered via Livewire |
| Plain HTML between tags | Wraps into `text_block` |

**Audit first** (1 day before any migration code is written) — see `specs/lex-tag-audit.md`:

```sql
SELECT body FROM bockavel_pages WHERE body LIKE '%{{%';
SELECT body FROM bockavel_blog WHERE body LIKE '%{{%';
SELECT body FROM bockavel_page_chunks WHERE body LIKE '%{{%';
```

## Claude Code workflow

- **Sequential Sprint 0 spikes + parallel audits (~1 wk):** Twill module spike (Page, throwaway branch — the lessons feed Stage 2a's real Page build), theme spike (home page), pages-ETL+Lex spike, PyroForms audit. Spec-filling audits run as parallel document-only workspaces.
- **Sequential foundation (1 workspace, ~1 wk):** scaffold Laravel + Twill + Livewire + Tailwind, write CLAUDE.md, set up `legacy/` submodule, CI + Pint + PHPStan + Pest. Apply Sprint 0 spike lessons.
- **Parallel Stage 2a + 3a (~1 wk wall-clock, up to ~9 workspaces):** Page module first (~half-day with spike output as starting point), then fan out — Member, Group, Setting, Snippet, EmailTemplate, AdCategory, News, Form modules + text/image/gallery/divider/video/cta blocks.
- **Parallel Stage 2b + 3b + Epic 4 + Epic 5 (~1.5 wk wall-clock, up to ~12 workspaces):** content modules (BlogPost, FormSubmission, Ad, Calendar, Sponsor, RestrictedFile) + module-dependent blocks (form, snippet, file_download, restricted_section, agenda, sponsor_strip) + member auth + Swedish URLs + transactional email wiring + visibility middleware + restricted-file policy.
- **Parallel custom logic + theme kickoff (~1 wk):** API key auth + logging, backups integration, member directory, classifieds CRUD; in parallel, theme design system + base layout + home page.
- **Parallel theme continued + PyroForms (~1.5 wk):** one workspace per page-template family (content, blog, news, ad, member, account, search) + dynamic-form Livewire component.
- **Sequential migration + cutover (1 workspace, ~3 wk):** Lex transforms → entity ETL → redirects → QA → UAT → cutover.

**Per-workspace prompts written ahead** — each slice has a short brief: "Build X mirroring Page module patterns, schema in `docs/specs/schema-mapping.md`, parity targets in `legacy/...`". Generic prompts produce generic work.

**Strict PR-per-slice + human review.** Auto-merge is the failure mode that turns this into a 4-month project instead of a 6-week one.

## What needs human ownership

- Schema decisions (Streams → Twill modules + blocks): one design pass before parallel work → `specs/schema-mapping.md`
- PyroForms behaviour spec: audit every production form (fields, validation, recipients, attachments) → `specs/pyroforms-spec.md`
- Member group rules: read `legacy/addons/bockavel/modules/member/` once, document → `specs/member-rules.md`
- Lex tag audit: SQL run + classification → `specs/lex-tag-audit.md`
- Modernized theme design direction (mood board, type, color, components) → `specs/theme-design.md`
- URL → redirect map → `specs/url-redirects.md`
- Cutover plan + DNS → `runbooks/cutover-runbook.md`

## Member auth + restricted content

**Two-guard auth pattern.** Twill admin (`twill_users` guard) and frontend members (`web` guard with custom `Member` model) are fully separated — different tables, different login routes, different sessions. Editorial admins never overlap with members.

**Member data model:**
- `members` table (replaces legacy `default_users` + `default_profiles`)
- `groups` table + `group_member` pivot (replaces `member_groups`)
- Twill module manages groups in admin

**Restricted pages:**
- `Page` module fields: `visibility` (enum `public | members | groups`) + `required_groups` (browser to Group)
- `EnforcePageAccess` middleware on the public page route enforces it
- Mid-page granularity via a `restricted_section` block that conditionally renders children based on current member

**Restricted file downloads — three-layer pattern (never skip any layer):**
1. Files stored on a **private disk** (`storage/app/private` or private S3 bucket), not under `public/`
2. Served via Laravel route + controller (`/downloads/{file:slug}`) protected by `auth:web` middleware, never linked directly
3. `RestrictedFilePolicy::download()` checks the member's groups against the file's `required_groups`

**Twill `RestrictedFile` module** — title, description, file upload (private disk), browser field to Group. Editors upload + tag groups; frontend renders auth-protected download link.

**Public images** (hero photos, blog thumbnails, etc.) keep using Twill's standard media library on the public disk — that pipeline unchanged.

**Login/register/password-reset:** Livewire components against the `web` guard. ~1 day.

| Sub-task | Days |
|---|---|
| Two-guard auth + member model + groups + Twill group module | ~1 |
| Login / register / password-reset Livewire | ~1 |
| Page access middleware + Page model fields | ~0.5 |
| RestrictedFile Twill module + Policy + download route | ~1 |
| `restricted_section` block | ~0.5 |
| **Total** | **~4 days** (already inside the ~10–13 wk total) |

## Paid memberships (optional Phase 2 — adds ~1 wk if included in Phase 1)

Stack: **Laravel Cashier** (free, MIT, official Laravel package). Two payment provider options:

- **Cashier + Stripe** — direct integration. You handle VAT (use Stripe Tax for automation, ~0.5% extra).
- **Cashier + Paddle** — Paddle is Merchant of Record, handles EU VAT for you. Slightly higher fees, zero VAT/invoicing burden. Often the right call for a small Swedish business serving mixed-EU customers.

**Architecture extends existing access model.** `Member` model gets the `Billable` trait. `Page->visibility` enum gains a `subscription` option with a `required_plan` reference. `EnforcePageAccess` middleware adds one extra check: `$member->subscribedToPrice($page->required_plan)`. `RestrictedFilePolicy::download()` adds the same check. No restructuring.

**Member account page (Livewire) → "Manage subscription" → Cashier's `redirectToBillingPortal()`** = Stripe-hosted billing UI. No custom subscription/payment-method UI needed.

**Webhooks** handled automatically by Cashier's built-in controller. Configure URL in Stripe, exclude from CSRF, done.

| Task | Days |
|---|---|
| Cashier install + `Billable` trait + migrations | 0.5 |
| Plans config + Livewire pricing page + Stripe Checkout flow | 1.5 |
| Webhook configuration + test mode validation | 0.5 |
| Member account dashboard (status + billing portal link) | 0.5 |
| Extend page/file access for subscription tiers | 0.5 |
| Trial / grace-period logic (if used) | 0.5 |
| Edge cases (failed payments, cancellations, downgrades) + testing | 1.0 |
| **Total** | **~5 days** |

**Non-technical work that dominates real cost** (not in dev estimate): pricing model decisions, VAT/invoicing integration with Swedish accounting (Fortnox/Visma — ~1 wk separate project if needed), terms/refund/GDPR policies, support operations, dunning strategy.

**Recommended sequencing:**

1. **Phase 1: bockavel rebuild without paid memberships.** Architecture supports adding them later with no rework.
2. **Phase 2: add Cashier in a feature branch** when business case is validated.

If paid memberships are part of the launch vision, add **~1 week to the total estimate** (~11–14 wk solo / ~7–9 wk with Claude Code).

## Risk multipliers

1. **Twill upstream bug surfaces in critical path** — fork strategy mitigates, but each patch is 0.5–1 d Claude Code work + testing
2. **PyroForms feature parity** — conditional logic / multi-step / unusual fields beyond the 5 known: +0.5–1 wk
3. **Member group logic** — non-trivial profile rules: +0.5–1 wk if surprises
4. **Modernized theme — design iteration cycles** — already baked in (1.3–1.5×); >2 design rounds adds 1–2 wk
5. **SEO continuity** — preserving exact URLs + 301 mapping is finicky; budgeted in migration but worth a redirect-map review
6. **Lex tag audit reveals exotic usage** — if many distinct dynamic tag types exist in content, +0.5–1 wk
7. **Stakeholder availability for content-owner UAT** — most common schedule killer (non-technical)

## Verification (Sprint 0 — locks the estimate to ±1 wk)

Sprint 0 is dedicated to spikes + audits before any production code is written. Detailed checklist in `epics/00-spikes-audits.md`. The four spikes:

1. **Twill module spike** — build the `Page` module end-to-end (Twill `make:module`, blueprint/blocks, browser field, Livewire-rendered frontend page). Time-box: 3 d. Validates the foundation + per-module estimate. Output reused as Epic 2's Page module starting point.
2. **Theme spike** — rebuild bockavel home page with real modernized design direction in Blade + Tailwind + Livewire. Time-box: 4 d. Validates the 2–3 wk theme estimate.
3. **Data migration spike** — write ETL for `pages` only, including a Lex-to-blocks transform for any tags in body content, run on a DB copy. Time-box: 2 d. Validates migration estimate + uncovers Lex audit findings early.
4. **PyroForms audit** — list every production form with field types, validation, recipients, file uploads. Time-box: 1 d.

End of Sprint 0: a go/no-go review against this ADR. If spikes blow up beyond their time-boxes, replan before Sprint 2 fans out parallel workspaces.

## Files referenced

- `addons/bockavel/modules/{ad,member,news}/` — site-specific custom code
- `addons/bockavel/themes/style334/` — public theme (modernized rebuild target)
- `addons/shared_addons/modules/{api,backups,pyroforms,secure,snippets}/` — shared modules in use
- `addons/shared_addons/field_types/{phone,page,regex,textarea_limited,multiple_images}/` — 5 custom field types (mostly mapped to Twill native browser/repeater/media fields)
- `system/cms/migrations/` — 100 core migrations (read-only reference for new schema design)

## Sprint sequencing (1-week sprints, 1 dev + Claude Code)

Sprints are dependency-aware: each row's epics are unblocked by the prior rows' outputs. Within a sprint, items fan out across parallel workspaces.

| Sprint | Focus | Epics / Activities |
|---|---|---|
| **0** | Spikes & audits | Epic 0 — four spikes (Twill module / theme / pages-ETL+Lex / PyroForms audit) + spec fill-in (schema, member rules, Lex audit, theme audit, theme design, URL inventory, email-template catalog) + go/no-go review |
| **1** | Foundation | Epic 1 — Laravel + Twill + Livewire + Tailwind + CI + two-guard auth + storage disks + deploy target |
| **2** | Foundation modules + base blocks | Epic 2 **Stage 2a** (Page first, then Member, Group, Setting, Snippet, EmailTemplate, AdCategory, News, Form) + Epic 3 **Stage 3a** (text, image, gallery, video, cta, divider) |
| **3** | Content modules + auth + dependent blocks + restricted content | Epic 2 **Stage 2b** (BlogPost, FormSubmission, Ad, Calendar, Sponsor, RestrictedFile) + Epic 3 **Stage 3b** (form, snippet, file_download, restricted_section, agenda, sponsor_strip) + Epic 4 (auth flow, Swedish URLs, transactional emails) + Epic 5 (visibility middleware, RestrictedFilePolicy) |
| **4** | Custom logic + theme rebuild kickoff | Epic 6 (API, backups, member directory, classifieds CRUD + contact) + Epic 7 (start: design system, base layout, header/nav/footer, home) |
| **5** | Theme continued + PyroForms parity | Epic 7 (content/blog/news/ad/member/account/search/agenda/sponsor partials) + Epic 8 (Livewire dynamic-form, validation, email-by-EmailTemplate, file uploads, spam protection, submissions admin) |
| **6** | Lex migration + Data ETL + UAT prep | Epic 9 (LexParser + transforms + sample-validate) + Epic 10 (orchestrator + entity ETL + URL redirect map + reconciliation + dry-run on full snapshot) |
| **7** | QA + UAT + Cutover | Epic 11 — cross-browser, mobile, Lighthouse, security review, content-owner UAT, pre-cutover prep, cutover day, post-cutover monitoring |

**~8 weeks of work with Claude Code parallel workspaces, plus 1–2 weeks contingency = ~9–10 weeks elapsed.** Sprint 0 adds ~1 wk vs the original sequencing but pays for itself by locking the estimate and front-loading risk discovery.

For 2 devs in parallel, Sprints 2–6 can compress to ~3 weeks total elapsed (Sprints 0, 1, 7 sequential).

Phase 2 (paid memberships, Epic 12): 1 week additional, run after launch when business case validated.

## Bottom line

**Twill 3.x + Livewire 3 + Tailwind. Solo dev: ~3 – 3.75 months. With Claude Code parallel workspaces: ~9 – 10 weeks elapsed (incl. Sprint 0 spikes/audits). Two devs in parallel: ~5 – 6 weeks. $0 license.**

Includes member-driven classifieds (members create/edit/delete ads with images, visitors contact sellers via on-site form), full editorial CMS, restricted content, member auth, PyroForms parity, modernized theme rebuild.

Picked over alternatives because:

- **Laravel-native** — long-term ecosystem safety regardless of Twill's own velocity
- **Free + MIT-licensed + forkable** — Claude Code can patch upstream bugs in a fork when needed
- **Blocks system** cleanly resolves the Lex-tags-in-content problem, with editorial UX as a bonus
- **Plain Laravel frontend** — Livewire works exactly as expected

Recommended next step: run the four week-1 spikes against a copy of bockavel to lock the estimate before committing.

## Appendix — adding Filament later (optional, easy)

Twill and Filament coexist cleanly — both are Laravel packages, mounted on different paths, sharing the same `App\Models\User` and database.

- **Twill** at `/cms` — editorial work (pages, blog, members, blocks, media)
- **Filament** at `/admin` — non-editorial admin (API keys, form submission bulk ops, dashboards, ops tooling)

**Recommended approach: Twill only in Phase 1. Add Filament later only if a concrete non-editorial admin need surfaces.** Setup is incremental — ~0.5 d to install + share auth, ~0.5–1 d per Resource. Don't pre-build Filament Resources for hypothetical needs; the option stays cheap to exercise.

Likely future Filament Resources for bockavel:

- API key management
- Form submission bulk review
- Member ops / group reassignment
- Backup status dashboard

## Appendix — alternatives considered

| Alternative | Why not chosen |
|---|---|
| **Statamic 5** | Best-velocity Laravel CMS, but $275/yr commercial license. Faster ship (5–7 wk w/ Claude) but ongoing cost. Pick this if license tolerable and Twill upstream pace too slow. |
| **Custom Laravel + Filament** | Maximum flexibility, $0, but 7–11 wk w/ Claude — most code to write and maintain. Pick this only if requirements push beyond what a packaged CMS handles cleanly. |
| **WordPress** | Fastest ship (4–6 wk w/ Claude) and free with careful plugin selection, but cultural step sideways from PyroCMS, plugin maintenance tax, and self-fixing plugin internals is much harder than fixing Twill code. |
| **Craft CMS 5** | Strong content modeling but Yii-based, not Laravel — fails the ecosystem-safety requirement. |
| **Headless (Payload, Directus, Strapi)** | Adds a separate JS frontend stack with no payoff for one editorial site. |
