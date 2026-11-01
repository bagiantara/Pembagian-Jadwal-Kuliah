<?php
$errors = is_array($errors ?? null) ? $errors : [];
$formData = is_array($formData ?? null) ? $formData : [];
$csrfToken = (string)($csrfToken ?? '');
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$scriptUrl = encodeUrlPath($scriptName);
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar User · Portal Akademik</title>
    <link rel="stylesheet" href="<?= $escape(rtrim(str_replace('\\', '/', dirname($scriptUrl)), '/') . '/assets/app.css?v=20261101-split-login') ?>">
</head>
<body>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">PORTAL AKADEMIK · USER MAHASISWA</p>
                <h1>Buat Akun User</h1>
                <p>Buat akun dengan NIM, email terdaftar, dan password baru.</p>
            </div>
        </header>
        <?php if ($errors !== []): ?>
            <ul class="errors" role="alert">
                <?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <section class="panel">
            <form method="post" action="<?= $escape($scriptUrl . '?url=register') ?>">
                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                <label for="nim">NIM</label>
                <input id="nim" name="nim" maxlength="30" autocomplete="username" required value="<?= $escape($formData['nim'] ?? '') ?>">
                <label for="email">Email terdaftar</label>
                <input id="email" name="email" type="email" maxlength="150" autocomplete="email" required value="<?= $escape($formData['email'] ?? '') ?>">
                <p class="field-hint">Email harus sama dengan email yang sudah dimasukkan admin pada data mahasiswa. Jika email belum benar atau belum terdaftar, minta admin memperbaruinya terlebih dahulu.</p>
                <label for="password">Buat password (minimal 5 karakter)</label>
                <input id="password" name="password" type="password" minlength="5" maxlength="72" autocomplete="new-password" required>
                <label for="password_confirmation">Ulangi password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" minlength="5" maxlength="72" autocomplete="new-password" required>
                <p class="field-hint">Bebas memakai huruf, angka, spasi, atau simbol. Pilih password yang mudah diingat dan jangan gunakan password akun lain.</p>
                <p class="actions"><button type="submit">Buat Akun</button></p>
            </form>
        </section>
        <p>Sudah punya akun? <a href="<?= $escape($scriptUrl . '?url=user') ?>">Masuk sebagai user</a></p>
    </main>
</body>
</html>
