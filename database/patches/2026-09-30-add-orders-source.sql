-- Add an explicit channel marker to orders created by the website and mobile app.
-- Existing orders remain "unknown" rather than being guessed from IP/payment.
ALTER TABLE `orders`
	ADD COLUMN `order_source` VARCHAR(16) NOT NULL DEFAULT 'unknown' AFTER `id`;
