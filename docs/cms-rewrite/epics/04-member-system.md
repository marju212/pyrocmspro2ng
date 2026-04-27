# Epic 4 — Member System & Auth

**Sprint:** 3
**Goal:** members can register, log in, manage account.

**Depends on:** `Member` + `Group` + `EmailTemplate` modules from Epic 2 Stage 2a (Sprint 2). Stage 2a must ship before Sprint 3 fan-out.

Two-guard auth pattern: Twill admin (`twill_users` guard) and frontend members (`web` guard with custom `Member` model) are fully separated.

## Checklist

- [ ] Migrations: `members`, `groups`, `group_member` pivot
- [ ] Models: `Member`, `Group` (with relationships)
- [ ] Configure `web` guard with members provider
- [ ] Build `Login` Livewire component
- [ ] Build `Register` Livewire component
- [ ] Build `PasswordRequest` + `PasswordReset` Livewire components
- [ ] Build email verification flow
- [ ] Build `MemberAccount` Livewire dashboard
- [ ] Route protection (redirect to login with intended URL preservation)
- [ ] Wire `new_member` email send via `EmailTemplate` resolution at register time (see `specs/email-templates.md`)
- [ ] Wire `forgotten_password` and `password_changed` emails via `EmailTemplate`
- [ ] Wire `activation` email via `EmailTemplate`
- [ ] Post-registration redirect to `/registrerad/{id}` (email-sent confirmation page; treat `{id}` as opaque so it doesn't leak account existence)
- [ ] Post-activation redirect to `/valkommen` (welcome page)
- [ ] Build `/valkommen` welcome page (Blade)
- [ ] Build `/registrerad/{id}` confirmation page (Blade)
- [ ] Pest tests: registration, login, logout, password reset, email verification, registration redirect to `/registrerad/{id}`, activation redirect to `/valkommen`
- [ ] Document legacy member group logic + registration flow in `specs/member-rules.md`
