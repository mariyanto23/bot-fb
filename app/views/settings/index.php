<?php

use App\Core\Csrf;

$value = static fn (string $key, mixed $default = ''): string => (string) ($settings[$key] ?? $default);
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-0">Settings</h1>
        <div class="text-secondary small">Runtime bot and notification values</div>
    </div>
</div>

<form method="post" action="<?= e(url('/settings')) ?>">
    <?= Csrf::field() ?>
    <div class="row g-4">
        <div class="col-xl-6">
            <section class="panel">
                <div class="panel-header"><h2 class="h5 mb-0">Bot</h2></div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" id="bot_enabled" name="bot_enabled" value="1" type="checkbox" <?= $value('bot_enabled', '1') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="bot_enabled">Enabled</label>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Batch Limit</label>
                        <input class="form-control" name="bot_batch_limit" type="number" min="1" max="50" value="<?= e($value('bot_batch_limit', '5')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Min Delay</label>
                        <input class="form-control" name="bot_min_delay_seconds" type="number" min="1" value="<?= e($value('bot_min_delay_seconds', '20')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max Delay</label>
                        <input class="form-control" name="bot_max_delay_seconds" type="number" min="1" value="<?= e($value('bot_max_delay_seconds', '90')) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Cooldown</label>
                        <input class="form-control" name="bot_cooldown_seconds" type="number" min="1" value="<?= e($value('bot_cooldown_seconds', '900')) ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Facebook Base URL</label>
                        <input class="form-control" name="facebook_base_url" type="url" value="<?= e($value('facebook_base_url', config('config.facebook.base_url'))) ?>">
                    </div>
                </div>
            </section>
        </div>
        <div class="col-xl-6">
            <section class="panel">
                <div class="panel-header"><h2 class="h5 mb-0">Telegram</h2></div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" id="telegram_enabled" name="telegram_enabled" value="1" type="checkbox" <?= $value('telegram_enabled', '0') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="telegram_enabled">Enabled</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Bot Token</label>
                    <input class="form-control" name="telegram_bot_token" value="<?= e($value('telegram_bot_token')) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Chat ID</label>
                    <input class="form-control" name="telegram_chat_id" value="<?= e($value('telegram_chat_id')) ?>">
                </div>
            </section>
        </div>
    </div>
    <div class="mt-4">
        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i> Save</button>
    </div>
</form>
