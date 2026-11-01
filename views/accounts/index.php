<?php
$accounts = is_array($accounts ?? null) ? $accounts : [];
$students = is_array($students ?? null) ? $students : [];
$errors = is_array($errors ?? null) ? $errors : [];
$success = $success ?? null;
$csrfToken = (string)($csrfToken ?? '');
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun</title>
    <link rel="stylesheet" href="<?= $escape(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-navigation-user-right') ?>">
</head>
<body>
    <?php require __DIR__ . '/../../navigation.php'; ?>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">ADMINISTRASI</p>
                <h1>Kelola Akun</h1>
                <p>Pilih mahasiswa yang akan mendaftar sendiri. Username-nya adalah NIM; mahasiswa akan membuat password saat pendaftaran.</p>
            </div>
        </header>
        <?php if ($success !== null): ?>
            <p class="success" role="status"><?= $escape($success) ?></p>
        <?php endif; ?>
        <?php if ($errors !== []): ?>
            <ul class="errors" role="alert">
                <?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <section class="panel">
            <h2>Siapkan pendaftaran user</h2>
            <form method="post" action="<?= $escape($scriptName . '?url=accounts') ?>">
                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                <input type="hidden" name="action" value="create">
                <label for="mahasiswa_id">Nama mahasiswa dan NIM</label>
                <select id="mahasiswa_id" name="mahasiswa_id" required>
                    <option value="">Pilih mahasiswa</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?= (int)$student['id'] ?>"><?= $escape($student['nama'] . ' · NIM ' . $student['nim']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="field-hint">Admin hanya menyiapkan mahasiswa. Mahasiswa membuat password sendiri (minimal 5 karakter) melalui tautan Daftar sebagai mahasiswa, dengan NIM dan email terdaftar.</p>
                <p class="actions"><button type="submit"<?= $students === [] ? ' disabled' : '' ?>>Siapkan Mahasiswa</button></p>
            </form>
        </section>
        <section class="schedule-section">
            <h2>Daftar akun</h2>
            <?php if ($accounts === []): ?>
                <p>Belum ada akun.</p>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Username / NIM</th><th>Peran</th><th>Mahasiswa</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                            <?php foreach ($accounts as $account): ?>
                                <tr>
                                    <td><?= $escape($account['username']) ?></td>
                                    <td><?= $account['role'] === 'admin' ? 'Admin' : 'User' ?></td>
                                    <td><?= $account['nama_mahasiswa'] === null ? '—' : $escape($account['nim'] . ' - ' . $account['nama_mahasiswa']) ?></td>
                                    <td><?= (int)$account['is_active'] === 1 ? 'Aktif' : 'Menunggu pendaftaran' ?></td>
                                    <td>
                                        <form method="post" action="<?= $escape($scriptName . '?url=accounts') ?>" onsubmit="return confirm('Hapus akun ini?');">
                                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$account['id'] ?>">
                                            <button class="button-danger" type="submit">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
