# Setup runbook (Epic 1)

Operational steps to bootstrap the dev environment for the bockavel rewrite. Use alongside `epics/01-foundation.md` (which has the tickable checklist).

## 1. Umbrella repo + workspace

```bash
mkdir -p ~/Herd/bockavel
cd ~/Herd/bockavel
git init
git submodule add <pyrocms-repo-url> legacy
```

Copy `docs/cms-rewrite/` from this branch into `~/Herd/bockavel/docs/`.

## 2. Herd sites

| Site | Path | PHP | Domain |
|---|---|---|---|
| Legacy | `~/Herd/bockavel/legacy` | 7.4 | `bockavel-legacy.test` |
| New app | `~/Herd/bockavel/app` | 8.3 | `bockavel.test` |

## 3. Local databases

Herd MySQL or DBngin:

| DB | Purpose |
|---|---|
| `bockavel_legacy` | Restored from prod snapshot. Refreshed weekly. |
| `bockavel_new` | Target DB. ETL'd from `bockavel_legacy` by scripts in `tools/`. |

## 4. Twill fork

1. Fork `area17/twill` → `junstrom/twill` (or org equivalent).
2. Branch `bockavel-main` is the deployed branch.
3. See `runbooks/twill-fork-patch.md` for patch workflow.

## 5. Laravel + Twill scaffolding

```bash
cd ~/Herd/bockavel
composer create-project laravel/laravel app
cd app
# Add VCS repository for Twill fork in composer.json (see ADR §Twill fork strategy)
composer require area17/twill:dev-bockavel-main
php artisan twill:install
composer require livewire/livewire
npm install -D tailwindcss @tailwindcss/vite alpinejs
```

## 6. Storage disks

In `app/config/filesystems.php`:

- `public` — Twill media library, public images
- `private` — restricted file downloads (S3 private bucket or `storage/app/private`)

## 7. Two-guard auth

In `app/config/auth.php`, define both `twill_users` (Twill default) and `web` with custom `members` provider pointing at `App\Models\Member`.

## 8. CI

GitHub Actions: Pint → PHPStan → Pest. Block PRs on red CI.

## 9. Smoke test

- `bockavel-legacy.test` loads.
- `bockavel.test` shows Laravel welcome.
- `bockavel.test/cms` shows Twill admin login.

When all checks above pass, Epic 1 is done — proceed to Epic 2.
