<?php
$kelas = is_array($kelas ?? null) ? $kelas : [];
$selectedKelas = is_array($selectedKelas ?? null) ? $selectedKelas : null;
$selectedKelasId = $selectedKelasId ?? '';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$escape = static fn($value): string => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelas Mahasiswa</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-navigation-user-right', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <?php require __DIR__ . '/../../navigation.php'; ?>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">AKADEMIK · ROMBONGAN BELAJAR</p>
                <h1>Kelas Mahasiswa</h1>
                <p>Pilih salah satu kelas mahasiswa: Denpasar, Bangli, atau Kenyeri.</p>
            </div>
            <div class="heading-count">
                <strong><?= count($kelas) ?></strong>
                <span>pilihan kelas</span>
            </div>
        </header>
        <?php if ($kelas === []): ?>
            <div class="empty-state">
                <strong>Belum ada pilihan kelas.</strong>
                <p>Jalankan setup database atau migrasi kelas untuk mengisi daftar kelas.</p>
            </div>
        <?php else: ?>
            <form method="get" action="<?= $escape($scriptName) ?>">
                    <input type="hidden" name="url" value="kelas">
                    <fieldset class="cohort-picker student-cohort-picker">
                        <legend>Kelas Mahasiswa</legend>
                        <p>Pilih lokasi kelas untuk menampilkan kelas mahasiswa.</p>
                        <div class="cohort-options">
                        <?php foreach ($kelas as $item): ?>
                            <label class="cohort-option<?= (string)$selectedKelasId === (string)$item['id'] ? ' is-selected' : '' ?>">
                                <input
                                    type="radio"
                                    name="kelas_id"
                                    value="<?= (int)$item['id'] ?>"
                                    required
                                    onchange="this.form.submit()"
                                    <?= (string)$selectedKelasId === (string)$item['id'] ? ' checked' : '' ?>
                                >
                                <span><?= $escape($item['nama']) ?></span>
                            </label>
                        <?php endforeach; ?>
                        </div>
                    </fieldset>
            </form>

            <?php if ($selectedKelas !== null): ?>
                <section class="class-cards" aria-live="polite">
                    <article class="class-card">
                        <span class="class-code"><?= $escape($selectedKelas['kode']) ?></span>
                        <h2><?= $escape($selectedKelas['nama']) ?></h2>
                        <p>Kelas mahasiswa yang dipilih.</p>
                        <a class="page-link" href="<?= $escape($scriptName . '?url=mahasiswa') ?>"><?= isAdmin() ? 'Tambah mahasiswa' : 'Pilih mata kuliah' ?></a>
                    </article>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>
