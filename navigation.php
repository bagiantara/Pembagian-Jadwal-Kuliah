<?php
$scriptName = $_SERVER['SCRIPT_NAME'] ?? 'index.php';
$currentRoute = trim((string)($_GET['url'] ?? 'jadwal'), '/');
$navItems = [
    'jadwal' => 'Jadwal Kelas',
    'ruangan' => 'Data Ruangan',
    'mata_kuliah' => 'Mata Kuliah',
    'mahasiswa' => isAdmin() ? 'Tambah Mahasiswa' : 'Pilih Mata Kuliah',
];
if (isAdmin()) {
    $navItems = ['admin' => 'Admin'] + $navItems;
    $navItems['dosen'] = 'Data Dosen';
    $navItems['about'] = 'Data Mahasiswa';
}
?>
<nav class="main-navigation" aria-label="Navigasi utama">
    <?php foreach ($navItems as $route => $label): ?>
        <?php $routeUrl = $route === 'admin'
            ? rtrim(str_replace('\\', '/', dirname($scriptName)), '/') . '/admin'
            : $scriptName . '?url=' . rawurlencode($route); ?>
        <a href="<?= htmlspecialchars($routeUrl, ENT_QUOTES, 'UTF-8') ?>"<?= $currentRoute === $route ? ' aria-current="page"' : '' ?>>
            <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        </a>
    <?php endforeach; ?>
    <?php $accountName = isAdmin()
        ? (string)(currentUser()['username'] ?? '')
        : (string)(currentUser()['nama_mahasiswa'] ?? currentUser()['username'] ?? ''); ?>
    <span class="navigation-user"><?= htmlspecialchars($accountName, ENT_QUOTES, 'UTF-8') ?> · <?= isAdmin() ? 'Admin' : 'User' ?></span>
    <form class="navigation-logout" method="post" action="<?= htmlspecialchars($scriptName . '?url=logout', ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)($_SESSION['csrf_token'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
        <button type="submit">Keluar</button>
    </form>
</nav>
