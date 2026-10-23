<?php
defined('BASEPATH') or exit('No direct script access allowed');

$telegramSupportClass = isset($class) ? (string) $class : '';
$telegramSupportIsRomanian = isset($lclang) && $lclang === 'ro';
$telegramSupportSettings = isset($telegram_support) && is_array($telegram_support)
    ? $telegram_support
    : array(
        'enabled' => true,
        'url' => defined('TELEGRAM_SUPPORT_BOT') ? TELEGRAM_SUPPORT_BOT : '#',
        'label' => '@vizaje_nica_support_bot',
    );

if (empty($telegramSupportSettings['enabled'])) {
    return;
}
?>

<a
    class="vn-telegram-support <?= htmlspecialchars($telegramSupportClass, ENT_QUOTES, 'UTF-8') ?>"
    href="<?= htmlspecialchars((string) $telegramSupportSettings['url'], ENT_QUOTES, 'UTF-8') ?>"
    target="_blank"
    rel="noopener noreferrer"
>
    <span class="vn-telegram-support__icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21.6 3.4 18.3 20c-.25 1.17-.9 1.46-1.83.91l-5.05-3.72-2.44 2.35c-.27.27-.5.5-1.02.5l.36-5.15 9.38-8.48c.41-.36-.09-.56-.64-.2L5.46 13.6.49 12.04c-1.08-.34-1.1-1.08.23-1.57L20.15 3.1c.9-.34 1.69.2 1.45.3Z" fill="currentColor"/>
        </svg>
    </span>
    <span class="vn-telegram-support__body">
        <span class="vn-telegram-support__label">
            <?= $telegramSupportIsRomanian ? 'Suport pe Telegram' : 'Поддержка в Telegram' ?>
        </span>
        <span class="vn-telegram-support__handle"><?= htmlspecialchars((string) $telegramSupportSettings['label'], ENT_QUOTES, 'UTF-8') ?></span>
    </span>
    <span class="vn-telegram-support__arrow" aria-hidden="true">↗</span>
</a>
