-- Cleanup for ad 136: image rendered as <img src=""> on /ad/show/136
-- because the upload silently failed under the old 2M post_max_size cap
-- (resolved in commit 5552595, which raised limits and surfaces upload
-- errors). This SQL inspects the current state, then resets any broken
-- image references to 'dummy' so the form/view treat them as "no image".
--
-- After running, the user should re-upload via /ad/edit/136 — the higher
-- 20M cap now applies, and the streams_pre_update_entry hook in
-- addons/bockavel/modules/ad/events.php will refresh `updated` on save
-- so the ad reappears in the 2-month listing window.

-- ============================================================
-- 1. INSPECT: run these first to see what's actually stored
-- ============================================================

-- Ad row + timestamps
SELECT id, ad_title, ad_image_1, ad_image_2, ad_image_3, created, updated
FROM bockavel_ads
WHERE id = 136;

-- Are the referenced file ids actually present in bockavel_files?
SELECT a.id AS ad_id,
       a.ad_image_1, f1.filename AS file_1,
       a.ad_image_2, f2.filename AS file_2,
       a.ad_image_3, f3.filename AS file_3
FROM bockavel_ads a
LEFT JOIN bockavel_files f1 ON f1.id = a.ad_image_1
LEFT JOIN bockavel_files f2 ON f2.id = a.ad_image_2
LEFT JOIN bockavel_files f3 ON f3.id = a.ad_image_3
WHERE a.id = 136;

-- ============================================================
-- 2. CLEANUP: only run after inspection confirms the rows above
--    have NULL or orphan image ids (file_N IS NULL while ad_image_N
--    has a non-empty, non-'dummy' value).
-- ============================================================

START TRANSACTION;

-- Reset any image slot that is NULL/empty or points at a missing file
-- to 'dummy', the sentinel the streams image field treats as "no image".
UPDATE bockavel_ads a
LEFT JOIN bockavel_files f1 ON f1.id = a.ad_image_1
SET a.ad_image_1 = 'dummy'
WHERE a.id = 136
  AND (a.ad_image_1 IS NULL OR a.ad_image_1 = '' OR (a.ad_image_1 <> 'dummy' AND f1.id IS NULL));

UPDATE bockavel_ads a
LEFT JOIN bockavel_files f2 ON f2.id = a.ad_image_2
SET a.ad_image_2 = 'dummy'
WHERE a.id = 136
  AND (a.ad_image_2 IS NULL OR a.ad_image_2 = '' OR (a.ad_image_2 <> 'dummy' AND f2.id IS NULL));

UPDATE bockavel_ads a
LEFT JOIN bockavel_files f3 ON f3.id = a.ad_image_3
SET a.ad_image_3 = 'dummy'
WHERE a.id = 136
  AND (a.ad_image_3 IS NULL OR a.ad_image_3 = '' OR (a.ad_image_3 <> 'dummy' AND f3.id IS NULL));

-- Refresh `updated` so the ad re-enters the 2-month listing window.
-- Comment this out if the user prefers to refresh by re-saving via the form.
UPDATE bockavel_ads SET updated = NOW() WHERE id = 136;

-- Inspect, then COMMIT or ROLLBACK.
SELECT id, ad_image_1, ad_image_2, ad_image_3, updated
FROM bockavel_ads WHERE id = 136;

-- COMMIT;
-- ROLLBACK;
