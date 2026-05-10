<?php use App\Core\Csrf; ?>
<section class="auth-panel">
    <div class="mb-4">
        <h1 class="h3 mb-1"><?= e(config('app.name')) ?></h1>
        <p class="text-secondary mb-0">Admin Login</p>
    </div>
    <form method="post" action="<?= e(url('/login')) ?>">
        <?= Csrf::field() ?>
        <div class="mb-3">
            <label class="form-label" for="email">Email</label>
            <input class="form-control" id="email" name="email" type="email" autocomplete="username" required>
        </div>
        <div class="mb-4">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Login</button>
    </form>
</section>
