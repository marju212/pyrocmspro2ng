-- Cleanup for 3 file rows whose on-disk file no longer exists.
-- Generated 2026-04-30 from cross-checking bockavel_files vs uploads/bockavel/files/.
--
-- Missing files:
--   23268b832b87377  860a171acbdc093579e2c3bbb23f38be.jpg  (used by ad 118 — set to dummy)
--   72b925d1bca5796  b3dfc3f5127858e7e9e2340189ed71ac.jpg  (orphan, no ad references it)
--   d9cece5059471d3  1e588c14762e8fc3a40063d11c547939.jpg  (orphan, no ad references it)
--
-- Safety checks before running (uncomment to inspect first):
-- SELECT id, ad_title FROM bockavel_ads WHERE ad_image_1 = '23268b832b87377' OR ad_image_2 = '23268b832b87377' OR ad_image_3 = '23268b832b87377';
-- SELECT id, filename, name FROM bockavel_files WHERE id IN ('23268b832b87377','72b925d1bca5796','d9cece5059471d3');

START TRANSACTION;

-- Reset the broken image reference on ad 118 to 'dummy' so the form/view treat
-- it as "no image" instead of rendering a 404.
UPDATE bockavel_ads SET ad_image_1 = 'dummy' WHERE id = 118 AND ad_image_1 = '23268b832b87377';

-- Drop the three file rows that point at non-existent files.
DELETE FROM bockavel_files WHERE id IN ('23268b832b87377', '72b925d1bca5796', 'd9cece5059471d3');

-- Inspect, then COMMIT or ROLLBACK.
SELECT ROW_COUNT() AS rows_affected_in_last_statement;

-- COMMIT;
-- ROLLBACK;
