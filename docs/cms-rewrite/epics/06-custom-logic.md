# Epic 6 — Custom Logic

**Sprint:** 4 (parallel)
**Goal:** API, backups, member directory, member classifieds all live.

## Checklist

### API module
- [ ] Install Sanctum
- [ ] Build `ApiKey` Twill module + key generation
- [ ] Build `auth.api-key` middleware
- [ ] Build `ApiCallLog` model + logging middleware
- [ ] Audit which legacy API endpoints are actually used (check prod logs)
- [ ] Implement only the used endpoints
- [ ] Document API in Scribe

### Backups
- [ ] Install spatie/laravel-backup
- [ ] Configure S3 destination + email notifications
- [ ] Schedule daily backup job
- [ ] Test restore procedure → `runbooks/backup-restore.md`

### Member directory
- [ ] Build `MemberDirectory` Livewire component (list + filter by group + search)
- [ ] Build member detail page
- [ ] Implement pagination
- [ ] Apply visibility rules (only show members who consented)

### Member classifieds (ads)
- [ ] Migrations: `ads`, `ad_categories`, `ad_images` (or use Spatie Media Library for images)
- [ ] Models: `Ad`, `AdCategory`; relationships (`Ad belongsTo Member`, `Ad belongsTo AdCategory`, `Ad hasMany Image`)
- [ ] Status enum: `draft | pending_review | published | expired | sold`
- [ ] Twill admin module: `Ad` (moderation queue, edit any ad, bulk actions)
- [ ] Twill admin module: `AdCategory` (CRUD)
- [ ] Member-facing `/account/ads` page (list own ads with status, edit/delete) — Livewire
- [ ] `CreateAd` / `EditAd` Livewire forms (title, description, category, price, location, images)
- [ ] Multi-image upload with preview + reorder + delete
- [ ] `AdPolicy`: members own only their ads
- [ ] Public `/annonser` listing page: category filter + search + pagination — Livewire
- [ ] Public `/annonser/{slug}` detail page (Blade)
- [ ] `ContactSeller` Livewire form on detail page → emails member's stored email via `ad_contact_seller` `EmailTemplate` (no email exposure to visitor)
- [ ] `ad_published` notification email to member when their ad goes live (via `EmailTemplate`)
- [ ] `ad_expired` notification email to member when an ad nears / passes expiration (via `EmailTemplate`)
- [ ] Spam protection on contact form (honeypot + rate limit)
- [ ] Scheduled job: expire ads after configurable period (default 60 days)
- [ ] Member can mark ad as `sold` from their account
- [ ] Pest tests: create/edit/delete ownership, contact form, expiration
