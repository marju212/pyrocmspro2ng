# Schema mapping

Legacy PyroCMS table → Twill module/field map. To be filled before Epic 2 starts; updated as modules ship.

## Format

For each legacy entity, document:
- Legacy table name (with `bockavel_` prefix)
- Streams field types in use
- Target Twill module
- Field-level mapping including type changes
- Custom field types (`phone`, `page`, `regex`, `textarea_limited`, `multiple_images`) and their Twill equivalents (mostly browser/repeater/media fields)

## Tables to map

- [ ] `default_users` + `default_profiles` → `Member` model
- [ ] `member_groups` → `Group` model + `group_member` pivot
- [ ] `default_pages` + `default_page_chunks` → `Page` module + blocks
- [ ] `default_blog` + categories → `BlogPost` module
- [ ] `default_files` + `default_file_folders` → Twill media library / `RestrictedFile`
- [ ] `bockavel_ads` → `Ad` module + `AdCategory`
- [ ] News module table → `News`
- [ ] PyroForms tables → `Form` + `FormSubmission`
- [ ] `default_snippets` → `Snippet`
- [ ] `default_settings` + `default_variables` → `Setting` (or spatie/laravel-settings)
- [ ] `default_navigation_*` → handled in admin nav config (no runtime table)
- [ ] `default_redirects` → spatie/laravel-redirects
- [ ] `default_keywords` → `BlogPost`/`Page` tag relations (or drop if unused)
- [ ] Calendar Streams stream (`streams_data_streams_calendar` or equivalent — verify) → `Calendar` Twill module. Confirmed fields used in `legacy/addons/bockavel/plugins/helpers.php`: `date`, `page` (Page browser). Verify additional fields (title, body, location, time) against the legacy stream definition.
- [ ] Sponsors → `Sponsor` Twill module. Storage form unconfirmed — could be a Streams stream, settings rows, or hardcoded in `legacy/addons/bockavel/themes/style334/views/partials/content/{left,right}/sponsors.html`. Audit during ETL design. Likely fields: logo (media), link, name, sort_order, sidebar placement tag.
- [ ] Email templates (`default_email_templates` or equivalent) → `EmailTemplate` Twill module (slug, subject, body, from_name, from_email; multi-locale). Preserve slugs exactly — any `Events::trigger('email', ['slug' => ...])` call site in `legacy/` is bound to the existing slug.

## Pending decisions

- Search index strategy: database driver vs. Meilisearch.
- Comment storage: keep as separate model, or use third-party?
