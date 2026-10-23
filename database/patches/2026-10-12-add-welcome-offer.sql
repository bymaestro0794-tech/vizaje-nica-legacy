-- Persist the 14-day welcome-offer window on a client account.
ALTER TABLE `clients`
	ADD COLUMN `welcome_offer_started_at` DATETIME NULL,
	ADD COLUMN `welcome_offer_expires_at` DATETIME NULL;

-- Create the advertised first-order offer unless an admin-created WELCOME15
-- already exists. The runtime fails closed unless the three exclusions below
-- are linked and the promo matches the exact offer terms.
INSERT INTO `promocodes` (
	`name`,
	`code`,
	`type`,
	`value`,
	`min_amount`,
	`max_discount`,
	`usage_limit`,
	`per_user_limit`,
	`allow_discounted`,
	`starts_at`,
	`ends_at`,
	`status`,
	`brand_scope`
)
SELECT
	'Welcome 15% - first order',
	'WELCOME15',
	'percentage',
	15,
	NULL,
	NULL,
	NULL,
	1,
	1,
	NULL,
	NULL,
	'active',
	'all'
WHERE NOT EXISTS (
	SELECT 1
	FROM `promocodes`
	WHERE `code` = 'WELCOME15'
);

SET @welcome_promo_id := (
	SELECT `id`
	FROM `promocodes`
	WHERE `code` = 'WELCOME15'
	LIMIT 1
);

-- Resolve the exclusions by their catalog brand names rather than assuming IDs.
-- If the final verification query does not show exactly these three brands,
-- correct their links in the admin promo editor before enabling the campaign.
INSERT INTO `promocode_excluded_brands` (`promocode_id`, `brand_id`)
SELECT @welcome_promo_id, `brands`.`id`
FROM `brands`
WHERE
	@welcome_promo_id IS NOT NULL
	AND (
		UPPER(REPLACE(REPLACE(REPLACE(REPLACE(`brands`.`title`, '.', ''), ' ', ''), '-', ''), '&', '')) LIKE '%SHIK%'
		OR UPPER(REPLACE(REPLACE(REPLACE(REPLACE(`brands`.`title`, '.', ''), ' ', ''), '-', ''), '&', '')) IN ('MAC', 'MACCOSMETICS')
		OR UPPER(REPLACE(REPLACE(REPLACE(REPLACE(`brands`.`title`, '.', ''), ' ', ''), '-', ''), '&', '')) LIKE '%ANASTASIABEVERLYHILLS%'
	)
	AND NOT EXISTS (
		SELECT 1
		FROM `promocode_excluded_brands` AS `existing_exclusion`
		WHERE `existing_exclusion`.`promocode_id` = @welcome_promo_id
			AND `existing_exclusion`.`brand_id` = `brands`.`id`
	);

SELECT
	`promocodes`.`id`,
	`promocodes`.`code`,
	`promocodes`.`status`,
	COUNT(`promocode_excluded_brands`.`brand_id`) AS `excluded_brand_count`,
	GROUP_CONCAT(`brands`.`title` ORDER BY `brands`.`title` SEPARATOR ', ') AS `excluded_brands`
FROM `promocodes`
LEFT JOIN `promocode_excluded_brands`
	ON `promocode_excluded_brands`.`promocode_id` = `promocodes`.`id`
LEFT JOIN `brands`
	ON `brands`.`id` = `promocode_excluded_brands`.`brand_id`
WHERE `promocodes`.`code` = 'WELCOME15'
GROUP BY `promocodes`.`id`, `promocodes`.`code`, `promocodes`.`status`;
