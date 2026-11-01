<?php
$ruangan = is_array($ruangan ?? null) ? $ruangan : [];
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
    <title>Data Ruangan</title>
    <link rel="stylesheet" href="<?= $escape(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-navigation-user-right') ?>">
</head>
<body>
    <?php require __DIR__ . '/../../navigation.php'; ?>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">AKADEMIK · FASILITAS KAMPUS</p>
                <h1>Data Ruangan</h1>
                <p>Kelola ruang kuliah agar pemilihan ruang pada jadwal konsisten.</p>
            </div>
            <div class="heading-count">
                <strong><?= count($ruangan) ?></strong>
                <span>ruangan</span>
            </div>
        </header>

        <?php if ($success !== null): ?>
            <p class="success" role="status"><?= $escape($success) ?></p>
        <?php endif; ?>
        <?php if ($errors !== []): ?>
            <ul class="errors" role="alert">
                <?php foreach ($errors as $error): ?>
                    <li><?= $escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (isAdmin()): ?>
        <section class="panel">
            <div class="section-heading">
                <div>
                    <h2><?= $editingId === null ? 'Tambah Ruangan' : 'Edit Ruangan' ?></h2>
                    <p>Isi identitas lokasi dan kapasitas ruangan.</p>
                </div>
            </div>
            <form method="post" action="<?= $escape($scriptName . '?url=ruangan') ?>" class="form-grid">
                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                <input type="hidden" name="action" value="<?= $editingId === null ? 'create' : 'update' ?>">
                <?php if ($editingId !== null): ?>
                    <input type="hidden" name="id" value="<?= (int)$editingId ?>">
                <?php endif; ?>

                <div class="field">
                    <label for="kode">Kode ruangan</label>
                    <input id="kode" name="kode" maxlength="20" required placeholder="Contoh: LAB-01" value="<?= $escape($formData['kode'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="nama">Nama ruangan</label>
                    <input id="nama" name="nama" maxlength="100" required placeholder="Contoh: Lab Komputer 1" value="<?= $escape($formData['nama'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="gedung">Gedung atau lokasi</label>
                    <input id="gedung" name="gedung" maxlength="100" placeholder="Contoh: Gedung A" value="<?= $escape($formData['gedung'] ?? '') ?>">
                </div>
                <div class="field">
                    <label for="kapasitas">Kapasitas mahasiswa</label>
                    <input id="kapasitas" name="kapasitas" type="number" min="1" max="65535" required value="<?= $escape($formData['kapasitas'] ?? 30) ?>">
                </div>
                <div class="form-actions field-wide">
                    <button type="submit"><?= $editingId === null ? 'Simpan Ruangan' : 'Simpan Perubahan' ?></button>
                    <?php if ($editingId !== null): ?>
                        <a class="button-secondary" href="<?= $escape($scriptName . '?url=ruangan') ?>">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>
        <?php endif; ?>

        <section class="schedule-section">
            <div class="section-heading">
                <div>
                    <h2>Daftar Ruangan</h2>
                    <p>Ruangan yang masih dipakai jadwal tidak bisa dihapus.</p>
                </div>
            </div>
            <?php if ($ruangan === []): ?>
                <div class="empty-state">
                    <strong>Belum ada data ruangan.</strong>
                    <p>Tambahkan ruangan agar bisa dipilih ketika membuat jadwal kelas.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Ruangan</th>
                                <th>Gedung</th>
                                <th>Kapasitas</th>
                                <th>Jadwal Aktif</th>
                                <?php if (isAdmin()): ?><th>Aksi</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ruangan as $room): ?>
                                <tr>
                                    <td><strong><?= $escape($room['kode']) ?></strong></td>
                                    <td><?= $escape($room['nama']) ?></td>
                                    <td><?= $escape($room['gedung'] ?? 'Belum diatur') ?></td>
                                    <td><?= (int)$room['kapasitas'] ?> orang</td>
                                    <td><?= (int)$room['jumlah_jadwal'] ?></td>
                                    <?php if (isAdmin()): ?><td>
                                        <div class="actions">
                                            <a href="<?= $escape($scriptName . '?url=ruangan&action=edit&id=' . (int)$room['id']) ?>">Edit</a>
                                            <form method="post" action="<?= $escape($scriptName . '?url=ruangan') ?>" onsubmit="return confirm('Hapus ruangan ini?');">
                                                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int)$room['id'] ?>">
                                                <button class="button-danger" type="submit">Hapus</button>
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
