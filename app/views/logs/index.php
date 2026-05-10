<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-0">Logs</h1>
        <div class="text-secondary small">Application and notification events</div>
    </div>
</div>

<section class="panel mb-4">
    <div class="panel-header"><h2 class="h5 mb-0">Application Logs</h2></div>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle js-table">
            <thead>
            <tr>
                <th>Time</th>
                <th>Level</th>
                <th>Status</th>
                <th>Post</th>
                <th>Message</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= e($log['created_at']) ?></td>
                    <td><span class="badge text-bg-secondary"><?= e($log['level']) ?></span></td>
                    <td><?= e($log['status']) ?></td>
                    <td><?= e($log['related_post_id'] ?? '-') ?></td>
                    <td class="table-long"><?= e($log['message']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel">
    <div class="panel-header"><h2 class="h5 mb-0">Telegram Logs</h2></div>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle js-table">
            <thead>
            <tr>
                <th>Time</th>
                <th>Status</th>
                <th>Message</th>
                <th>Response</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($telegramLogs as $log): ?>
                <tr>
                    <td><?= e($log['created_at']) ?></td>
                    <td><?= e($log['status']) ?></td>
                    <td class="table-long"><?= e($log['message']) ?></td>
                    <td class="table-long"><?= e($log['response_body'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
