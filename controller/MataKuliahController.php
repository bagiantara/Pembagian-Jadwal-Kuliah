<?php

require_once __DIR__ . '/../model/mata_kuliah.php';

class MataKuliahController
{
    public function index(): void
    {
        $model = new MataKuliah();
        $errors = [];
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        $editingId = null;
        $originalLecturerId = null;
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
                    $errors[] = 'Mata kuliah tidak ditemukan.';
                } elseif (!$model->delete($id)) {
                    $errors[] = 'Mata kuliah tidak dapat dihapus karena masih dipilih oleh mahasiswa.';
                } else {
                    $_SESSION['flash_success'] = 'Mata kuliah berhasil dihapus.';
                    $this->redirect();
                }
            } elseif ($action === 'create' || $action === 'update') {
                $formData = [
                    'nama' => trim((string)($_POST['nama'] ?? '')),
                    'sks' => trim((string)($_POST['sks'] ?? '')),
                    'kode_kelas' => trim((string)($_POST['kode_kelas'] ?? '')),
                    'semester_ke' => filter_var(
                        $_POST['semester_ke'] ?? null,
                        FILTER_VALIDATE_INT,
                        ['options' => ['min_range' => 1, 'max_range' => 7]]
                    ),
                    'semester' => trim((string)($_POST['semester'] ?? '')),
                    'tahun_ajaran' => trim((string)($_POST['tahun_ajaran'] ?? '')),
                    'dosen_id' => trim((string)($_POST['dosen_id'] ?? '')),
                    'kelas_id' => null,
                ];

                if ($action === 'update') {
                    $editingId = $this->readId($_POST['id'] ?? null);
                    $existing = $editingId === null ? null : $model->findById($editingId);
                    if ($existing === null) {
                        $errors[] = 'Mata kuliah yang akan diubah tidak ditemukan.';
                        $editingId = null;
                    } else {
                        $formData['kelas_id'] = $existing['kelas_id'];
                        $originalLecturerId = (int)$existing['dosen_id'];
                    }
                }

                $formData['sks'] = filter_var(
                    $formData['sks'],
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1, 'max_range' => 6]]
                );
                $formData['dosen_id'] = $this->readId($formData['dosen_id']);

                if ($formData['nama'] === '' || strlen($formData['nama']) > 100) {
                    $errors[] = 'Nama mata kuliah wajib diisi dan maksimal 100 karakter.';
                }
                if ($formData['sks'] === false) {
                    $errors[] = 'Jumlah SKS harus berupa angka antara 1 sampai 6.';
                }
                if ($formData['kode_kelas'] === '' || strlen($formData['kode_kelas']) > 20) {
                    $errors[] = 'Kode kelas wajib diisi dan maksimal 20 karakter.';
                }
                if ($formData['semester_ke'] === false) {
                    $errors[] = 'Semester kurikulum harus dipilih antara 1 sampai 7.';
                }
                if (!in_array($formData['semester'], ['Ganjil', 'Genap'], true)) {
                    $errors[] = 'Silakan pilih semester Ganjil atau Genap.';
                }
                if ($formData['tahun_ajaran'] === '' || strlen($formData['tahun_ajaran']) > 9) {
                    $errors[] = 'Tahun ajaran wajib diisi dan maksimal 9 karakter.';
                }
                if ($formData['dosen_id'] === null || !$model->lecturerExists($formData['dosen_id'])) {
                    $errors[] = 'Silakan pilih dosen dari halaman Data Dosen.';
                }
                if (
                    $errors === []
                    && $action === 'update'
                    && $formData['kelas_id'] !== null
                    && $formData['dosen_id'] !== $originalLecturerId
                    && $model->lecturerHasScheduleConflict($formData['kelas_id'], $formData['dosen_id'])
                ) {
                    $errors[] = 'Dosen tersebut sudah memiliki jadwal kelas lain yang bentrok pada waktu yang sama.';
                }

                if ($errors === []) {
                    try {
                        if ($action === 'create') {
                            $model->create($formData);
                            $_SESSION['flash_success'] = 'Mata kuliah dan kelas berhasil ditambahkan.';
                        } else {
                            $model->update($editingId, $formData);
                            $_SESSION['flash_success'] = 'Mata kuliah berhasil diperbarui.';
                        }
                        $this->redirect();
                    } catch (mysqli_sql_exception $exception) {
                        if ($exception->getCode() === 1062) {
                            $errors[] = 'Nama mata kuliah atau kode kelas sudah digunakan.';
                        } else {
                            $errors[] = 'Database gagal menyimpan mata kuliah. Periksa relasi dan data yang dimasukkan.';
                        }
                    }
                }
            } else {
                $errors[] = 'Aksi tidak dikenal.';
            }
        } elseif (($_GET['action'] ?? '') === 'edit') {
            $editingId = $this->readId($_GET['id'] ?? null);
            $existing = $editingId === null ? null : $model->findById($editingId);
            if ($existing === null) {
                $errors[] = 'Mata kuliah yang akan diubah tidak ditemukan.';
                $editingId = null;
            } else {
                $formData = $existing;
            }
        }

        renderView('mata_kuliah/index', [
            'mataKuliah' => $model->getAll(),
            'dosen' => $model->getAllDosen(),
            'errors' => $errors,
            'success' => $success,
            'editingId' => $editingId,
            'formData' => $formData,
            'csrfToken' => $_SESSION['csrf_token'],
        ]);
    }

    private function emptyForm(): array
    {
        return [
            'nama' => '',
            'sks' => '',
            'kode_kelas' => '',
            'semester_ke' => 1,
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2026/2027',
            'dosen_id' => '',
            'kelas_id' => null,
        ];
    }

    private function readId($value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }

    private function redirect(): void
    {
        header('Location: ' . ($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=mata_kuliah');
        exit;
    }
}
