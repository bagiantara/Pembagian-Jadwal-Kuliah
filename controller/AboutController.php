<?php

require_once __DIR__ . '/../model/mahasiswa.php';

class AboutController {
    public function index(): void
    {
        $model = new Mahasiswa();
        $errors = [];
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        $cohorts = $model->getAllRombonganBelajar();
        $programStudi = $model->getAllProgramStudi();
        $classes = $model->getAllKelasMataKuliah();
        $editingStudent = null;
        $editingClassIds = null;
        $filterValue = trim((string)($_GET['filter_kelas'] ?? 'semua'));
        $requestedSearch = $_GET['q'] ?? '';
        $searchValue = is_string($requestedSearch) ? trim($requestedSearch) : '';
        $filterCohortId = null;

        if ($filterValue !== '' && $filterValue !== 'semua') {
            $requestedId = filter_var(
                $filterValue,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );
            $cohortIds = array_map(static fn(array $cohort): int => (int)$cohort['id'], $cohorts);
            if ($requestedId === false || !in_array($requestedId, $cohortIds, true)) {
                $errors[] = 'Filter kelas tidak valid.';
                $filterValue = 'semua';
            } else {
                $filterCohortId = $requestedId;
            }
        } elseif ($filterValue === '') {
            $filterValue = 'semua';
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = (string)($_POST['csrf_token'] ?? '');
            $action = (string)($_POST['action'] ?? '');
            $id = filter_var(
                $_POST['id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if (!hash_equals($_SESSION['csrf_token'], $token)) {
                $errors[] = 'Form tidak valid. Muat ulang halaman lalu coba lagi.';
            } elseif (!in_array($action, ['delete', 'update'], true)) {
                $errors[] = 'Aksi tidak dikenal.';
            } elseif ($id === false || $id === null || $model->findById($id) === null) {
                $errors[] = 'Data mahasiswa tidak ditemukan.';
            } elseif ($action === 'update') {
                $editingStudent = $model->findById($id);
                $editingStudent = array_merge($editingStudent, [
                    'nim' => trim((string)($_POST['nim'] ?? '')),
                    'nama' => trim((string)($_POST['nama'] ?? '')),
                    'email' => trim((string)($_POST['email'] ?? '')),
                    'program_studi_id' => $_POST['program_studi_id'] ?? '',
                    'rombongan_belajar_id' => $_POST['rombongan_belajar_id'] ?? '',
                    'semester_aktif' => $_POST['semester_aktif'] ?? '',
                ]);
                $submittedClassIds = $_POST['kelas_ids'] ?? [];
                $classIds = [];
                if (!is_array($submittedClassIds)) {
                    $errors[] = 'Pilihan mata kuliah tidak valid.';
                } else {
                    foreach ($submittedClassIds as $submittedClassId) {
                        $classId = filter_var(
                            $submittedClassId,
                            FILTER_VALIDATE_INT,
                            ['options' => ['min_range' => 1]]
                        );
                        if ($classId === false) {
                            $errors[] = 'Pilihan mata kuliah tidak valid.';
                            break;
                        }
                        $classIds[] = $classId;
                    }
                    $classIds = array_values(array_unique($classIds));
                    $editingClassIds = $classIds;
                }

                if ($editingStudent['nim'] === '' || strlen($editingStudent['nim']) > 30) {
                    $errors[] = 'NIM wajib diisi dan maksimal 30 karakter.';
                }
                if ($editingStudent['nama'] === '' || strlen($editingStudent['nama']) > 100) {
                    $errors[] = 'Nama wajib diisi dan maksimal 100 karakter.';
                }
                if ($editingStudent['email'] !== ''
                    && (!filter_var($editingStudent['email'], FILTER_VALIDATE_EMAIL)
                        || strlen($editingStudent['email']) > 150)) {
                    $errors[] = 'Format email tidak valid atau email melebihi 150 karakter.';
                }
                $programStudiId = filter_var(
                    $editingStudent['program_studi_id'],
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );
                if ($programStudiId === false || !$model->programStudiExists($programStudiId)) {
                    $errors[] = 'Silakan pilih program studi yang tersedia.';
                }
                $activeSemester = filter_var(
                    $editingStudent['semester_aktif'],
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1, 'max_range' => 7]]
                );
                if ($activeSemester === false) {
                    $errors[] = 'Semester aktif harus berupa angka antara 1 sampai 7.';
                }

                $cohortInput = $editingStudent['rombongan_belajar_id'];
                if ($cohortInput === '') {
                    $cohortId = null;
                } else {
                    $cohortId = filter_var(
                        $cohortInput,
                        FILTER_VALIDATE_INT,
                        ['options' => ['min_range' => 1]]
                    );
                    $cohortIds = array_map(static fn(array $cohort): int => (int)$cohort['id'], $cohorts);
                    if ($cohortId === false || !in_array($cohortId, $cohortIds, true)) {
                        $errors[] = 'Silakan pilih kelas mahasiswa yang tersedia.';
                    }
                }
                if ($classIds !== [] && !$model->allClassIdsExist($classIds)) {
                    $errors[] = 'Satu atau beberapa pilihan mata kuliah tidak tersedia.';
                }

                if ($errors === []) {
                    try {
                        $model->update($id, [
                            'nim' => $editingStudent['nim'],
                            'nama' => $editingStudent['nama'],
                            'email' => $editingStudent['email'] === '' ? null : $editingStudent['email'],
                            'program_studi_id' => $programStudiId,
                            'rombongan_belajar_id' => $cohortId,
                            'semester_aktif' => $activeSemester,
                        ], $classIds);
                        $_SESSION['flash_success'] = 'Data mahasiswa berhasil diperbarui.';
                        $this->redirect();
                    } catch (mysqli_sql_exception $exception) {
                        if ($exception->getCode() === 1062) {
                            $errors[] = 'NIM atau email tersebut sudah digunakan oleh mahasiswa lain.';
                        } else {
                            $errors[] = 'Database gagal menyimpan perubahan data mahasiswa.';
                        }
                    }
                }
            } else {
                try {
                    $model->delete($id);
                    $_SESSION['flash_success'] = 'Data mahasiswa berhasil dihapus.';
                    $this->redirect();
                } catch (mysqli_sql_exception $exception) {
                    $errors[] = 'Database gagal menghapus data mahasiswa.';
                }
            }
        } elseif (($_GET['action'] ?? '') === 'edit') {
            $editingId = filter_var(
                $_GET['id'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );
            $editingStudent = $editingId === false ? null : $model->findById($editingId);
            if ($editingStudent === null) {
                $errors[] = 'Data mahasiswa yang akan diubah tidak ditemukan.';
            }
        }

        renderView('about', [
            'mahasiswa' => $model->getAll($filterCohortId, $searchValue),
            'rombonganBelajar' => $cohorts,
            'programStudi' => $programStudi,
            'kelasMataKuliah' => $classes,
            'editingStudent' => $editingStudent,
            'enrolledClassIds' => $editingClassIds
                ?? ($editingStudent === null ? [] : $model->getEnrolledClassIds((int)$editingStudent['id'])),
            'filterValue' => $filterValue,
            'searchValue' => $searchValue,
            'errors' => $errors,
            'success' => $success,
            'csrfToken' => $_SESSION['csrf_token'],
        ]);
    }

    private function redirect(): void
    {
        header('Location: ' . ($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=about');
        exit;
    }
}