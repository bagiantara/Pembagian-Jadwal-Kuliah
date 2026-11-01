<?php

require_once __DIR__ . '/../model/account.php';

class LoginController
{
    public function index(string $loginRole): void
    {
        if (!in_array($loginRole, ['admin', 'user'], true)) {
            throw new InvalidArgumentException('Jenis halaman login tidak valid.');
        }
        $errors = [];
        $success = $_SESSION['flash_login_success'] ?? null;
        unset($_SESSION['flash_login_success']);
        $usernameValue = (string)($_SESSION['login_username'] ?? '');
        unset($_SESSION['login_username']);
        if (!isset($_SESSION['login_csrf_token'])) {
            $_SESSION['login_csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = (string)($_POST['csrf_token'] ?? '');
            $username = trim((string)($_POST['username'] ?? ''));
            $password = (string)($_POST['password'] ?? '');

            if (!hash_equals($_SESSION['login_csrf_token'], $token)) {
                $errors[] = 'Form login tidak valid. Muat ulang halaman lalu coba lagi.';
            } else {
                $account = (new Account())->authenticate($username, $password);
                if ($account === null) {
                    $errors[] = $loginRole === 'admin'
                        ? 'Username admin atau password salah.'
                        : 'NIM atau password salah.';
                } elseif ($account['role'] !== $loginRole) {
                    $errors[] = $loginRole === 'admin'
                        ? 'Akun ini bukan akun admin. Gunakan halaman login user.'
                        : 'Akun ini bukan akun user. Admin silakan masuk melalui /admin.';
                } elseif ($account['role'] === 'user' && $account['mahasiswa_id'] === null) {
                    $errors[] = 'Akun ini belum terhubung ke data mahasiswa. Hubungi admin.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user'] = $account;
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    unset($_SESSION['login_csrf_token']);
                    $destination = $loginRole === 'admin'
                        ? encodeUrlPath(rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/') . '/admin')
                        : encodeUrlPath($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=jadwal';
                    header('Location: ' . $destination);
                    exit;
                }
            }
        }

        renderView('login', [
            'errors' => $errors,
            'success' => $success,
            'usernameValue' => $usernameValue,
            'loginRole' => $loginRole,
            'csrfToken' => $_SESSION['login_csrf_token'],
        ]);
    }
}
