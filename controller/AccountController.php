<?php

require_once __DIR__ . '/../model/account.php';

class AccountController
{
    public function index(): void
    {
        $model = new Account();
        $errors = [];
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = (string)($_POST['csrf_token'] ?? '');
            $action = (string)($_POST['action'] ?? '');
            if (!hash_equals($_SESSION['csrf_token'], $token)) {
                $errors[] = 'Form tidak valid. Muat ulang halaman lalu coba lagi.';
            } elseif ($action === 'create') {
                $mahasiswaId = filter_var(
                    $_POST['mahasiswa_id'] ?? null,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );
                $availableStudentIds = array_map(
                    static fn(array $student): int => (int)$student['id'],
                    $model->getAvailableStudents()
                );
                if ($mahasiswaId === false || !in_array($mahasiswaId, $availableStudentIds, true)) {
                    $errors[] = 'Pilih mahasiswa yang belum memiliki akun.';
                }

                if ($errors === []) {
                    try {
                        $model->createPendingStudentAccount($mahasiswaId);
                        $_SESSION['flash_success'] = 'Mahasiswa disiapkan untuk membuat akun. Minta mahasiswa mendaftar dengan NIM dan email terdaftar untuk menentukan password sendiri.';
                        $this->redirect();
                    } catch (mysqli_sql_exception $exception) {
                        if ($exception->getCode() === 1062) {
                            $errors[] = 'Mahasiswa tersebut sudah memiliki akun atau username sudah digunakan.';
                        } else {
                            $errors[] = 'Database gagal menyiapkan akun mahasiswa.';
                        }
                    } catch (RuntimeException $exception) {
                        $errors[] = $exception->getMessage();
                    }
                }
            } elseif ($action === 'delete') {
                $id = filter_var(
                    $_POST['id'] ?? null,
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );
                $account = null;
                foreach ($model->getAll() as $item) {
                    if ((int)$item['id'] === $id) {
                        $account = $item;
                        break;
                    }
                }
                if ($id === false || $account === null) {
                    $errors[] = 'Akun tidak ditemukan.';
                } elseif ((int)$id === (int)currentUser()['id']) {
                    $errors[] = 'Akun yang sedang digunakan tidak dapat dihapus.';
                } elseif ($account['role'] === 'admin' && $model->isLastAdmin((int)$id)) {
                    $errors[] = 'Akun admin terakhir tidak dapat dihapus.';
                } else {
                    $model->delete((int)$id);
                    $_SESSION['flash_success'] = 'Akun berhasil dihapus.';
                    $this->redirect();
                }
            } else {
                $errors[] = 'Aksi tidak dikenal.';
            }
        }

        renderView('accounts/index', [
            'accounts' => $model->getAll(),
            'students' => $model->getAvailableStudents(),
            'errors' => $errors,
            'success' => $success,
            'csrfToken' => $_SESSION['csrf_token'],
        ]);
    }

    private function redirect(): void
    {
        header('Location: ' . ($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=accounts');
        exit;
    }
}
