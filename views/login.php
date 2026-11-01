<?php
$errors = is_array($errors ?? null) ? $errors : [];
$success = $success ?? null;
$usernameValue = (string)($usernameValue ?? '');
$loginRole = ($loginRole ?? 'user') === 'admin' ? 'admin' : 'user';
$csrfToken = (string)($csrfToken ?? '');
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$scriptUrl = encodeUrlPath($scriptName);
$appDirectoryUrl = encodeUrlPath(rtrim(str_replace('\\', '/', dirname($scriptName)), '/'));
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $loginRole === 'admin' ? 'Login Admin' : 'Login User' ?> · Portal Akademik</title>
    <link rel="stylesheet" href="<?= $escape($appDirectoryUrl . '/assets/app.css?v=20261101-split-login') ?>">
</head>
<body>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">PORTAL AKADEMIK · <?= $loginRole === 'admin' ? 'ADMINISTRATOR' : 'MAHASISWA' ?></p>
                <h1><?= $loginRole === 'admin' ? 'Login Admin' : 'Login User' ?></h1>
                <p><?= $loginRole === 'admin' ? 'Masuk untuk mengelola data akademik.' : 'Masuk untuk melihat data dan memilih mata kuliah.' ?></p>
            </div>
        </header>
        <?php if ($errors !== []): ?>
            <ul class="errors" role="alert">
                <?php foreach ($errors as $error): ?>
                    <li><?= $escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ($success !== null): ?><p class="success" role="status"><?= $escape($success) ?></p><?php endif; ?>
        <section class="panel">
            <form method="post" action="<?= $escape($loginRole === 'admin'
                ? rtrim($appDirectoryUrl, '/') . '/admin'
                : $scriptUrl . '?url=user') ?>">
                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                <label for="username"><?= $loginRole === 'admin' ? 'Username admin' : 'NIM mahasiswa' ?></label>
                <input id="username" name="username" autocomplete="username" maxlength="50" required autofocus value="<?= $escape($usernameValue) ?>">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
                <p class="field-hint"><?= $loginRole === 'admin'
                    ? 'Halaman ini khusus administrator.'
                    : 'Gunakan NIM dan password yang dibuat saat mendaftar.' ?></p>
                <p class="actions"><button type="submit">Masuk</button></p>
            </form>
        </section>
        <?php if ($loginRole === 'user'): ?>
            <p>Belum punya akun? <a href="<?= $escape($scriptUrl . '?url=register') ?>">Daftar sebagai mahasiswa</a>.</p>
        <?php endif; ?>
    </main>
</body>
</html>
