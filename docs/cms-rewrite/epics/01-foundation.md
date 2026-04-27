# Epic 1 — Foundation & Setup

**Sprint:** 1
**Goal:** working dev environment, scaffolded Laravel + Twill + Livewire, CI, deploy pipeline.

**Depends on:** Epic 0 (Spikes & Audits) — design direction and schema decisions feed CLAUDE.md and storage-disk choices. Foundation can scaffold in parallel with the audits, but the spikes themselves run on top of this foundation.

See `00-setup.md` for the operational runbook.

## Checklist

- [ ] Create `~/Herd/bockavel/` umbrella; init git
- [ ] Add `legacy/` as git submodule of PyroCMS repo
- [ ] Configure Herd: `bockavel-legacy.test` (PHP 7.4) + `bockavel.test` (PHP 8.3)
- [ ] Create local DBs `bockavel_legacy` + `bockavel_new`; restore prod snapshot to `bockavel_legacy`
- [ ] Verify legacy site runs at `bockavel-legacy.test`
- [ ] Initialize Laravel 11 project in `app/`
- [ ] Fork `area17/twill` to project's GitHub org
- [ ] Install Twill via Composer VCS pointing at the fork
- [ ] Run Twill installer (`twill:install`); configure `config/twill.php`
- [ ] Install Livewire 3 + Alpine + Tailwind + Vite
- [ ] Configure two-guard auth in `config/auth.php` (`twill_users` + `web`/`members`)
- [ ] Set up GitHub Actions CI: Pint + PHPStan + Pest
- [ ] Write root `CLAUDE.md` (conventions, packages, legacy reference paths, target patterns)
- [ ] Configure storage disks: `public` and `private`
- [ ] Configure mail driver + queue (Redis or DB)
- [ ] Set up Sentry / error monitoring
- [ ] Provision deploy target (Forge / Ploi); configure SSH + envs
- [ ] Smoke test: Twill admin loads at `/cms`; public welcome route renders
