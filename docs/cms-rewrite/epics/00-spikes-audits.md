# Epic 0 — Spikes & Audits

**Sprint:** 0 (~1 week)
**Goal:** lock the estimate to ±1 wk; fill the specs that downstream epics depend on; surface unknowns before they become blockers.

This epic exists because the rest of the plan assumes specs are filled and technical risks are resolved. Skip it and Sprint 2 stalls waiting on schema decisions.

Sprint 0 runs **before** Epic 1 foundation work (most activities are doc / audit work that needs no code) **except** the spikes themselves, which need a minimal Laravel + Twill scaffold. Two reasonable orderings:

- **Audits first, scaffold second, spikes third** — clean separation, ~5 work days.
- **Audits + foundation scaffold in parallel, spikes once scaffold lands** — compresses to ~3 work days if the team has bandwidth.

Either way, output by end of Sprint 0:
- All specs in `docs/cms-rewrite/specs/` filled (or stubbed with verified TODOs).
- All four spikes run; lessons documented; estimate confirmed or replanned.
- Stakeholder design pass complete (modernized theme direction signed off).

## Spikes (technical feasibility, time-boxed)

Each spike runs on a throwaway branch off the foundation scaffold. Code is **not** kept verbatim — the lessons are. Output is a short note in this epic + relevant specs updated.

- [ ] **Twill module spike** — 3 days. Build the `Page` module end-to-end (Twill `make:module`, blueprint with body blocks, browser fields, a Livewire-rendered public page). Validates per-module estimate + foundation. Output: confirmed module pattern → reused as Epic 2's template.
- [ ] **Theme spike** — 4 days. Rebuild bockavel home page with real modernized design direction in Blade + Tailwind + Livewire. Validates 2–3 wk theme estimate + design system feasibility. Output: confirmed design system + base layout pattern → reused in Epic 7.
- [ ] **Data migration spike** — 2 days. Write ETL for `pages` only (one entity), including a Lex-to-blocks transform for tags found in body content, run on a DB copy. Validates ETL estimate + uncovers Lex audit findings early. Output: confirmed ETL pattern + initial Lex tag inventory.
- [ ] **PyroForms audit spike** — 1 day. List every production form with field types, validation, recipients, file uploads. Output: `specs/pyroforms-spec.md` populated.

## Audits (data gathering, time-boxed)

Pure document work — runs in parallel with the spikes once the scaffold is up.

- [ ] **Schema design pass** → fills `specs/schema-mapping.md`. One design pass mapping every legacy table / Streams stream to its Twill module + field mapping. Includes Calendar stream + Sponsors storage form verification.
- [ ] **Member rules audit** → fills `specs/member-rules.md`. Read `legacy/addons/bockavel/modules/member/`. Document group taxonomy, registration flow (already drafted), profile fields, visibility rules, member number generation pattern.
- [ ] **Lex tag audit (full)** → fills `specs/lex-tag-audit.md`. Run the audit SQL from the spec; classify each distinct tag pattern → target block. Extend with bockavel-specific Lex plugin tags (`helpers:*`, `members:*`, `subnav:*`, `ad:*`, `member:*`).
- [ ] **Theme view inventory** → fills `specs/theme-audit.md`. Enumerate `addons/bockavel/themes/style334/views/` template families.
- [ ] **Theme design direction** → fills `specs/theme-design.md`. Stakeholder design pass: typography, color, spacing, component library. Signed off before Epic 7 starts.
- [ ] **URL inventory (initial)** → seeds `specs/url-redirects.md`. Crawl the legacy site to capture top-traffic URLs that must redirect 1:1.
- [ ] **Email template catalog** → extends `specs/email-templates.md`. Grep `legacy/` for every `Events::trigger('email', [..., 'slug' => ...])` call site; capture each unique slug + variables passed.

## Spec readiness gate (end of Sprint 0)

Before Sprint 1 closes, each spec is one of:

- **Filled** — content is in place; consuming epic can start.
- **Stubbed-with-verified-TODOs** — gaps are explicit and have an owner / target date inside Sprint 1 or Sprint 2.

If any spec is empty with no plan, that's a Sprint 2 risk. Resolve before fanning out parallel workspaces.

## Go / no-go review

End of Sprint 0, hold a short review against the ADR:

- [ ] Did any spike blow up beyond its time-box? If yes, what does the realistic estimate look like?
- [ ] Any spec reveal exotic prior usage that needs scope adjustment? (Common candidates: surprising Lex tags; PyroForms with conditional logic; member rules with non-trivial profile constraints.)
- [ ] Theme design direction signed off?
- [ ] Twill upstream — anything fresh that affects the fork strategy?

If risks exceed the buffer, replan before Sprint 2 fans out. The cost of replanning at end-of-Sprint-0 is ~half a day; the cost of replanning mid-Sprint-3 is a week.
