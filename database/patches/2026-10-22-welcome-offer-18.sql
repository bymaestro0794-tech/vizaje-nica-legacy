-- Switch the first-order campaign from 15% to 18%.
-- Apply after 2026-10-12-add-welcome-offer.sql.

UPDATE `promocodes`
SET `code` = 'WELCOME18',
    `name` = 'Welcome 18% - first order',
    `value` = 18
WHERE `code` = 'WELCOME15'
  AND NOT EXISTS (
	SELECT 1 FROM (SELECT `id` FROM `promocodes` WHERE `code` = 'WELCOME18' LIMIT 1) AS existing_offer
  );

UPDATE `promocodes`
SET `name` = 'Welcome 18% - first order',
    `value` = 18,
    `status` = 'active'
WHERE `code` = 'WELCOME18';

-- If both codes existed, keep the old campaign out of runtime selection.
UPDATE `promocodes`
SET `status` = 'inactive'
WHERE `code` = 'WELCOME15';
