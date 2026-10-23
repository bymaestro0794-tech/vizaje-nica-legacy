<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$item = isset($feedback_item) && is_array($feedback_item) ? $feedback_item : array();
$typeNames = array('idea' => 'Идея', 'bug' => 'Ошибка', 'question' => 'Вопрос');
$statusNames = array('new' => 'Новое', 'in_progress' => 'В работе', 'done' => 'Готово', 'rejected' => 'Отклонено');
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li><a href="/<?= ADM_CONTROLLER ?>/feedback">Обратная связь сайта</a><i class="fa fa-circle"></i></li>
        <li><span>Сообщение #<?= (int) $item['id'] ?></span></li>
    </ul>
</div>

<h1 class="page-title">Сообщение #<?= (int) $item['id'] ?></h1>

<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption"><i class="icon-bubble"></i> <?= html_escape($typeNames[$item['feedback_type']] ?? $item['feedback_type']) ?></div>
        <div class="actions"><a class="btn default" href="/<?= ADM_CONTROLLER ?>/feedback">← К списку</a></div>
    </div>
    <div class="portlet-body">
        <dl class="dl-horizontal">
            <dt>Дата</dt><dd><?= html_escape($item['created_at']) ?></dd>
            <dt>Страница</dt><dd><code><?= html_escape($item['page_path'] ?: '—') ?></code></dd>
            <dt>Язык</dt><dd><?= html_escape($item['locale'] ?: '—') ?></dd>
            <dt>Статус</dt><dd><?= html_escape($statusNames[$item['status']] ?? $item['status']) ?></dd>
        </dl>

        <hr>
        <h4>Сообщение</h4>
        <div style="max-width:900px;white-space:pre-wrap;word-break:break-word;"><?= html_escape($item['message']) ?></div>

        <?php if (!empty($item['reference_url'])): ?>
            <hr>
            <h4>Ссылка</h4>
            <a href="<?= html_escape($item['reference_url']) ?>" target="_blank" rel="noopener noreferrer"><?= html_escape($item['reference_url']) ?></a>
        <?php endif; ?>

        <?php if (!empty($item['attachments'])): ?>
            <hr>
            <h4>Изображения</h4>
            <?php foreach ($item['attachments'] as $attachment): ?>
                <a href="/<?= ADM_CONTROLLER ?>/feedback/image/<?= (int) $attachment['id'] ?>" target="_blank" rel="noopener noreferrer" style="display:inline-block;margin:0 12px 12px 0;">
                    <img src="/<?= ADM_CONTROLLER ?>/feedback/image/<?= (int) $attachment['id'] ?>" alt="<?= html_escape($attachment['original_name'] ?: 'Изображение') ?>" style="display:block;max-width:220px;max-height:180px;border:1px solid #e5e7eb;padding:4px;background:#fff;">
                    <span><?= html_escape($attachment['original_name'] ?: 'Открыть изображение') ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
