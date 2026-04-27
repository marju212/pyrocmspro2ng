# Epic 5 — Restricted Content

**Sprint:** 3 (parallel with Epic 4 partway)
**Goal:** pages and files gated by login + group membership.

**Depends on:** `Page` + `Group` + `Member` from Stage 2a; `RestrictedFile` from Stage 2b; member auth flow live (Epic 4) so middleware has a real `auth:web` to lean on.

**Restricted file downloads — three-layer pattern (never skip any layer):**
1. Files stored on a **private disk** (`storage/app/private` or private S3 bucket), not under `public/`
2. Served via Laravel route + controller (`/downloads/{file:slug}`) protected by `auth:web` middleware, never linked directly
3. `RestrictedFilePolicy::download()` checks the member's groups against the file's `required_groups`

## Checklist

- [ ] Add `visibility` + `required_groups` to Page module
- [ ] Build `EnforcePageAccess` middleware
- [ ] Apply middleware to public page routes
- [ ] Build `restricted_section` block (renders children conditionally)
- [ ] Configure `private` filesystem disk
- [ ] Build `RestrictedFile` Twill admin (file upload to private disk + Group browser)
- [ ] Build `DownloadController` + auth-protected download routes
- [ ] Build `RestrictedFilePolicy` (download authorization)
- [ ] Pest tests: logged-out blocked, wrong-group blocked, correct-group allowed
- [ ] Verify private files are NOT directly web-accessible
