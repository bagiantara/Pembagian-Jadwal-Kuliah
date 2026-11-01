<?php
$mahasiswa = is_array($mahasiswa ?? null) ? $mahasiswa : [];
$rombonganBelajar = is_array($rombonganBelajar ?? null) ? $rombonganBelajar : [];
$programStudi = is_array($programStudi ?? null) ? $programStudi : [];
$kelasMataKuliah = is_array($kelasMataKuliah ?? null) ? $kelasMataKuliah : [];
$editingStudent = is_array($editingStudent ?? null) ? $editingStudent : null;
$enrolledClassIds = is_array($enrolledClassIds ?? null) ? $enrolledClassIds : [];
$filterValue = (string)($filterValue ?? 'semua');
$searchValue = (string)($searchValue ?? '');
$errors = is_array($errors ?? null) ? $errors : [];
$success = $success ?? null;
$csrfToken = (string)($csrfToken ?? '');
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$mahasiswaUrl = $scriptName . '?url=mahasiswa';
$clearSearchUrl = $scriptName . '?' . http_build_query([
    'url' => 'about',
    'filter_kelas' => $filterValue,
]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Mahasiswa</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-curriculum-semester', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <?php require __DIR__ . '/../navigation.php'; ?>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">AKADEMIK · DATA MAHASISWA</p>
                <h1>Data Mahasiswa</h1>
                <p>Cari mahasiswa berdasarkan NIM atau nama, lalu filter berdasarkan kelas.</p>
            </div>
            <?php if (isAdmin()): ?><a class="button-link" href="<?= htmlspecialchars($mahasiswaUrl, ENT_QUOTES, 'UTF-8') ?>">Tambah Mahasiswa</a><?php endif; ?>
        </header>

        <?php if ($success !== null): ?>
            <p class="success" role="status"><?= htmlspecialchars((string)$success, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($errors !== []): ?>
            <ul class="errors" role="alert">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars((string)$error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (isAdmin() && $editingStudent !== null): ?>
            <section class="panel">
                <h2>Edit Data Mahasiswa</h2>
                <form method="post" action="<?= htmlspecialchars($scriptName . '?url=about', ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= (int)$editingStudent['id'] ?>">
                    <label for="edit-nim">NIM</label>
                    <input id="edit-nim" name="nim" maxlength="30" required value="<?= htmlspecialchars((string)$editingStudent['nim'], ENT_QUOTES, 'UTF-8') ?>">
                    <label for="edit-nama">Nama</label>
                    <input id="edit-nama" name="nama" maxlength="100" required value="<?= htmlspecialchars((string)$editingStudent['nama'], ENT_QUOTES, 'UTF-8') ?>">
                    <label for="edit-email">Email (opsional)</label>
                    <input id="edit-email" name="email" type="email" maxlength="150" value="<?= htmlspecialchars((string)($editingStudent['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <label for="edit-program-studi">Program Studi</label>
                    <select id="edit-program-studi" name="program_studi_id" required>
                        <option value="">Pilih program studi</option>
                        <?php foreach ($programStudi as $prodi): ?>
                            <option value="<?= (int)$prodi['id'] ?>"<?= (string)$editingStudent['program_studi_id'] === (string)$prodi['id'] ? ' selected' : '' ?>>
                                <?= htmlspecialchars($prodi['kode'] . ' - ' . $prodi['nama'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="edit-kelas-mahasiswa">Kelas Mahasiswa</label>
                    <select id="edit-kelas-mahasiswa" name="rombongan_belajar_id">
                        <option value="">Belum dipilih</option>
                        <?php foreach ($rombonganBelajar as $cohort): ?>
                            <option value="<?= (int)$cohort['id'] ?>"<?= (string)($editingStudent['rombongan_belajar_id'] ?? '') === (string)$cohort['id'] ? ' selected' : '' ?>>
                                <?= htmlspecialchars((string)$cohort['nama'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="edit-semester-aktif">Semester Aktif</label>
                    <select id="edit-semester-aktif" name="semester_aktif" required>
                        <?php foreach (range(1, 7) as $semesterKe): ?>
                            <option value="<?= $semesterKe ?>"<?= (string)($editingStudent['semester_aktif'] ?? 1) === (string)$semesterKe ? ' selected' : '' ?>>
                                Semester <?= $semesterKe ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="field-hint">Naikkan semester mahasiswa di sini. Pilihan mata kuliah lama tetap tersimpan.</p>
                    <fieldset class="course-options">
                        <legend>Mata Kuliah</legend>
                        <?php foreach (range(1, 7) as $semesterKe): ?>
                            <?php $semesterClasses = array_filter($kelasMataKuliah, static fn(array $class): bool => (int)$class['semester_ke'] === $semesterKe); ?>
                            <?php if ($semesterClasses !== []): ?>
                                <h3>Semester <?= $semesterKe ?></h3>
                                <?php foreach ($semesterClasses as $class): ?>
                                    <label class="course-option">
                                        <input type="checkbox" name="kelas_ids[]" value="<?= (int)$class['id'] ?>"<?= in_array((int)$class['id'], $enrolledClassIds, true) ? ' checked' : '' ?>>
                                        <span><?= htmlspecialchars($class['nama_mata_kuliah'] . ' · ' . $class['kode_kelas'] . ' · ' . $class['nama_dosen'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </fieldset>
                    <p class="actions">
                        <button type="submit">Simpan Perubahan</button>
                        <a class="button-secondary" href="<?= htmlspecialchars($scriptName . '?url=about', ENT_QUOTES, 'UTF-8') ?>">Batal</a>
                    </p>
                </form>
            </section>
        <?php endif; ?>

        <form class="student-search" method="get" action="<?= htmlspecialchars($scriptName, ENT_QUOTES, 'UTF-8') ?>" role="search">
            <input type="hidden" name="url" value="about">
            <input type="hidden" name="filter_kelas" value="<?= htmlspecialchars($filterValue, ENT_QUOTES, 'UTF-8') ?>">
            <label class="visually-hidden" for="student-search">Cari berdasarkan NIM atau nama</label>
            <input
                id="student-search"
                name="q"
                type="search"
                maxlength="100"
                placeholder="Cari NIM atau nama mahasiswa..."
                value="<?= htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8') ?>"
            >
            <button type="submit">Cari</button>
            <?php if ($searchValue !== ''): ?>
                <a class="button-secondary" href="<?= htmlspecialchars($clearSearchUrl, ENT_QUOTES, 'UTF-8') ?>">Hapus pencarian</a>
            <?php endif; ?>
        </form>

        <nav class="schedule-filter" aria-label="Filter kelas mahasiswa">
            <a href="<?= htmlspecialchars($scriptName . '?url=about&filter_kelas=semua&q=' . rawurlencode($searchValue), ENT_QUOTES, 'UTF-8') ?>" class="<?= $filterValue === 'semua' ? 'is-active' : '' ?>"<?= $filterValue === 'semua' ? ' aria-current="page"' : '' ?>>Semua kelas</a>
            <?php foreach ($rombonganBelajar as $cohort): ?>
                <a
                    href="<?= htmlspecialchars($scriptName . '?url=about&filter_kelas=' . (int)$cohort['id'] . '&q=' . rawurlencode($searchValue), ENT_QUOTES, 'UTF-8') ?>"
                    class="<?= $filterValue === (string)$cohort['id'] ? 'is-active' : '' ?>"
                    <?= $filterValue === (string)$cohort['id'] ? 'aria-current="page"' : '' ?>
                ><?= htmlspecialchars((string)$cohort['nama'], ENT_QUOTES, 'UTF-8') ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if ($mahasiswa === []): ?>
            <div class="empty-state">
                <?php if ($searchValue !== ''): ?>
                    <strong>Mahasiswa tidak ditemukan.</strong>
                    <p>Tidak ada mahasiswa dengan NIM atau nama yang cocok dengan “<?= htmlspecialchars($searchValue, ENT_QUOTES, 'UTF-8') ?>”.</p>
                    <a class="button-link" href="<?= htmlspecialchars($clearSearchUrl, ENT_QUOTES, 'UTF-8') ?>">Tampilkan semua mahasiswa</a>
                <?php else: ?>
                    <strong>Belum ada mahasiswa terdaftar.</strong>
                    <p><?= isAdmin() ? 'Tambahkan mahasiswa agar data akademik muncul di halaman ini.' : 'Belum ada data mahasiswa yang dapat ditampilkan.' ?></p>
                    <?php if (isAdmin()): ?><a class="button-link" href="<?= htmlspecialchars($mahasiswaUrl, ENT_QUOTES, 'UTF-8') ?>">Tambah Mahasiswa</a><?php endif; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="student-data-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>NIM</th>
                            <th>Nama</th>
                            <th>Program Studi</th>
                            <th>Kelas Mahasiswa</th>
                            <th>Semester</th>
                            <th>Mata Kuliah</th>
                            <?php if (isAdmin()): ?><th>Aksi</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mahasiswa as $index => $m): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars((string)($m['nim'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($m['nama'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($m['prodi'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)($m['kelas'] ?? 'Belum dijadwalkan'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="student-course-count">Semester <?= (int)$m['semester_aktif'] ?></span></td>
                                <td>
                                    <?php if ($m['mata_kuliah'] === []): ?>
                                        <span class="student-course-empty">Belum memilih mata kuliah</span>
                                    <?php else: ?>
                                        <details class="student-course-details">
                                            <summary>
                                                <span class="student-course-count"><?= count($m['mata_kuliah']) ?> mata kuliah</span>
                                                <span class="student-course-toggle">Lihat pilihan</span>
                                            </summary>
                                            <?php $coursesBySemester = []; ?>
                                            <?php foreach ($m['mata_kuliah'] as $course): ?>
                                                <?php $coursesBySemester[$course['semester_ke']][] = $course; ?>
                                            <?php endforeach; ?>
                                            <?php foreach ($coursesBySemester as $semesterKe => $courses): ?>
                                                <h3>Semester <?= (int)$semesterKe ?></h3>
                                                <ul class="student-course-list">
                                                    <?php foreach ($courses as $course): ?>
                                                        <li>
                                                            <strong><?= htmlspecialchars((string)$course['nama'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                            <span><?= htmlspecialchars((string)$course['kode'] . ' · Dosen: ' . $course['dosen'], ENT_QUOTES, 'UTF-8') ?></span>
                                                            <span><?= htmlspecialchars((string)$course['jadwal'], ENT_QUOTES, 'UTF-8') ?></span>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endforeach; ?>
                                        </details>
                                    <?php endif; ?>
                                </td>
                                <?php if (isAdmin()): ?><td>
                                    <form
                                        method="post"
                                        action="<?= htmlspecialchars($scriptName . '?url=about', ENT_QUOTES, 'UTF-8') ?>"
                                        onsubmit="return confirm('Hapus data mahasiswa ini?');"
                                    >
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                        <button class="button-danger" type="submit" aria-label="Hapus mahasiswa">Hapus</button>
                                    </form>
                                    <a class="button-secondary" href="<?= htmlspecialchars($scriptName . '?url=about&action=edit&id=' . (int)$m['id'], ENT_QUOTES, 'UTF-8') ?>">Edit</a>
                                </td><?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
