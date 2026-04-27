# Theme design — modernized direction

Design direction for the rebuilt public theme. Replaces legacy `style334` (LESS + Bootstrap + jQuery 2.1.3) with Blade + Tailwind + Livewire + Alpine. Same structural IA, fresh design — not pixel-faithful.

To be filled by stakeholder design pass before Epic 7 starts.

## Sections

### Brand language
- Color palette (primary, secondary, accent, neutrals, semantic)
- Typography (heading + body fonts, scale, line-heights)
- Spacing scale + grid
- Iconography
- Imagery direction / photography style

### Component library
- Button variants
- Form controls
- Cards (page, blog, ad, member)
- Navigation (header, mobile menu, breadcrumbs, pagination)
- Modals / drawers
- Toasts
- Empty / error states
- Loading states (Livewire `wire:loading`)

### Page templates
- Home
- Content page (renders blocks)
- Blog list / detail
- News list / detail
- Ad list / detail / contact-seller
- Member directory / member detail
- Account / login / register
- Search results
- 404 / 500

### Responsive
- Breakpoint targets (mobile / tablet / desktop)
- Hero / nav behavior at each breakpoint

### Accessibility
- WCAG 2.1 AA target
- Focus states
- Color contrast minimums
- Reduced-motion support

### Performance budgets
- Lighthouse perf 90+
- LCP target
- JS bundle size cap
