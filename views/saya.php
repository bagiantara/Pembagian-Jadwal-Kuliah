<?php
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$dashboardUrl = $scriptName . '?url=dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Saya</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php') . '/assets/app.css?v=20261101-navigation-user-right', ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
    <?php require __DIR__ . '/../navigation.php'; ?>
    <main>
        <section class="profile">
            <p class="eyebrow">PROFIL</p>
            <h1>Tentang Saya</h1>
            <p>Halo, saya <strong>Dek Bagia</strong>.</p>
            <a href="<?= htmlspecialchars($dashboardUrl, ENT_QUOTES, 'UTF-8') ?>">Kembali ke Dashboard</a>
        </section>
    </main>
</body>
</html>
