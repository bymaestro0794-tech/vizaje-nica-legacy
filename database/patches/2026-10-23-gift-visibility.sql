-- Separate gift visibility from the admin active flag.
-- is_enabled controls whether the gift record is usable;
-- show_on_site controls whether it can be selected/issued by the public roulette.
ALTER TABLE `gifts`
    ADD COLUMN `show_on_site` TINYINT(1) NOT NULL DEFAULT 1 AFTER `is_enabled`;

ALTER TABLE `gifts`
    ADD KEY `idx_gifts_visible_order` (`show_on_site`, `is_enabled`, `sorder`, `id`);
