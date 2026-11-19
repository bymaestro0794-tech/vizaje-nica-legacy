-- Normalize legacy orders that were created before the status field was
-- initialized by the web/app order writers. Existing non-empty statuses are
-- preserved. The update is idempotent and does not touch payment data.
UPDATE `orders`
SET `status` = 'new'
WHERE `status` IS NULL
   OR TRIM(`status`) = '';
