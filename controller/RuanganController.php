<?php

require_once __DIR__ . '/../model/ruangan.php';

class RuanganController
{
    public function index(): void
    {
        $model = new Ruangan();
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
                    $errors[] = 'Ruangan tidak ditemukan.';
                } else {
                    try {
                        $model->delete($id);
                        $_SESSION['flash_success'] = 'Ruangan berhasil dihapus.';
                        $this->redirect();
                    } catch (mysqli_sql_exception $exception) {
                        if ($exception->getCode() === 1451) {
                            $errors[] = 'Ruangan tidak dapat dihapus karena masih digunakan oleh jadwal kelas.';
                        } else {
                            $errors[] = 'Database gagal menghapus ruangan.';
                        }
                    }
                }
            } elseif ($action === 'create' || $action === 'update') {
                $formData = [
                    'kode' => strtoupper(trim((string)($_POST['kode'] ?? ''))),
                    'nama' => trim((string)($_POST['nama'] ?? '')),
                    'gedung' => trim((string)($_POST['gedung'] ?? '')),
                    'kapasitas' => filter_var(
                        $_POST['kapasitas'] ?? null,
                        FILTER_VALIDATE_INT,
                        ['options' => ['min_range' => 1, 'max_range' => 65535]]
                    ),
                ];

                if ($action === 'update') {
                    $editingId = $this->readId($_POST['id'] ?? null);
                    if ($editingId === null || $model->findById($editingId) === null) {
                        $errors[] = 'Ruangan yang akan diubah tidak ditemukan.';
                        $editingId = null;
                    }
                }

                if ($formData['kode'] === '' || strlen($formData['kode']) > 20) {
                    $errors[] = 'Kode ruangan wajib diisi dan maksimal 20 karakter.';
                }
                if ($formData['nama'] === '' || strlen($formData['nama']) > 100) {
                    $errors[] = 'Nama ruangan wajib diisi dan maksimal 100 karakter.';
                }
                if ($formData['gedung'] !== '' && strlen($formData['gedung']) > 100) {
                    $errors[] = 'Nama gedung maksimal 100 karakter.';
                }
                if ($formData['kapasitas'] === false) {
                    $errors[] = 'Kapasitas harus berupa angka antara 1 sampai 65.535.';
                }

                if ($errors === []) {
                    $formData['gedung'] = $formData['gedung'] === '' ? null : $formData['gedung'];
                    try {
                        if ($action === 'create') {
                            $model->create($formData);
                            $_SESSION['flash_success'] = 'Ruangan berhasil ditambahkan.';
                        } else {
                            $model->update($editingId, $formData);
                            $_SESSION['flash_success'] = 'Data ruangan berhasil diperbarui.';
                        }
                        $this->redirect();
                    } catch (mysqli_sql_exception $exception) {
                        if ($exception->getCode() === 1062) {
                            $errors[] = 'Kode atau nama ruangan tersebut sudah digunakan.';
                        } else {
                            $errors[] = 'Database gagal menyimpan data ruangan.';
                        }
                    }
                }
            } else {
                $errors[] = 'Aksi tidak dikenal.';
            }
        } elseif (($_GET['action'] ?? '') === 'edit') {
            $editingId = $this->readId($_GET['id'] ?? null);
            $room = $editingId === null ? null : $model->findById($editingId);

            if ($room === null) {
                $errors[] = 'Ruangan yang akan diubah tidak ditemukan.';
                $editingId = null;
            } else {
                $formData = $room;
            }
        }

        renderView('ruangan/index', [
            'ruangan' => $model->getAll(),
            'errors' => $errors,
            'success' => $success,
            'editingId' => $editingId,
            'formData' => $formData,
            'csrfToken' => $_SESSION['csrf_token'],
        ]);
    }

    private function emptyForm(): array
    {
        return ['kode' => '', 'nama' => '', 'gedung' => '', 'kapasitas' => 30];
    }

    private function readId($value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }

    private function redirect(): void
    {
        header('Location: ' . ($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=ruangan');
        exit;
    }
}
