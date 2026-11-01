<?php
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$sections = [
    'accounts' => [
        'title' => 'Kelola Akun User',
        'description' => 'Siapkan pendaftaran user dengan memilih nama mahasiswa dan NIM.',
    ],
    'mahasiswa' => [
        'title' => 'Data Mahasiswa',
        'description' => 'Tambah, edit, cari, dan hapus data mahasiswa.',
    ],
    'mata_kuliah' => [
        'title' => 'Mata Kuliah',
        'description' => 'Kelola mata kuliah, kelas, SKS, semester, dan dosen pengajar.',
    ],
    'jadwal' => [
        'title' => 'Jadwal Kelas',
        'description' => 'Atur jadwal perkuliahan, ruang, dan kelas mahasiswa.',
    ],
    'dosen' => [
        'title' => 'Data Dosen',
        'description' => 'Tambah, edit, dan hapus data dosen.',
    ],
    'ruangan' => [
        'title' => 'Data Ruangan',
        'description' => 'Kelola informasi ruang kuliah dan kapasitasnya.',
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-navigation-user-right', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <?php require __DIR__ . '/../../navigation.php'; ?>
    <main>
        <header class="page-heading">
            <div>
                <p class="eyebrow">AREA ADMINISTRATOR</p>
                <h1>Panel Admin</h1>
                <p>Kelola data akademik dan pendaftaran user dari satu halaman.</p>
            </div>
        </header>
        <section class="pages" aria-label="Menu admin">
            <?php foreach ($sections as $route => $section): ?>
                <article class="page-card">
                    <h2><?= htmlspecialchars($section['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($section['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="page-link" href="<?= htmlspecialchars($scriptName . '?url=' . rawurlencode($route), ENT_QUOTES, 'UTF-8') ?>">Buka</a>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>
