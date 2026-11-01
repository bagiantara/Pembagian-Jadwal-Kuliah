<?php
$programStudi = is_array($programStudi ?? null) ? $programStudi : [];
$rombonganBelajar = is_array($rombonganBelajar ?? null) ? $rombonganBelajar : [];
$errors = is_array($errors ?? null) ? $errors : [];
$formData = is_array($formData ?? null) ? $formData : [];
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
    <title>Tambah Mahasiswa</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-navigation-user-right', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <?php require __DIR__ . '/../../navigation.php'; ?>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">AKADEMIK · DATA MAHASISWA</p>
                <h1>Tambah Mahasiswa</h1>
                <p>Daftarkan mahasiswa. Mahasiswa baru otomatis mulai dari semester 1 dan dapat melihat mata kuliah semesternya setelah akun dibuat.</p>
            </div>
            <a class="button-link" href="<?= $escape($scriptName . '?url=about') ?>">Lihat Data Mahasiswa</a>
        </header>

        <?php if ($success !== null): ?>
            <p class="success" role="status"><?= $escape($success) ?></p>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <ul class="errors">
                <?php foreach ($errors as $error): ?>
                    <li><?= $escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <section class="panel">
            <div class="section-heading">
                <div>
                    <h2>Informasi Mahasiswa</h2>
                    <p>Isi data dasar mahasiswa. Semester awal ditetapkan ke semester 1.</p>
                </div>
            </div>
            <form method="post" action="<?= $escape($scriptName . '?url=mahasiswa') ?>">
                <input type="hidden" name="csrf_token" value="<?= $escape($csrfToken) ?>">
                <input type="hidden" name="action" value="create">

                <label for="nim">NIM</label>
                <input id="nim" name="nim" maxlength="30" required value="<?= $escape($formData['nim'] ?? '') ?>">

                <label for="nama">Nama</label>
                <input id="nama" name="nama" maxlength="100" required value="<?= $escape($formData['nama'] ?? '') ?>">

                <label for="email">Email (opsional)</label>
                <input id="email" name="email" type="email" maxlength="150" value="<?= $escape($formData['email'] ?? '') ?>">

                <label for="program_studi_id">Program Studi</label>
                <select id="program_studi_id" name="program_studi_id" required>
                    <option value="">Pilih program studi</option>
                    <?php foreach ($programStudi as $prodi): ?>
                        <option value="<?= (int)$prodi['id'] ?>"<?= (string)($formData['program_studi_id'] ?? '') === (string)$prodi['id'] ? ' selected' : '' ?>>
                            <?= $escape($prodi['kode'] . ' - ' . $prodi['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="rombongan_belajar_id">Kelas Mahasiswa</label>
                <select id="rombongan_belajar_id" name="rombongan_belajar_id" class="student-cohort-select" required>
                    <option value="">-- Pilih kelas mahasiswa --</option>
                    <?php foreach ($rombonganBelajar as $cohort): ?>
                        <?php $cohortName = preg_replace('/^Kelas\s+/i', '', (string)$cohort['nama']); ?>
                        <option value="<?= (int)$cohort['id'] ?>"<?= (string)($formData['rombongan_belajar_id'] ?? '') === (string)$cohort['id'] ? ' selected' : '' ?>>
                            <?= $escape($cohortName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="field-hint">Pilih Denpasar, Bangli, atau Kenyeri.</p>

                <p class="actions">
                    <button type="submit"<?= $rombonganBelajar === [] ? ' disabled' : '' ?>>Simpan Mahasiswa</button>
                </p>
            </form>
        </section>
    </main>
</body>
</html>
