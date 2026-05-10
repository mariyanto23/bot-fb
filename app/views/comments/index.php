<?php use App\Core\Csrf; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-0">Comments</h1>
        <div class="text-secondary small">Rotated messages for automated replies</div>
    </div>
</div>

<section class="panel mb-4">
    <div class="panel-header"><h2 class="h5 mb-0">New Comment</h2></div>
    <form method="post" action="<?= e(url('/comments')) ?>">
        <?= Csrf::field() ?>
        <div class="mb-3">
            <label class="form-label" for="body">Body</label>
            <textarea class="form-control" id="body" name="body" rows="3" required></textarea>
        </div>
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" id="is_active" name="is_active" value="1" type="checkbox" checked>
            <label class="form-check-label" for="is_active">Active</label>
        </div>
        <button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg"></i> Add</button>
    </form>
</section>

<section class="panel">
    <div class="panel-header"><h2 class="h5 mb-0">Comment Pool</h2></div>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle js-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>Body</th>
                <th>Active</th>
                <th>Used</th>
                <th>Last Used</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($comments as $comment): ?>
                <tr>
                    <td><?= e($comment['id']) ?></td>
                    <td class="text-wrap table-long"><?= e($comment['body']) ?></td>
                    <td><?= ((int) $comment['is_active'] === 1) ? 'Yes' : 'No' ?></td>
                    <td><?= e($comment['used_count']) ?></td>
                    <td><?= e($comment['last_used_at'] ?? '-') ?></td>
                    <td class="text-end">
                        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#editComment<?= e($comment['id']) ?>" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
                <tr class="collapse" id="editComment<?= e($comment['id']) ?>">
                    <td colspan="6">
                        <form class="row g-2 align-items-end" method="post" action="<?= e(url('/comments/update')) ?>">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= e($comment['id']) ?>">
                            <div class="col-lg-8">
                                <label class="form-label">Body</label>
                                <textarea class="form-control" name="body" rows="2" required><?= e($comment['body']) ?></textarea>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" name="is_active" value="1" type="checkbox" <?= ((int) $comment['is_active'] === 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>
                            <div class="col-lg-2 d-flex gap-2">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i></button>
                                <button class="btn btn-outline-danger" type="submit" formaction="<?= e(url('/comments/delete')) ?>" onclick="return confirm('Delete this comment?')"><i class="bi bi-trash"></i></button>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
