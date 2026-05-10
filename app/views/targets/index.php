<?php use App\Core\Csrf; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-0">Target Groups</h1>
        <div class="text-secondary small">Facebook group or feed URLs to scan</div>
    </div>
</div>

<section class="panel mb-4">
    <div class="panel-header"><h2 class="h5 mb-0">New Target</h2></div>
    <form class="row g-3" method="post" action="<?= e(url('/targets')) ?>">
        <?= Csrf::field() ?>
        <div class="col-lg-3">
            <label class="form-label">Name</label>
            <input class="form-control" name="name" required>
        </div>
        <div class="col-lg-5">
            <label class="form-label">Source URL</label>
            <input class="form-control" name="source_url" type="url" required>
        </div>
        <div class="col-lg-2">
            <label class="form-label">Group ID</label>
            <input class="form-control" name="facebook_group_id">
        </div>
        <div class="col-lg-1 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
                <input class="form-check-input" name="is_active" value="1" type="checkbox" checked>
            </div>
        </div>
        <div class="col-lg-1 d-flex align-items-end">
            <button class="btn btn-primary w-100" type="submit"><i class="bi bi-plus-lg"></i></button>
        </div>
    </form>
</section>

<section class="panel">
    <div class="panel-header"><h2 class="h5 mb-0">Targets</h2></div>
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle js-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>URL</th>
                <th>Active</th>
                <th>Last Fetch</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($targets as $target): ?>
                <tr>
                    <td><?= e($target['name']) ?></td>
                    <td class="table-url"><a href="<?= e($target['source_url']) ?>" target="_blank" rel="noopener"><?= e($target['source_url']) ?></a></td>
                    <td><?= ((int) $target['is_active'] === 1) ? 'Yes' : 'No' ?></td>
                    <td><?= e($target['last_fetched_at'] ?? '-') ?></td>
                    <td class="text-end">
                        <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="collapse" data-bs-target="#editTarget<?= e($target['id']) ?>" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
                <tr class="collapse" id="editTarget<?= e($target['id']) ?>">
                    <td colspan="5">
                        <form class="row g-2 align-items-end" method="post" action="<?= e(url('/targets/update')) ?>">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= e($target['id']) ?>">
                            <div class="col-lg-3">
                                <label class="form-label">Name</label>
                                <input class="form-control" name="name" value="<?= e($target['name']) ?>" required>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label">Source URL</label>
                                <input class="form-control" name="source_url" type="url" value="<?= e($target['source_url']) ?>" required>
                            </div>
                            <div class="col-lg-2">
                                <label class="form-label">Group ID</label>
                                <input class="form-control" name="facebook_group_id" value="<?= e($target['facebook_group_id'] ?? '') ?>">
                            </div>
                            <div class="col-lg-1">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" name="is_active" value="1" type="checkbox" <?= ((int) $target['is_active'] === 1) ? 'checked' : '' ?>>
                                </div>
                            </div>
                            <div class="col-lg-2 d-flex gap-2">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg"></i></button>
                                <button class="btn btn-outline-danger" type="submit" formaction="<?= e(url('/targets/delete')) ?>" onclick="return confirm('Delete this target?')"><i class="bi bi-trash"></i></button>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
