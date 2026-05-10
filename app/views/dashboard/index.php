<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-0">Dashboard</h1>
        <div class="text-secondary small">Bot overview and latest activity</div>
    </div>
    <a class="btn btn-primary" href="<?= e(url('/bot')) ?>"><i class="bi bi-robot"></i> Bot Control</a>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        'total' => ['Total Posts', 'bi-collection'],
        'pending' => ['Pending', 'bi-hourglass-split'],
        'commented' => ['Commented', 'bi-check2-circle'],
        'failed' => ['Failed', 'bi-exclamation-triangle'],
    ] as $key => $item): ?>
        <div class="col-6 col-xl-3">
            <div class="metric">
                <div class="metric-icon"><i class="bi <?= e($item[1]) ?>"></i></div>
                <div>
                    <div class="text-secondary small"><?= e($item[0]) ?></div>
                    <div class="metric-value" data-stat="<?= e($key) ?>"><?= e($stats[$key] ?? 0) ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">
    <div class="col-xl-5">
        <section class="panel">
            <div class="panel-header">
                <h2 class="h5 mb-0">Post Status</h2>
            </div>
            <canvas id="statusChart" height="220" data-chart='<?= e(json_encode($stats)) ?>'></canvas>
        </section>
    </div>
    <div class="col-xl-7">
        <section class="panel">
            <div class="panel-header">
                <h2 class="h5 mb-0">Latest Posts</h2>
            </div>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle js-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Target</th>
                        <th>Status</th>
                        <th>Attempts</th>
                        <th>Created</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?= e($post['id']) ?></td>
                            <td><?= e($post['target_name'] ?? '-') ?></td>
                            <td><span class="badge text-bg-secondary"><?= e($post['status']) ?></span></td>
                            <td><?= e($post['attempt_count']) ?></td>
                            <td><?= e($post['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<section class="panel mt-4">
    <div class="panel-header">
        <h2 class="h5 mb-0">Recent Logs</h2>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle">
            <thead>
            <tr>
                <th>Time</th>
                <th>Level</th>
                <th>Status</th>
                <th>Message</th>
            </tr>
            </thead>
            <tbody id="recentLogs">
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['created_at']) ?></td>
                    <td><?= e($log['level']) ?></td>
                    <td><?= e($log['status']) ?></td>
                    <td><?= e($log['message']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
