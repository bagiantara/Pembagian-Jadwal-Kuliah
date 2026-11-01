<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams['lifetime'],
        'path' => $cookieParams['path'],
        'domain' => $cookieParams['domain'],
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/model/account.php';

function renderView(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__ . '/views/' . $view . '.php';
}

$route = trim((string)($_GET['url'] ?? 'jadwal'), '/');

if (currentUser() !== null) {
    $activeAccount = (new Account())->findById((int)currentUser()['id']);
    if ($activeAccount === null) {
        unset($_SESSION['user'], $_SESSION['csrf_token']);
        header('Location: ' . encodeUrlPath($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=user');
        exit;
    }
    $_SESSION['user'] = $activeAccount;
}

if (in_array($route, ['login', 'user', 'register'], true)) {
    if (currentUser() !== null) {
        if (isAdmin()) {
            $adminUrl = encodeUrlPath(rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/') . '/admin');
            header('Location: ' . $adminUrl);
        } else {
            header('Location: ' . encodeUrlPath($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=jadwal');
        }
        exit;
    }
    if ($route === 'register') {
        require_once __DIR__ . '/controller/RegisterController.php';
        (new RegisterController())->index();
    } else {
        require_once __DIR__ . '/controller/LoginController.php';
        (new LoginController())->index('user');
    }
    exit;
}

if ($route === 'admin') {
    if (currentUser() === null) {
        require_once __DIR__ . '/controller/LoginController.php';
        (new LoginController())->index('admin');
        exit;
    }
    if (!isAdmin()) {
        denyAccess();
    }
}

if (currentUser() === null) {
    header('Location: ' . encodeUrlPath($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=user');
    exit;
}

if ($route === 'logout') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || !isset($_SESSION['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], (string)($_POST['csrf_token'] ?? ''))) {
        denyAccess();
    }
    $logoutDestination = isAdmin()
        ? encodeUrlPath(rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/') . '/admin')
        : encodeUrlPath($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=user';
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
    }
    session_destroy();
    header('Location: ' . $logoutDestination);
    exit;
}

if (in_array($route, ['accounts', 'dosen', 'about'], true) && !isAdmin()) {
    denyAccess();
}

if (!isAdmin()
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && !($route === 'mahasiswa' && ($_POST['action'] ?? '') === 'enroll')) {
    denyAccess();
}

if (!isAdmin()
    && ($_GET['action'] ?? '') === 'edit') {
    denyAccess();
}

switch ($route) {
    case 'admin':
        renderView('admin/index');
        break;

    case '':
    case 'dashboard':
        header('Location: ' . ($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=jadwal');
        exit;

    case 'kelas':
        require_once __DIR__ . '/controller/KelasController.php';
        (new KelasController())->index();
        break;

    case 'jadwal':
        require_once __DIR__ . '/controller/JadwalController.php';
        (new JadwalController())->index();
        break;

    case 'ruangan':
        require_once __DIR__ . '/controller/RuanganController.php';
        (new RuanganController())->index();
        break;

    case 'mahasiswa':
        require_once __DIR__ . '/controller/MahasiswaController.php';
        (new MahasiswaController())->index();
        break;

    case 'mata_kuliah':
        require_once __DIR__ . '/controller/MataKuliahController.php';
        (new MataKuliahController())->index();
        break;

    case 'saya':
        require_once __DIR__ . '/controller/SayaController.php';
        (new SayaController())->index();
        break;

    case 'dosen':
        require_once __DIR__ . '/controller/DosenController.php';
        (new DosenController())->index();
        break;

    case 'about':
        require_once __DIR__ . '/controller/AboutController.php';
        (new AboutController())->index();
        break;

    case 'accounts':
        require_once __DIR__ . '/controller/AccountController.php';
        (new AccountController())->index();
        break;

    default:
        http_response_code(404);
        echo 'Halaman tidak ditemukan.';
}
