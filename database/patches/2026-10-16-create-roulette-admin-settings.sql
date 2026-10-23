-- Admin-only configuration for a future Lucky Roulette integration.
-- This patch does not enable a public wheel or issue rewards.
CREATE TABLE IF NOT EXISTS `roulette_settings` (
	`id` TINYINT UNSIGNED NOT NULL,
	`titleRU` VARCHAR(120) NOT NULL,
	`titleRO` VARCHAR(120) NOT NULL,
	`descriptionRU` TEXT NULL,
	`descriptionRO` TEXT NULL,
	`rulesRU` TEXT NULL,
	`rulesRO` TEXT NULL,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `roulette_prizes` (
	`id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
	`code` VARCHAR(40) NOT NULL,
	`titleRU` VARCHAR(120) NOT NULL,
	`titleRO` VARCHAR(120) NOT NULL,
	`reward_type` VARCHAR(32) NOT NULL,
	`reward_value` DECIMAL(10,2) NULL DEFAULT NULL,
	`probability` DECIMAL(5,2) NULL DEFAULT NULL,
	`is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
	`sort_order` SMALLINT NOT NULL DEFAULT 0,
	`updated_at` DATETIME NOT NULL,
	PRIMARY KEY (`id`),
	UNIQUE KEY `uniq_roulette_prizes_code` (`code`),
	KEY `idx_roulette_prizes_order` (`sort_order`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `roulette_settings` (
	`id`, `titleRU`, `titleRO`, `descriptionRU`, `descriptionRO`,
	`rulesRU`, `rulesRO`, `updated_at`
)
VALUES (
	1,
	'Рулетка удачи',
	'Roata norocului',
	'Крутите колесо и получите гарантированный подарок от Vizaje-Nica. Скидки, бонусы и другие приятные сюрпризы.',
	'Învârte roata și primește un cadou garantat de la Vizaje-Nica. Reduceri, puncte bonus și alte surprize plăcute.',
	'',
	'',
	NOW()
);

INSERT IGNORE INTO `roulette_prizes` (`code`, `titleRU`, `titleRO`, `reward_type`, `reward_value`, `probability`, `is_enabled`, `sort_order`, `updated_at`)
VALUES ('discount_15', '−15%', '−15%', 'discount_percent', 15, NULL, 1, 1, NOW());

INSERT IGNORE INTO `roulette_prizes` (`code`, `titleRU`, `titleRO`, `reward_type`, `reward_value`, `probability`, `is_enabled`, `sort_order`, `updated_at`)
VALUES ('free_shipping', 'Бесплатная доставка', 'Livrare gratuită', 'free_shipping', NULL, NULL, 1, 2, NOW());

INSERT IGNORE INTO `roulette_prizes` (`code`, `titleRU`, `titleRO`, `reward_type`, `reward_value`, `probability`, `is_enabled`, `sort_order`, `updated_at`)
VALUES ('bonus_100', '100 бонусов', '100 puncte bonus', 'bonus_points', 100, NULL, 1, 3, NOW());

INSERT IGNORE INTO `roulette_prizes` (`code`, `titleRU`, `titleRO`, `reward_type`, `reward_value`, `probability`, `is_enabled`, `sort_order`, `updated_at`)
VALUES ('gift', 'Подарок', 'Cadou', 'gift', NULL, NULL, 1, 4, NOW());

INSERT IGNORE INTO `roulette_prizes` (`code`, `titleRU`, `titleRO`, `reward_type`, `reward_value`, `probability`, `is_enabled`, `sort_order`, `updated_at`)
VALUES ('surprise', 'Сюрприз', 'Surpriză', 'surprise', NULL, NULL, 1, 5, NOW());

INSERT IGNORE INTO `roulette_prizes` (`code`, `titleRU`, `titleRO`, `reward_type`, `reward_value`, `probability`, `is_enabled`, `sort_order`, `updated_at`)
VALUES ('discount_10', '−10%', '−10%', 'discount_percent', 10, NULL, 1, 6, NOW());
