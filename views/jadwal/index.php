<?php
$jadwal = is_array($jadwal ?? null) ? $jadwal : [];
$kelas = is_array($kelas ?? null) ? $kelas : [];
$ruangan = is_array($ruangan ?? null) ? $ruangan : [];
$rombonganBelajar = is_array($rombonganBelajar ?? null) ? $rombonganBelajar : [];
$errors = is_array($errors ?? null) ? $errors : [];
$success = $success ?? null;
$editingId = $editingId ?? null;
$formData = is_array($formData ?? null) ? $formData : [];
$csrfToken = (string)($csrfToken ?? '');
$days = is_array($days ?? null) ? $days : [];
$filterValue = (string)($filterValue ?? '');
$unassignedCount = (int)($unassignedCount ?? 0);
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Kelas</title>
    <link rel="stylesheet" href="<?= $escape(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-navigation-user-right') ?>">
</head>
<body>
    <?php require __DIR__ . '/../../navigation.php'; ?>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">AKADEMIK · JADWAL PERKULIAHAN</p>
                <h1>Jadwal Kelas</h1>
                <p>Atur waktu, ruang, dan dosen pengajar untuk setiap pertemuan kelas.</p>
            </div>
            <div class="heading-count">
                <strong><?= count($jadwal) ?></strong>
                <span>jadwal aktif</span>
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
                    <h2><?= $editingId === null ? 'Tambah Jadwal' : 'Ubah Jadwal' ?></h2>
                    <p>Pilih kelas yang telah dibuat pada menu Mata Kuliah.</p>
                </div>
            </div>
            <?php if ($kelas === []): ?>
                <div class="empty-state">
                    <strong>Belum ada kelas yang bisa dijadwalkan.</strong>
                    <p>Tambahkan mata kuliah, kode kelas, dan dosen pengajar terlebih dahulu.</p>
                    <a class="button-link" href="<?= $escape($scriptName . '?url=mata_kuliah') ?>">Buka Mata Kuliah</a>
                </div>
            <?php elseif ($ruangan === []): ?>
                <div class="empty-state">
                    <strong>Belum ada ruangan yang tersedia.</strong>
                    <p>Tambahkan ruangan terlebih dahulu sebelum membuat jadwal.</p>
                    <a class="button-link" href="<?= $escape($scriptName . '?url=ruangan') ?>">Kelola Ruangan</a>
                </div>
            <?php else: ?>
                <form method="post" action="<?= $escape($scriptName . '?url=jadwal') ?>" class="form-grid">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <input type="hidden" name="action" value="<?= $editingId === null ? 'create' : 'update' ?>">
                    <?php if ($editingId !== null): ?>
                        <input type="hidden" name="id" value="<?= (int)$editingId ?>">
                    <?php endif; ?>

                    <div class="field field-wide class-picker">
                        <label for="rombongan_belajar_id">Kelas Mahasiswa</label>
                        <select id="rombongan_belajar_id" name="rombongan_belajar_id" required>
                            <option value="">-- Pilih kelas mahasiswa --</option>
                            <?php foreach ($rombonganBelajar as $cohort): ?>
                                <option value="<?= (int)$cohort['id'] ?>"<?= (string)($formData['rombongan_belajar_id'] ?? '') === (string)$cohort['id'] ? ' selected' : '' ?>>
                                    <?= $escape($cohort['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="class-picker-hint">Pilih Denpasar, Bangli, atau Kenyeri.</p>
                    </div>

                    <div class="field field-wide class-picker">
                        <label for="kelas_id">Mata Kuliah dan Dosen Pengajar</label>
                        <select id="kelas_id" name="kelas_id" required>
                            <option value="">-- Pilih mata kuliah dan dosen --</option>
                            <?php foreach ($kelas as $item): ?>
                                <option value="<?= (int)$item['id'] ?>"<?= (string)($formData['kelas_id'] ?? '') === (string)$item['id'] ? ' selected' : '' ?>>
                                    <?= $escape($item['kode'] . ' · ' . $item['nama_mata_kuliah'] . ' — ' . $item['nama_dosen']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="class-picker-hint">Pilih mata kuliah beserta dosen pengajarnya untuk lokasi mahasiswa yang dipilih di atas.</p>
                    </div>

                    <div class="field">
                        <label for="hari">Hari</label>
                        <select id="hari" name="hari" required>
                            <?php foreach ($days as $day): ?>
                                <option value="<?= $escape($day) ?>"<?= ($formData['hari'] ?? 'Senin') === $day ? ' selected' : '' ?>>
                                    <?= $escape($day) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="ruangan_id">Ruangan</label>
                        <select id="ruangan_id" name="ruangan_id" required>
                            <option value="">Pilih ruangan</option>
                            <?php foreach ($ruangan as $room): ?>
                                <option value="<?= (int)$room['id'] ?>"<?= (string)($formData['ruangan_id'] ?? '') === (string)$room['id'] ? ' selected' : '' ?>>
                                    <?= $escape($room['kode'] . ' - ' . $room['nama'] . ' (' . ($room['gedung'] ?: 'Lokasi belum diatur') . ', kapasitas ' . $room['kapasitas'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="jam_mulai">Jam mulai</label>
                        <input id="jam_mulai" name="jam_mulai" type="time" required value="<?= $escape($formData['jam_mulai'] ?? '08:00') ?>">
                    </div>
                    <div class="field">
                        <label for="jam_selesai">Jam selesai</label>
                        <input id="jam_selesai" name="jam_selesai" type="time" required value="<?= $escape($formData['jam_selesai'] ?? '10:00') ?>">
                    </div>
                    <div class="form-actions field-wide">
                        <button type="submit"><?= $editingId === null ? 'Simpan Jadwal' : 'Simpan Perubahan' ?></button>
                        <?php if ($editingId !== null): ?>
                            <a class="button-secondary" href="<?= $escape($scriptName . '?url=jadwal') ?>">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <section class="schedule-section">
            <div class="section-heading">
                <div>
                    <h2>Daftar Perkuliahan</h2>
                    <p>Jadwal diurutkan berdasarkan hari dan jam mulai.</p>
                </div>
            </div>
            <nav class="schedule-filter" aria-label="Filter jadwal berdasarkan kelas">
                <a class="<?= $filterValue === '' || $filterValue === 'semua' ? 'is-active' : '' ?>"
                   href="<?= $escape($scriptName . '?url=jadwal&filter_kelas=semua') ?>">Semua kelas</a>
                <?php foreach ($rombonganBelajar as $cohort): ?>
                    <a class="<?= $filterValue === (string)$cohort['id'] ? 'is-active' : '' ?>"
                       href="<?= $escape($scriptName . '?url=jadwal&filter_kelas=' . (int)$cohort['id']) ?>">
                        <?= $escape($cohort['nama']) ?>
                    </a>
                <?php endforeach; ?>
                <?php if ($unassignedCount > 0): ?>
                    <a class="<?= $filterValue === 'belum_dipilih' ? 'is-active' : '' ?>"
                       href="<?= $escape($scriptName . '?url=jadwal&filter_kelas=belum_dipilih') ?>">
                        Belum dipilih (<?= $unassignedCount ?>)
                    </a>
                <?php endif; ?>
            </nav>
            <?php if ($jadwal === []): ?>
                <div class="empty-state">
                    <strong><?= $filterValue === '' || $filterValue === 'semua' ? 'Belum ada jadwal tersimpan.' : 'Belum ada jadwal untuk kelas ini.' ?></strong>
                    <p>Gunakan formulir di atas untuk mengatur jadwal perkuliahan.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Hari &amp; Waktu</th>
                                <th>Kelas Mahasiswa</th>
                                <th>Mata Kuliah</th>
                                <th>Kelas</th>
                                <th>Dosen Pengajar</th>
                                <th>Ruang</th>
                                <?php if (isAdmin()): ?><th>Aksi</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jadwal as $item): ?>
                                <tr>
                                    <td>
                                        <span class="day-badge"><?= $escape($item['hari']) ?></span>
                                        <span class="time-range"><?= $escape(substr((string)$item['jam_mulai'], 0, 5)) ?>–<?= $escape(substr((string)$item['jam_selesai'], 0, 5)) ?></span>
                                    </td>
                                    <td>
                                        <span class="cohort-badge"><?= $escape($item['nama_rombongan_belajar'] ?? 'Belum dipilih') ?></span>
                                    </td>
                                    <td>
                                        <strong><?= $escape($item['nama_mata_kuliah']) ?></strong>
                                        <span class="table-subtitle"><?= (int)$item['sks'] ?> SKS</span>
                                    </td>
                                    <td>
                                        <strong><?= $escape($item['kode_kelas']) ?></strong>
                                        <span class="table-subtitle"><?= $escape($item['semester'] . ' ' . $item['tahun_ajaran']) ?></span>
                                    </td>
                                    <td>
                                        <?= $escape($item['nama_dosen']) ?>
                                        <span class="table-subtitle">NIDN <?= $escape($item['nidn']) ?></span>
                                    </td>
                                    <td>
                                        <strong><?= $escape($item['nama_ruangan']) ?></strong>
                                        <span class="table-subtitle"><?= $escape($item['kode_ruangan'] . ($item['gedung'] ? ' · ' . $item['gedung'] : '')) ?></span>
                                    </td>
                                    <?php if (isAdmin()): ?><td>
                                        <div class="actions">
                                            <a href="<?= $escape($scriptName . '?url=jadwal&action=edit&id=' . (int)$item['id']) ?>">Edit</a>
                                            <form method="post" action="<?= $escape($scriptName . '?url=jadwal') ?>" onsubmit="return confirm('Hapus jadwal ini?');">
                                                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
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
