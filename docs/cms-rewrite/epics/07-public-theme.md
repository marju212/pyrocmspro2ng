# Epic 7 — Public Theme (Modernized Rebuild)

**Sprints:** 4–6 (parallel workspaces)
**Goal:** entire public site rebuilt in Blade + Tailwind + Livewire.

Modernized rebuild — same structural IA as legacy `style334`, fresh design (not pixel-faithful). Design direction lives in `specs/theme-design.md`; legacy view inventory in `specs/theme-audit.md`.

## Checklist

- [ ] **Design system:** typography, color, spacing, component library → `specs/theme-design.md`
- [ ] Tailwind config + base styles + Vite setup
- [ ] Base layout (`layouts/app.blade.php`)
- [ ] Header / nav / footer components
- [ ] Home page
- [ ] Home page: upcoming-events agenda partial (queries `Calendar` module, future entries only)
- [ ] Content page template (renders blocks)
- [ ] Content page: sponsor sidebar partials (left + right placements, driven by `Sponsor` module)
- [ ] `/valkommen` welcome page (Blade — post-activation)
- [ ] `/registrerad/{id}` registration-confirmation page (Blade — post-registration; render same view regardless of whether `{id}` exists)
- [ ] Blog list / detail
- [ ] News list / detail
- [ ] Ad list / detail
- [ ] Member directory
- [ ] Search results page
- [ ] Login / register / account pages
- [ ] Form embed component
- [ ] 404 / 500 pages
- [ ] Alpine interactions (mobile nav, dropdowns, modals)
- [ ] Responsive breakpoints validated
- [ ] Accessibility audit (axe / Lighthouse a11y target 95+)
- [ ] Lighthouse perf pass (target 90+ all categories)
