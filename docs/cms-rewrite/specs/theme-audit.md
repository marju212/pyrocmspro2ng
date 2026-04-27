# Theme audit — legacy view inventory

Inventory of every view file in `addons/bockavel/themes/style334/` (~74 files) so the modernized rebuild can be planned by family rather than file-by-file. To be filled before Epic 7 starts.

## Audit method

```
find legacy/addons/bockavel/themes/style334 -name "*.html" -o -name "*.php"
```

For each view, capture:
- Path
- Purpose (template family: home / content / blog / news / ad / member / form / partial)
- Lex tags used (theme-template kind, separate from editorial)
- LESS/CSS dependencies
- jQuery / JS hooks
- Whether it's still in use (some legacy themes have orphan templates)

## Template families

- [ ] **Layouts** — `layouts/default.html`, `layouts/print.html`, etc.
- [ ] **Home** — landing page composition
- [ ] **Content pages** — generic page wrapper, page chunks loop
- [ ] **Blog** — list, detail, archive, category
- [ ] **News** — list, detail
- [ ] **Ads** — list, detail, contact form, member's own ads
- [ ] **Members** — directory, profile detail
- [ ] **Forms** — pyroforms render templates
- [ ] **Account** — login, register, password reset, profile edit
- [ ] **Partials** — header, nav, footer, sidebar, search, language switcher
- [ ] **Search results**
- [ ] **Errors** — 404, maintenance

## Frontend assets

- [ ] LESS structure: `assets/less/*.less` — list root files
- [ ] Bootstrap version + which components in use
- [ ] jQuery plugins in use (sliders, tabs, validation, lightbox, etc.)
- [ ] Image / icon assets carried over vs. replaced

## Output

This audit drives the Epic 7 task breakdown — one workspace per template family.
