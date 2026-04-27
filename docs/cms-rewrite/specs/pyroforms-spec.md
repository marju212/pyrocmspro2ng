# PyroForms audit

Every production form on bockavel, audited for parity. To be filled in week-1 spike (~1 day).

## Format per form

For each form record in `default_pyroforms_*` tables, document:

- **Form name + ID**
- **Page(s) it's embedded on** (search content for `{{ pyroforms:form id="X" }}`)
- **Fields** — name, type, label, required, default, validation rules, placeholder
- **Custom regex** if `regex` field type is used
- **File uploads** — allowed types, max size, destination
- **Recipients** — to/cc/bcc emails
- **Email template** — subject, body, headers
- **Confirmation** — redirect target or thank-you message
- **Storage** — does it persist submissions to DB?
- **Spam protection** — captcha? honeypot?
- **Edge cases** — multi-step? conditional fields? consent checkboxes?

## Forms to audit

- [ ] [enumerate from prod DB during week-1 spike]

## Field-type mapping

| Legacy field type | Twill / Livewire equivalent |
|---|---|
| text | `<input type="text">` |
| email | `<input type="email">` with email validation |
| phone (custom) | `<input type="tel">` with regex validation |
| textarea | `<textarea>` |
| textarea_limited (custom) | `<textarea maxlength>` + JS counter |
| select | `<select>` |
| checkbox | `<input type="checkbox">` |
| radio | `<input type="radio">` |
| file | Livewire file upload to private disk |
| regex (custom) | text input + Laravel `regex:` rule |
| page (custom) | browser to Page (admin only) |
| multiple_images (custom) | Livewire repeater + media library |
