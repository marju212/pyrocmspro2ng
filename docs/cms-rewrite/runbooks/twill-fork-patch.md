# Twill fork patch workflow

When a Twill upstream bug blocks bockavel work, patch it on the fork rather than waiting for upstream.

## One-time setup

1. Fork `area17/twill` to GitHub org → e.g. `junstrom/twill`.
2. Create deployment branch on the fork: `bockavel-main`. This is what the app's Composer requires.
3. In `app/composer.json`:
   ```json
   "repositories": [{
       "type": "vcs",
       "url": "https://github.com/junstrom/twill"
   }],
   "require": {
       "area17/twill": "dev-bockavel-main"
   }
   ```

## Patching a Twill bug

1. **Reproduce** the bug locally with a failing test (where possible) — minimal repro in the bockavel app first.
2. **Branch on the fork:** `git checkout -b fix/<short-description>` from `bockavel-main`.
3. **Have Claude Code patch:**
   - Bring the bug repro into the fork as a Twill-level test.
   - Patch the Twill source.
   - Verify the test passes.
4. **Tag internal version:** annotate or commit on `bockavel-main`.
5. **Merge the fix branch into `bockavel-main`** on the fork.
6. **Bump the app:**
   ```bash
   cd ~/Herd/bockavel/app
   composer update area17/twill
   ```
7. **Verify the original repro is fixed in the app.**
8. **Optionally upstream:** open PR from `fix/<short-description>` against `area17/twill:main`.

## When to patch vs. upstream-only

- **Patch immediately on fork** if the bug blocks current sprint work or has security implications.
- **Wait for upstream** if it's cosmetic / non-blocking and an upstream fix is in flight.
- **Always upstream** the patch if the bug is generic (not bockavel-specific) — keeps the fork debt small.

## Tracking

Maintain a `FORK-PATCHES.md` in the fork root listing each patch with:
- Date
- Short description
- Whether upstreamed (and PR link)
- Whether the patch can be removed (if upstream merged)

When upstream merges a patch, rebase `bockavel-main` and drop the local commit to keep the divergence minimal.
