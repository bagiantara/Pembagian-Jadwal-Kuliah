<?php

require_once __DIR__ . '/../model/account.php';

class RegisterController
{
    public function index(): void
    {
        $errors = [];
        $formData = ['nim' => '', 'email' => ''];
        if (!isset($_SESSION['register_csrf_token'])) {
            $_SESSION['register_csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = (string)($_POST['csrf_token'] ?? '');
            $formData = [
                'nim' => trim((string)($_POST['nim'] ?? '')),
                'email' => trim((string)($_POST['email'] ?? '')),
            ];
            $password = (string)($_POST['password'] ?? '');
            $passwordConfirmation = (string)($_POST['password_confirmation'] ?? '');

            if (!hash_equals($_SESSION['register_csrf_token'], $token)) {
                $errors[] = 'Form pendaftaran tidak valid. Muat ulang halaman lalu coba lagi.';
            } elseif ($formData['nim'] === '' || strlen($formData['nim']) > 30) {
                $errors[] = 'NIM wajib diisi dan maksimal 30 karakter.';
            } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)
                || strlen($formData['email']) > 150) {
                $errors[] = 'Masukkan email yang valid, sama persis dengan email yang terdaftar.';
            } elseif (strlen($password) < 5 || strlen($password) > 72) {
                $errors[] = 'Password harus minimal 5 karakter dan maksimal 72 byte.';
            } elseif ($password !== $passwordConfirmation) {
                $errors[] = 'Konfirmasi password tidak sama.';
            } else {
                try {
                    if ((new Account())->registerStudent($formData['nim'], $formData['email'], $password)) {
                        $_SESSION['flash_login_success'] = 'Akun user berhasil dibuat. Silakan login dengan NIM dan password yang baru dibuat.';
                        $_SESSION['login_username'] = $formData['nim'];
                        unset($_SESSION['register_csrf_token']);
                        header('Location: ' . encodeUrlPath($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=user');
                        exit;
                    }
                    $errors[] = 'NIM dan email tidak cocok dengan data mahasiswa, atau akun untuk mahasiswa ini sudah ada. Periksa data atau hubungi admin.';
                } catch (mysqli_sql_exception $exception) {
                    if ($exception->getCode() === 1062) {
                        $errors[] = 'Akun untuk mahasiswa tersebut sudah ada. Jika Anda belum pernah membuatnya, hubungi admin.';
                    } else {
                        $errors[] = 'Database gagal membuat akun. Silakan coba lagi nanti.';
                    }
                }
            }
        }

        renderView('register', [
            'errors' => $errors,
            'formData' => $formData,
            'csrfToken' => $_SESSION['register_csrf_token'],
        ]);
    }
}
