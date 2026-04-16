# Phase 0: HTTP Route Test Suite

**Status:** Not started
**Prerequisites:** None
**Blocks:** All other phases (establishes baseline)

---

## Purpose

Create a safety net before touching any code. Standalone curl-based script that verifies HTTP status codes against a running instance. Works on any PHP version.

## Implementation Tasks

- [ ] Create `tests/route_test.php` (standalone, no framework dependency)
- [ ] Implement public route checks (7 routes, expect HTTP 200)
- [ ] Implement auth-required route checks (3 routes, expect HTTP 302)
- [ ] Implement admin route checks (15 routes, expect HTTP 302)
- [ ] Implement bockavel-specific route checks
- [ ] Implement `--save-baseline` flag (saves to `tests/route_baseline.json`)
- [ ] Implement `--compare-baseline` flag (reports regressions)
- [ ] Implement `--auth user:pass` flag (authenticated test pass)
- [ ] Run against current working site and save baseline

## Routes to Test

### Public Routes (expect HTTP 200)

| Route                  | Description                     |
|------------------------|---------------------------------|
| `GET /`                | Homepage (pages catch-all)      |
| `GET /nyheter/`        | News listing                    |
| `GET /blimedlem`       | Registration form (alias)       |
| `GET /sitemap.xml`     | XML sitemap                     |
| `GET /member/medlemmar`| Members listing                 |
| `GET /users/login`     | Login page                      |
| `GET /register`        | Registration page               |

### Auth-Required Routes (expect HTTP 302 → login redirect)

| Route              | Description                        |
|--------------------|------------------------------------|
| `GET /ad/`         | Ad listing (requires login)        |
| `GET /ad/create`   | Create ad form (requires login)    |
| `GET /edit-profile` | Profile edit (requires login)     |

### Admin Routes (expect HTTP 302 → admin login redirect)

| Route                  | Description          |
|------------------------|----------------------|
| `GET /admin/`          | Admin dashboard      |
| `GET /admin/pages/`    | Pages admin          |
| `GET /admin/blog/`     | Blog admin           |
| `GET /admin/users/`    | Users admin          |
| `GET /admin/settings/` | Settings admin       |
| `GET /admin/files/`    | Files admin          |
| `GET /admin/navigation/`| Navigation admin    |
| `GET /admin/ad/`       | Ad admin (bockavel)  |
| `GET /admin/member/`   | Member admin         |
| `GET /admin/comments/` | Comments admin       |
| `GET /admin/templates/`| Templates admin      |
| `GET /admin/variables/`| Variables admin      |
| `GET /admin/redirects/`| Redirects admin      |
| `GET /admin/groups/`   | Groups admin         |
| `GET /admin/keywords/` | Keywords admin       |

### Bockavel-Specific

| Route              | Description                |
|--------------------|----------------------------|
| `GET /ad/show/all` | Ad show all placeholder    |

### Error Handling

| Route                                | Expected                         |
|--------------------------------------|----------------------------------|
| `GET /this-page-does-not-exist-xyz`  | 404 (or 200 via pages catch-all) |

## Usage

```bash
php tests/route_test.php https://bockavel.se
php tests/route_test.php https://bockavel.se --save-baseline
php tests/route_test.php https://bockavel.se --compare-baseline
php tests/route_test.php https://bockavel.se --auth user@email.com:password
```

## Verification

- [ ] All public routes return HTTP 200
- [ ] All auth-required routes return HTTP 302 (unauthenticated)
- [ ] All admin routes return HTTP 302 (unauthenticated)
- [ ] Baseline saved successfully to `tests/route_baseline.json`
- [ ] `--compare-baseline` reports zero regressions against itself
