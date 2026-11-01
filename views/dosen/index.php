<?php
$dosen = is_array($dosen ?? null) ? $dosen : [];
$errors = is_array($errors ?? null) ? $errors : [];
$success = $success ?? null;
$editingId = $editingId ?? null;
$formData = is_array($formData ?? null) ? $formData : [];
$csrfToken = (string)($csrfToken ?? '');
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Dosen</title>
    <link rel="stylesheet" href="<?= $escape(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-navigation-user-right') ?>">
</head>
<body>
    <?php require __DIR__ . '/../../navigation.php'; ?>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">AKADEMIK · TENAGA PENGAJAR</p>
                <h1>Data Dosen</h1>
                <p>Kelola dosen pengajar untuk setiap mata kuliah dan kelas.</p>
            </div>
            <div class="heading-count">
                <strong><?= count($dosen) ?></strong>
                <span>dosen terdaftar</span>
            </div>
        </header>

        <?php if ($success !== null): ?>
            <p class="success"><?= $escape($success) ?></p>
        <?php endif; ?>
        <?php if ($errors !== []): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?= $escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
        <section class="panel">
            <div class="section-heading">
                <div>
                    <h2><?= $editingId === null ? 'Tambah Dosen' : 'Edit Dosen' ?></h2>
                    <p>Lengkapi identitas dosen yang akan mengajar.</p>
                </div>
            </div>
            <form method="post" action="<?= $escape($scriptName . '?url=dosen') ?>">
                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                <input type="hidden" name="action" value="<?= $editingId === null ? 'create' : 'update' ?>">
                <?php if ($editingId !== null): ?>
                    <input type="hidden" name="id" value="<?= (int)$editingId ?>">
                <?php endif; ?>

                <label for="nidn">NIDN</label>
                <input id="nidn" name="nidn" maxlength="20" required value="<?= $escape($formData['nidn'] ?? '') ?>">

                <label for="nama">Nama Dosen</label>
                <input id="nama" name="nama" maxlength="100" required value="<?= $escape($formData['nama'] ?? '') ?>">

                <label for="email">Email (opsional)</label>
                <input id="email" name="email" type="email" maxlength="150" value="<?= $escape($formData['email'] ?? '') ?>">

                <p class="actions">
                    <button type="submit"><?= $editingId === null ? 'Tambah Dosen' : 'Simpan Perubahan' ?></button>
                    <?php if ($editingId !== null): ?>
                        <a href="<?= $escape($scriptName . '?url=dosen') ?>">Batal</a>
                    <?php endif; ?>
                </p>
            </form>
        </section>
        <?php endif; ?>

        <section class="schedule-section">
            <div class="section-heading">
                <div>
                    <h2>Daftar Dosen</h2>
                    <p>Informasi dosen yang tersedia untuk mengajar.</p>
                </div>
            </div>
        <?php if ($dosen === []): ?>
            <p>Belum ada data dosen.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>NIDN</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <?php if (isAdmin()): ?><th>Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dosen as $index => $lecturer): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= $escape($lecturer['nidn']) ?></td>
                                <td><?= $escape($lecturer['nama']) ?></td>
                                <td><?= $escape($lecturer['email'] ?? '') ?></td>
                                <?php if (isAdmin()): ?><td>
                                    <div class="actions">
                                        <a href="<?= $escape($scriptName . '?url=dosen&action=edit&id=' . (int)$lecturer['id']) ?>">Edit</a>
                                        <form method="post" action="<?= $escape($scriptName . '?url=dosen') ?>" onsubmit="return confirm('Hapus data dosen ini?');">
                                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$lecturer['id'] ?>">
                                            <button type="submit">Hapus</button>
                                        </form>
                                    </div>
                                </td><?php endif; ?>
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
