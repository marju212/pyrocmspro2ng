# Epic 8 — PyroForms Parity

**Sprint:** 5
**Goal:** every legacy form replicated with same fields, validation, recipients.

Spec: `specs/pyroforms-spec.md`.

## Checklist

- [ ] Audit every prod form → `specs/pyroforms-spec.md` (fields, validation, recipients, files, edge cases)
- [ ] Implement Form + FormSubmission Twill modules (Epic 2)
- [ ] Build Livewire `<x-dynamic-form>` component (renders any form schema)
- [ ] Build field types: text, email, phone, textarea, select, checkbox, radio, file
- [ ] Validation engine (built-in rules + custom regex per field)
- [ ] Email routing (per-form recipient list, CC, BCC) — bodies resolved from `EmailTemplate` by slug (default `pyroforms_notification`; per-form override allowed)
- [ ] Optional auto-reply to submitter via `pyroforms_response` template (per-form opt-in)
- [ ] File upload handling (private disk, size + type limits)
- [ ] Spam protection (honeypot + rate limiting)
- [ ] Submission listing in Twill admin (search, filter, bulk CSV export)
- [ ] Verify each migrated form against legacy behavior
