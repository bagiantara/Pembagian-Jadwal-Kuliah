<?php
$mataKuliah = is_array($mataKuliah ?? null) ? $mataKuliah : [];
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
    <title>Kelola Mata Kuliah</title>
    <link rel="stylesheet" href="<?= $escape(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-curriculum-semester') ?>">
</head>
<body>
    <?php require __DIR__ . '/../../navigation.php'; ?>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">AKADEMIK · KURIKULUM</p>
                <h1>Mata Kuliah</h1>
                <p>Kelola mata kuliah, SKS, kelas, semester, serta dosen pengajarnya.</p>
            </div>
            <div class="heading-count">
                <strong><?= count($mataKuliah) ?></strong>
                <span>mata kuliah</span>
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
                    <h2><?= $editingId === null ? 'Tambah Mata Kuliah' : 'Edit Mata Kuliah' ?></h2>
                    <p>Isi nama mata kuliah dan jumlah SKS, lalu tentukan kelas dan pengajarnya.</p>
                </div>
            </div>
            <?php if ($dosen === []): ?>
                <p>Tambahkan dosen terlebih dahulu melalui halaman Data Dosen.</p>
            <?php else: ?>
                <form method="post" action="<?= $escape($scriptName . '?url=mata_kuliah') ?>">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <input type="hidden" name="action" value="<?= $editingId === null ? 'create' : 'update' ?>">
                    <?php if ($editingId !== null): ?>
                        <input type="hidden" name="id" value="<?= (int)$editingId ?>">
                    <?php endif; ?>

                    <label for="nama">Nama Mata Kuliah</label>
                    <input id="nama" name="nama" maxlength="100" required value="<?= $escape($formData['nama'] ?? '') ?>">

                    <label for="sks">Jumlah SKS</label>
                    <input id="sks" name="sks" type="number" min="1" max="6" required value="<?= $escape($formData['sks'] ?? '') ?>">

                    <label for="kode_kelas">Kode Kelas</label>
                    <input id="kode_kelas" name="kode_kelas" maxlength="20" required value="<?= $escape($formData['kode_kelas'] ?? '') ?>">

                    <label for="dosen_id">Dosen Pengajar</label>
                    <select id="dosen_id" name="dosen_id" required>
                        <option value="">Pilih dosen</option>
                        <?php foreach ($dosen as $lecturer): ?>
                            <option value="<?= (int)$lecturer['id'] ?>"<?= (string)($formData['dosen_id'] ?? '') === (string)$lecturer['id'] ? ' selected' : '' ?>>
                                <?= $escape($lecturer['nidn'] . ' - ' . $lecturer['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="semester_ke">Semester Kurikulum</label>
                    <select id="semester_ke" name="semester_ke" required>
                        <?php foreach (range(1, 7) as $semesterKe): ?>
                            <option value="<?= $semesterKe ?>"<?= (string)($formData['semester_ke'] ?? 1) === (string)$semesterKe ? ' selected' : '' ?>>
                                Semester <?= $semesterKe ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="semester">Periode Akademik</label>
                    <select id="semester" name="semester" required>
                        <?php foreach (['Ganjil', 'Genap'] as $semester): ?>
                            <option value="<?= $escape($semester) ?>"<?= ($formData['semester'] ?? 'Ganjil') === $semester ? ' selected' : '' ?>>
                                <?= $escape($semester) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="tahun_ajaran">Tahun Ajaran</label>
                    <input id="tahun_ajaran" name="tahun_ajaran" maxlength="9" placeholder="2026/2027" required value="<?= $escape($formData['tahun_ajaran'] ?? '') ?>">

                    <p class="actions">
                        <button type="submit"><?= $editingId === null ? 'Tambah Mata Kuliah' : 'Simpan Perubahan' ?></button>
                        <?php if ($editingId !== null): ?>
                            <a href="<?= $escape($scriptName . '?url=mata_kuliah') ?>">Batal</a>
                        <?php endif; ?>
                    </p>
                </form>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <section class="schedule-section">
            <div class="section-heading">
                <div>
                    <h2>Daftar Mata Kuliah</h2>
                    <p>Daftar mata kuliah dan kelas yang sudah terdaftar.</p>
                </div>
            </div>
        <?php if ($mataKuliah === []): ?>
            <p>Belum ada data mata kuliah.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Mata Kuliah</th>
                            <th>SKS</th>
                            <th>Semester Kurikulum</th>
                            <th>Kelas</th>
                            <th>Dosen</th>
                            <?php if (isAdmin()): ?><th>Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mataKuliah as $index => $mk): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= $escape($mk['nama']) ?></td>
                                <td><?= (int)$mk['sks'] ?></td>
                                <td><?= $mk['semester_ke'] === null ? 'Belum diatur' : 'Semester ' . (int)$mk['semester_ke'] ?></td>
                                <td>
                                    <?php if ($mk['kode_kelas'] === null): ?>
                                        Belum ada jadwal
                                    <?php else: ?>
                                        <?= $escape($mk['kode_kelas'] . ' · ' . $mk['semester'] . ' ' . $mk['tahun_ajaran']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= $escape($mk['nama_dosen'] ?? 'Belum ditentukan') ?></td>
                                <?php if (isAdmin()): ?><td>
                                    <div class="actions">
                                        <a href="<?= $escape($scriptName . '?url=mata_kuliah&action=edit&id=' . (int)$mk['id']) ?>">Edit</a>
                                        <form method="post" action="<?= $escape($scriptName . '?url=mata_kuliah') ?>" onsubmit="return confirm('Hapus mata kuliah ini?');">
                                            <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$mk['id'] ?>">
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
        <p><a class="button-link" href="<?= $escape($scriptName . '?url=jadwal') ?>">Kelola Jadwal Kelas</a></p>
    </main>
</body>
</html>
