# URL → 301 redirect map

Complete map of legacy URLs to new URLs, for SEO continuity at cutover. Built during Epic 10 (Data Migration).

## Strategy

- Where URLs match exactly: no redirect needed (Laravel routes resolve directly).
- Where slugs change: 1:1 redirect rule.
- Where pages merge / split: best-effort redirect to closest equivalent.
- Where pages are removed: 410 Gone (not 301).

Use spatie/laravel-redirects for runtime resolution; export final map as CSV for review.

## Buckets

### Pages (CMS pages)
- [ ] Generate map from `bockavel_default_pages` slugs vs. new `pages.slug` after ETL.

### Blog
- [ ] `/blog/[year]/[month]/[slug]` → `/blog/[slug]` if structure simplifies, else 1:1.

### News
- [ ] `/news/[id]/[slug]` → `/nyheter/[slug]` (or whatever new path).

### Ads (annonser)
- [ ] `/annonser/[id]` → `/annonser/[slug]` (slugs introduced in new model).

### Member directory
- [ ] `/medlemmar/[id]` → `/medlemmar/[slug]`.

### Account / member-flow URLs (Swedish slugs — preserve verbatim)

These are wired into the legacy registration flow via `addons/bockavel/modules/member/events.php` and referenced by transactional emails. New app must answer the same paths.

- [ ] `/registrerad/{id}` — post-registration "email-sent" confirmation page. Preserve path. Treat `{id}` as opaque (must not leak whether the id exists — render same page either way).
- [ ] `/valkommen` — post-activation welcome page (entered after the user clicks the activation link). Preserve path.
- [ ] Verify and preserve other Swedish auth slugs in use (`/login`, `/logga-ut`, `/registrera`, `/glomt-losenord`, etc.) — list during legacy audit and add here.

### Files / downloads
- [ ] Old `/files-archive/...` URLs → new `/downloads/{slug}`.

### Misc
- [ ] Any hand-written redirects from `default_redirects` table copied across.

## QA at cutover

- [ ] Crawl prod with Screaming Frog or similar 24h before.
- [ ] After DNS swap, re-crawl: any 404 that was 200 in baseline = missing redirect.
- [ ] Add missing redirects iteratively for 2 weeks post-launch.
