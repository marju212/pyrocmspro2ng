# Backup + restore

Operational procedures for `spatie/laravel-backup`. Configured during Epic 6.

## Backups

- Driver: `spatie/laravel-backup`
- Destination: S3 bucket `bockavel-backups-prod` (versioning + lifecycle 90d).
- Schedule: daily at 03:00 Europe/Stockholm via Laravel Scheduler.
- Includes: full DB dump + uploads from `storage/app/public` and private disk.
- Notifications: email to ops@<domain> on success + failure.

## Restore — full

1. SSH to a fresh server with same Laravel + PHP version.
2. `composer install` + `npm ci && npm run build`.
3. Pull latest backup archive from S3 (`aws s3 cp …`).
4. Unpack: contains `db.sql` + filesystem tree.
5. Create empty DB; load dump: `mysql … < db.sql`.
6. Copy filesystem tree into `storage/app/`.
7. Set correct permissions on `storage/` and `bootstrap/cache/`.
8. `.env` from secret vault (NOT in backup).
9. `php artisan config:cache && php artisan route:cache`.
10. Smoke-test admin + public.

## Restore — selective (single record/file)

For single-row mistakes (deleted page, lost form submission):

1. Unpack latest backup to `/tmp/restore-<date>`.
2. Load DB dump into a scratch DB on the same server.
3. `INSERT … SELECT` from scratch DB into prod for the missing row(s).
4. Drop scratch DB.

## Verification (recommended quarterly)

- [ ] Pull a recent backup.
- [ ] Restore to a staging server.
- [ ] Run smoke tests.
- [ ] Document any gaps in this runbook.
