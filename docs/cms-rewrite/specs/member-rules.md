# Member rules

Member group / profile / permission rules from legacy PyroCMS. To be filled by reading `legacy/addons/bockavel/modules/member/` once and documenting findings.

## Source files to read

- `addons/bockavel/modules/member/details.php`
- `addons/bockavel/modules/member/controllers/`
- `addons/bockavel/modules/member/models/`
- `addons/bockavel/modules/member/language/`
- `system/cms/modules/users/` for default user behavior

## Topics to cover

- [ ] Group taxonomy (names, hierarchy, default group on register)
- [ ] Self-registration flow — required fields, approval needed?
- [ ] Profile fields beyond core (display_name, phone, address, photo, etc.)
- [ ] Visibility flags (private members vs. directory-listed members)
- [ ] Group-based permissions: which content types/pages each group sees
- [ ] Admin-only group-assignment workflow
- [ ] Email verification + password policies
- [ ] Members table data quirks (e.g., profile rows missing for old users?)

## Registration flow (from `legacy/addons/bockavel/modules/member/events.php`)

| Hook (legacy event) | Behavior | New app target |
|---|---|---|
| `post_user_register_email_sent` | Send `new_member` transactional email + redirect to `/registrerad/{id}` | Livewire register component dispatches `Mail::to(...)->send(new TemplatedMail('new_member', ...))`, then redirects to `/registrerad/{id}` |
| `post_user_activation` | Redirect to `/valkommen` | Activation route handler redirects to `/valkommen` after marking email verified |
| `post_user_update` | Redirect home with flash message | Profile-edit Livewire component redirects with session flash |

URL paths are Swedish — preserve verbatim (see `url-redirects.md`).

The `/registrerad/{id}` page reads minimal user data (name + email) from the just-created profile row. New app: render the same fields but treat `{id}` as opaque so it doesn't double as a user-existence oracle.

Email template slugs and bodies live in `email-templates.md`. Don't hardcode body text in PHP — resolve by slug.
