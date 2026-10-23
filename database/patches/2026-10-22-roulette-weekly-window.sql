-- The roulette schedule is a fixed calendar period, not an editable cooldown.
-- Apply after 2026-10-16-roulette-gameplay-v1.sql.

SET NAMES utf8mb4;

UPDATE `roulette_settings`
SET `spin_interval_hours` = 168,
    `rulesRU` = 'Одна прокрутка доступна с понедельника 00:00 до следующего понедельника 00:00. Приз действует 24 часа. Скидки не суммируются; MAC, Anastasia Beverly Hills (ABH) и SHIK исключены. Окончательные условия проверяются в корзине.',
    `rulesRO` = 'O rotire este disponibilă de luni, ora 00:00, până luni următoare la ora 00:00. Premiul este valabil 24 de ore. MAC, Anastasia Beverly Hills (ABH) și SHIK sunt excluse. Condițiile finale sunt verificate în coș.',
    `updated_at` = UTC_TIMESTAMP()
WHERE `id` = 1;
