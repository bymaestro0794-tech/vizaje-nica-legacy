<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$rows = isset($feedback) && is_array($feedback) ? $feedback : array();
$pagination = isset($pagination) && is_array($pagination)
    ? $pagination
    : array('page' => 1, 'total_pages' => 1, 'total' => count($rows));

$typeNames = array(
    'idea' => 'Идея',
    'bug' => 'Ошибка',
    'question' => 'Вопрос',
);

$statusNames = array(
    'new' => 'Новое',
    'in_progress' => 'В работе',
    'done' => 'Готово',
    'rejected' => 'Отклонено',
);

$page = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);
$queryUrl = static function ($nextPage) use ($selected_status, $selected_type) {
    $query = array();
    if ($selected_status !== '') {
        $query['status'] = $selected_status;
    }
    if ($selected_type !== '') {
        $query['type'] = $selected_type;
    }
    if ((int) $nextPage > 1) {
        $query['page'] = (int) $nextPage;
    }

    $url = '/' . ADM_CONTROLLER . '/feedback';
    return empty($query) ? $url : $url . '?' . http_build_query($query);
};
?>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li><a href="/<?= ADM_CONTROLLER ?>/">Главная</a><i class="fa fa-circle"></i></li>
        <li><span>Обратная связь сайта</span></li>
    </ul>
</div>

<h1 class="page-title">Обратная связь сайта</h1>

<form method="get" action="<?= html_escape(current_url()) ?>" class="form-inline" style="margin-bottom:20px;">
    <div class="form-group">
        <label for="feedback-status">Статус</label>
        <select id="feedback-status" name="status" class="form-control">
            <option value="">Все статусы</option>
            <?php foreach ($statusNames as $statusKey => $statusLabel): ?>
                <option value="<?= html_escape($statusKey) ?>" <?= $selected_status === $statusKey ? 'selected' : '' ?>><?= html_escape($statusLabel) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group" style="margin-left:8px;">
        <label for="feedback-type">Тип</label>
        <select id="feedback-type" name="type" class="form-control">
            <option value="">Все типы</option>
            <?php foreach ($typeNames as $typeKey => $typeLabel): ?>
                <option value="<?= html_escape($typeKey) ?>" <?= $selected_type === $typeKey ? 'selected' : '' ?>><?= html_escape($typeLabel) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn blue" style="margin-left:8px;">Применить</button>
</form>

<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption"><i class="icon-bubble"></i> Сообщения <small>(<?= (int) ($pagination['total'] ?? 0) ?>)</small></div>
    </div>
    <div class="portlet-body table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Тип</th>
                    <th>Сообщение</th>
                    <th>Страница</th>
                    <th>Язык</th>
                    <th>Материалы</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="7" class="text-muted">Сообщений пока нет.</td></tr>
            <?php else: ?>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= html_escape($row['created_at']) ?></td>
                        <td><?= html_escape($typeNames[$row['feedback_type']] ?? $row['feedback_type']) ?></td>
                        <td style="min-width:280px;max-width:420px;">
                            <?php
                            $message = (string) $row['message'];
                            $messagePreview = function_exists('mb_strlen') && mb_strlen($message, 'UTF-8') > 180
                                ? mb_substr($message, 0, 180, 'UTF-8') . '…'
                                : (strlen($message) > 180 ? substr($message, 0, 180) . '…' : $message);
                            ?>
                            <div style="white-space:pre-wrap;word-break:break-word;"><?= html_escape($messagePreview) ?></div>
                            <?php if ($messagePreview !== $message || !empty($row['reference_url']) || !empty($row['attachments'])): ?>
                                <a href="/<?= ADM_CONTROLLER ?>/feedback/view/<?= (int) $row['id'] ?>" style="display:inline-block;margin-top:6px;">Открыть item →</a>
                            <?php endif; ?>
                        </td>
                        <td><code><?= html_escape($row['page_path'] ?: '—') ?></code></td>
                        <td><?= html_escape($row['locale'] ?: '—') ?></td>
                        <td>
                            <?php if (!empty($row['reference_url'])): ?>
                                <div><a href="<?= html_escape($row['reference_url']) ?>" target="_blank" rel="noopener noreferrer">Ссылка</a></div>
                            <?php endif; ?>
                            <?php foreach (($row['attachments'] ?? array()) as $attachment): ?>
                                <div><a href="/<?= ADM_CONTROLLER ?>/feedback/image/<?= (int) $attachment['id'] ?>" target="_blank" rel="noopener noreferrer">Изображение</a></div>
                            <?php endforeach; ?>
                            <?php if (empty($row['reference_url']) && empty($row['attachments'])): ?>—<?php endif; ?>
                        </td>
                        <td>
                            <form method="post" action="/<?= ADM_CONTROLLER ?>/feedback/update_status/<?= (int) $row['id'] ?>">
                                <input type="hidden" name="return_status" value="<?= html_escape($selected_status) ?>">
                                <input type="hidden" name="return_type" value="<?= html_escape($selected_type) ?>">
                                <input type="hidden" name="return_page" value="<?= (int) $page ?>">
                                <select name="status" class="form-control input-sm" onchange="this.form.submit()">
                                    <?php foreach ($statusNames as $statusKey => $statusLabel): ?>
                                        <option value="<?= html_escape($statusKey) ?>" <?= $row['status'] === $statusKey ? 'selected' : '' ?>><?= html_escape($statusLabel) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <ul class="pagination" style="margin-bottom:0;">
                <?php if ($page > 1): ?><li><a href="<?= html_escape($queryUrl($page - 1)) ?>">← Назад</a></li><?php endif; ?>
                <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                    <li class="<?= $pageNumber === $page ? 'active' : '' ?>"><a href="<?= html_escape($queryUrl($pageNumber)) ?>"><?= $pageNumber ?></a></li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?><li><a href="<?= html_escape($queryUrl($page + 1)) ?>">Далее →</a></li><?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
