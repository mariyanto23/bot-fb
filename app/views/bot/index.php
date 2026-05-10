<?php use App\Core\Csrf; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-0">Bot Control</h1>
        <div class="text-secondary small">Manual controls for cron-compatible processes</div>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        'pending' => ['Pending', 'bi-hourglass-split'],
        'commented' => ['Commented', 'bi-check2-circle'],
        'failed' => ['Failed', 'bi-exclamation-triangle'],
        'skipped' => ['Skipped', 'bi-skip-forward'],
    ] as $key => $item): ?>
        <div class="col-6 col-xl-3">
            <div class="metric">
                <div class="metric-icon"><i class="bi <?= e($item[1]) ?>"></i></div>
                <div>
                    <div class="text-secondary small"><?= e($item[0]) ?></div>
                    <div class="metric-value"><?= e($stats[$key] ?? 0) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-xl-5">
        <section class="panel">
            <div class="panel-header"><h2 class="h5 mb-0">Actions</h2></div>
            <div class="d-flex flex-wrap gap-2">
                <form method="post" action="<?= e(url('/bot/run')) ?>">
                    <?= Csrf::field() ?>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-play-fill"></i> Run Bot</button>
                </form>
                <form method="post" action="<?= e(url('/bot/fetch-posts')) ?>">
                    <?= Csrf::field() ?>
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-cloud-download"></i> Fetch Posts</button>
                </form>
                <form method="post" action="<?= e(url('/bot/send-comments')) ?>">
                    <?= Csrf::field() ?>
                    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-send"></i> Send Comments</button>
                </form>
            </div>
            <hr>
            <dl class="row mb-0">
                <?php foreach ($statuses as $status): ?>
                    <dt class="col-sm-4"><?= e($status['status_key']) ?></dt>
                    <dd class="col-sm-8"><?= e($status['status_value'] ?? '-') ?></dd>
                <?php endforeach; ?>
            </dl>
        </section>
    </div>
    <div class="col-xl-7">
        <section class="panel">
            <div class="panel-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Facebook Cookie</h2>
                <span class="badge <?= $cookieExists ? 'text-bg-success' : 'text-bg-danger' ?>"><?= $cookieExists ? 'Saved' : 'Missing' ?></span>
            </div>
            <form method="post" action="<?= e(url('/bot/cookie')) ?>">
                <?= Csrf::field() ?>
                <div class="mb-3">
                    <label class="form-label">Cookie File Content</label>
                    <textarea class="form-control" name="cookie_content" rows="7" spellcheck="false"></textarea>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-save"></i> Save</button>
                    <button class="btn btn-outline-danger" type="submit" formaction="<?= e(url('/bot/cookie/clear')) ?>" onclick="return confirm('Clear saved cookie?')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
