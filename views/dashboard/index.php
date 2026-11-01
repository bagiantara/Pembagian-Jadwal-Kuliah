<?php
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$pages = [
    'jadwal' => ['title' => 'Jadwal Kelas', 'description' => 'Atur hari, jam, ruang, dan dosen pengajar setiap kelas.'],
    'kelas' => ['title' => 'Kelas Mahasiswa', 'description' => 'Lihat pilihan kelas Denpasar, Bangli, dan Kenyeri untuk mahasiswa.'],
    'ruangan' => ['title' => 'Data Ruangan', 'description' => 'Kelola kode, lokasi, dan kapasitas setiap ruang kuliah.'],
    'mata_kuliah' => ['title' => 'Mata Kuliah', 'description' => 'Kelola mata kuliah, kelas, semester, dan dosen pengajar.'],
    'dosen' => ['title' => 'Data Dosen', 'description' => 'Lihat daftar dosen yang tersimpan di database.'],
    'mahasiswa' => ['title' => 'Tambah Mahasiswa', 'description' => 'Isi formulir untuk menambahkan mahasiswa baru.'],
    'about' => ['title' => 'Data Mahasiswa', 'description' => 'Lihat mahasiswa yang sudah tersimpan beserta program studinya.'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-navigation-user-right', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <?php require __DIR__ . '/../../navigation.php'; ?>
    <main>
        <header class="page-heading dashboard-heading">
            <div>
                <p class="eyebrow">SELAMAT DATANG DI PORTAL AKADEMIK</p>
                <h1>Semua aktivitas perkuliahan, lebih teratur.</h1>
                <p>Kelola jadwal, ruangan, mata kuliah, dosen, dan data mahasiswa dalam satu tempat.</p>
            </div>
            <a class="button-link" href="<?= htmlspecialchars($scriptName . '?url=jadwal', ENT_QUOTES, 'UTF-8') ?>">Lihat Jadwal</a>
        </header>
        <section class="pages" aria-label="Daftar halaman">
            <?php foreach ($pages as $route => $page): ?>
                <article class="page-card">
                    <h2><?= htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($page['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <a class="page-link" href="<?= htmlspecialchars($scriptName . '?url=' . rawurlencode($route), ENT_QUOTES, 'UTF-8') ?>">
                        Buka halaman
                    </a>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>
