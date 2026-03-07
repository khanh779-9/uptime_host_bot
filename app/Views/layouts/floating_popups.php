<?php
$popupItems = is_array($popupItems ?? null) ? $popupItems : [];
if (empty($popupItems)) {
    return;
}
?>
<div class="floating-popup-stack" aria-live="polite" aria-atomic="true">
    <?php foreach ($popupItems as $notice): ?>
        <?php
        $noticeLevel = (string) ($notice['level'] ?? 'info');
        $noticeLevel = in_array($noticeLevel, ['success', 'danger', 'warning', 'info'], true) ? $noticeLevel : 'info';
        $noticeIcon = 'bi-info-circle-fill';
        if ($noticeLevel === 'success') {
            $noticeIcon = 'bi-check-circle-fill';
        } elseif ($noticeLevel === 'danger') {
            $noticeIcon = 'bi-x-octagon-fill';
        } elseif ($noticeLevel === 'warning') {
            $noticeIcon = 'bi-exclamation-triangle-fill';
        }

        $noticeMessages = [];
        $messagesPayload = $notice['messages'] ?? null;
        if (is_array($messagesPayload)) {
            foreach ($messagesPayload as $messageItem) {
                $messageText = trim((string) $messageItem);
                if ($messageText !== '') {
                    $noticeMessages[] = $messageText;
                }
            }
        }

        if (empty($noticeMessages)) {
            $messagePayload = $notice['message'] ?? '';

            if (is_array($messagePayload)) {
                foreach ($messagePayload as $messageItem) {
                    $messageText = trim((string) $messageItem);
                    if ($messageText !== '') {
                        $noticeMessages[] = $messageText;
                    }
                }
            } else {
                $messageText = trim((string) $messagePayload);
                if ($messageText !== '') {
                    $splitMessages = preg_split('/\r\n|\r|\n/', $messageText) ?: [];
                    foreach ($splitMessages as $splitMessage) {
                        $normalized = trim((string) $splitMessage);
                        if ($normalized !== '') {
                            $noticeMessages[] = $normalized;
                        }
                    }
                }
            }
        }

        if (empty($noticeMessages)) {
            continue;
        }
        ?>
        <div class="floating-popup floating-popup-<?= htmlspecialchars($noticeLevel) ?>" role="alert" data-autoclose="5000">
            <div class="floating-popup-icon-wrap" aria-hidden="true">
                <i class="bi <?= htmlspecialchars($noticeIcon) ?>"></i>
            </div>

            <div class="floating-popup-content">
                <div class="floating-popup-message">
                    <?php if (count($noticeMessages) > 1): ?>
                        <ul class="floating-popup-list mb-0">
                            <?php foreach ($noticeMessages as $messageLine): ?>
                                <li><?= htmlspecialchars($messageLine) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <?= htmlspecialchars((string) $noticeMessages[0]) ?>
                    <?php endif; ?>
                </div>
            </div>

            <button type="button" class="floating-popup-close" aria-label="<?= htmlspecialchars(t('common.close', 'Close')) ?>">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    <?php endforeach; ?>
</div>
