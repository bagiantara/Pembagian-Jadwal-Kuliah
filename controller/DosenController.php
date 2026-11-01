<?php

require_once __DIR__ . '/../model/dosen.php';

class DosenController
{
    public function index(): void
    {
        $model = new Dosen();
        $errors = [];
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        $editingId = null;
        $formData = $this->emptyForm();

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = (string)($_POST['csrf_token'] ?? '');
            $action = (string)($_POST['action'] ?? '');

            if (!hash_equals($_SESSION['csrf_token'], $token)) {
                $errors[] = 'Form tidak valid. Muat ulang halaman lalu coba lagi.';
            } elseif ($action === 'delete') {
                $id = $this->readId($_POST['id'] ?? null);
                if ($id === null || $model->findById($id) === null) {
                    $errors[] = 'Data dosen tidak ditemukan.';
                } else {
                    try {
                        $model->delete($id);
                        $_SESSION['flash_success'] = 'Data dosen berhasil dihapus.';
                        $this->redirect();
                    } catch (mysqli_sql_exception $exception) {
                        if ($exception->getCode() === 1451) {
                            $errors[] = 'Dosen tidak dapat dihapus karena masih mengajar kelas mata kuliah.';
                        } else {
                            $errors[] = 'Database gagal menghapus data dosen.';
                        }
                    }
                }
            } elseif ($action === 'create' || $action === 'update') {
                $formData = [
                    'nidn' => trim((string)($_POST['nidn'] ?? '')),
                    'nama' => trim((string)($_POST['nama'] ?? '')),
                    'email' => trim((string)($_POST['email'] ?? '')),
                ];

                if ($action === 'update') {
                    $editingId = $this->readId($_POST['id'] ?? null);
                    if ($editingId === null || $model->findById($editingId) === null) {
                        $errors[] = 'Dosen yang akan diubah tidak ditemukan.';
                        $editingId = null;
                    }
                }

                if ($formData['nidn'] === '' || strlen($formData['nidn']) > 20) {
                    $errors[] = 'NIDN wajib diisi dan maksimal 20 karakter.';
                }
                if ($formData['nama'] === '' || strlen($formData['nama']) > 100) {
                    $errors[] = 'Nama dosen wajib diisi dan maksimal 100 karakter.';
                }
                if ($formData['email'] !== '' && (
                    !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)
                    || strlen($formData['email']) > 150
                )) {
                    $errors[] = 'Format email tidak valid atau email melebihi 150 karakter.';
                }

                if ($errors === []) {
                    $formData['email'] = $formData['email'] === '' ? null : $formData['email'];
                    try {
                        if ($action === 'create') {
                            $model->create($formData);
                            $_SESSION['flash_success'] = 'Data dosen berhasil ditambahkan.';
                        } else {
                            $model->update($editingId, $formData);
                            $_SESSION['flash_success'] = 'Data dosen berhasil diperbarui.';
                        }
                        $this->redirect();
                    } catch (mysqli_sql_exception $exception) {
                        if ($exception->getCode() === 1062) {
                            $errors[] = 'NIDN atau email tersebut sudah digunakan.';
                        } else {
                            $errors[] = 'Database gagal menyimpan data dosen.';
                        }
                    }
                }
            } else {
                $errors[] = 'Aksi tidak dikenal.';
            }
        } elseif (($_GET['action'] ?? '') === 'edit') {
            $editingId = $this->readId($_GET['id'] ?? null);
            $record = $editingId === null ? null : $model->findById($editingId);

            if ($record === null) {
                $errors[] = 'Dosen yang akan diubah tidak ditemukan.';
                $editingId = null;
            } else {
                $formData = $record;
            }
        }

        renderView('dosen/index', [
            'dosen' => $model->getAll(),
            'errors' => $errors,
            'success' => $success,
            'editingId' => $editingId,
            'formData' => $formData,
            'csrfToken' => $_SESSION['csrf_token'],
        ]);
    }

    private function emptyForm(): array
    {
        return ['nidn' => '', 'nama' => '', 'email' => ''];
    }

    private function readId($value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }

    private function redirect(): void
    {
        header('Location: ' . ($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=dosen');
        exit;
    }
}
