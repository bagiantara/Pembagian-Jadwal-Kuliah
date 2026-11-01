<?php
$classes = is_array($classes ?? null) ? $classes : [];
$semesterClasses = is_array($semesterClasses ?? null) ? $semesterClasses : [];
$enrolledIds = is_array($enrolledIds ?? null) ? $enrolledIds : [];
$errors = is_array($errors ?? null) ? $errors : [];
$success = $success ?? null;
$csrfToken = (string)($csrfToken ?? '');
$student = is_array($student ?? null) ? $student : [];
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Mata Kuliah</title>
    <link rel="stylesheet" href="<?= $escape(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-curriculum-semester') ?>">
</head>
<body>
    <?php require __DIR__ . '/../../navigation.php'; ?>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">AKADEMIK · SEMESTER <?= (int)($student['semester_aktif'] ?? 1) ?></p>
                <h1>Pilih Mata Kuliah</h1>
                <p><?= $escape(($student['nama'] ?? '') . ' · ' . ($student['nim'] ?? '')) ?> · Semester aktif <?= (int)($student['semester_aktif'] ?? 1) ?></p>
            </div>
        </header>
        <?php if ($success !== null): ?><p class="success" role="status"><?= $escape($success) ?></p><?php endif; ?>
        <?php if ($errors !== []): ?>
            <ul class="errors" role="alert"><?php foreach ($errors as $error): ?><li><?= $escape($error) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <section class="panel">
            <h2>Mata kuliah yang sudah dipilih</h2>
            <?php if ($enrolledIds === []): ?>
                <p>Belum ada mata kuliah yang dipilih.</p>
            <?php else: ?>
                <?php
                $enrolledBySemester = [];
                foreach ($classes as $class) {
                    if (in_array((int)$class['id'], $enrolledIds, true)) {
                        $enrolledBySemester[(int)$class['semester_ke']][] = $class;
                    }
                }
                ?>
                <?php foreach ($enrolledBySemester as $semesterKe => $semesterEnrolled): ?>
                    <h3>Semester <?= (int)$semesterKe ?></h3>
                    <ul class="student-course-list">
                        <?php foreach ($semesterEnrolled as $class): ?>
                            <li>
                                <strong><?= $escape($class['nama_mata_kuliah']) ?></strong>
                                <span><?= $escape($class['kode_kelas'] . ' · Dosen: ' . $class['nama_dosen']) ?></span>
                                <span><?= $escape($class['semester'] . ' ' . $class['tahun_ajaran']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
        <section class="panel">
            <h2>Mata kuliah yang tersedia — Semester <?= (int)($student['semester_aktif'] ?? 1) ?></h2>
            <p>Daftar di bawah mengikuti semester aktif Anda. Mata kuliah semester lain tidak dapat dipilih dari akun user.</p>
            <?php if ($semesterClasses === []): ?>
                <p>Belum ada mata kuliah yang diatur untuk semester ini. Silakan hubungi admin.</p>
            <?php else: ?>
                <form method="post" action="<?= $escape($scriptName . '?url=mahasiswa') ?>">
                    <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                    <input type="hidden" name="action" value="enroll">
                    <?php foreach ($semesterClasses as $class): ?>
                        <?php $alreadyEnrolled = in_array((int)$class['id'], $enrolledIds, true); ?>
                        <label class="course-option">
                            <input type="checkbox" name="kelas_ids[]" value="<?= (int)$class['id'] ?>"<?= $alreadyEnrolled ? ' disabled checked' : '' ?>>
                            <span>
                                <strong><?= $escape($class['nama_mata_kuliah']) ?></strong><br>
                                <?= $escape($class['kode_kelas'] . ' · Dosen: ' . $class['nama_dosen']) ?><br>
                                Semester kurikulum <?= (int)$class['semester_ke'] ?> · <?= $escape($class['semester'] . ' ' . $class['tahun_ajaran']) ?>
                                <?= $alreadyEnrolled ? ' · Sudah dipilih' : '' ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                    <?php if (count($semesterClasses) > count(array_intersect(array_column($semesterClasses, 'id'), $enrolledIds))): ?>
                        <p class="actions"><button type="submit">Tambahkan pilihan</button></p>
                    <?php else: ?>
                        <p>Semua mata kuliah semester ini sudah dipilih.</p>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
