<?php

require_once __DIR__ . '/../model/mahasiswa.php';

class MahasiswaController
{
    public function index(): void
    {
        $model = new Mahasiswa();
        if ((currentUser()['role'] ?? null) === 'user') {
            $this->enroll($model);
            return;
        }

        $errors = [];
        $formData = [
            'nim' => '',
            'nama' => '',
            'email' => '',
            'program_studi_id' => '',
            'rombongan_belajar_id' => '',
        ];
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = (string)($_POST['action'] ?? '');
            $token = (string)($_POST['csrf_token'] ?? '');

            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                $errors[] = 'Form sudah kedaluwarsa atau tidak valid. Muat ulang halaman lalu coba lagi.';
            } elseif ($action === 'create') {
                $formData = [
                    'nim' => trim((string)($_POST['nim'] ?? '')),
                    'nama' => trim((string)($_POST['nama'] ?? '')),
                    'email' => trim((string)($_POST['email'] ?? '')),
                    'program_studi_id' => trim((string)($_POST['program_studi_id'] ?? '')),
                    'rombongan_belajar_id' => trim((string)($_POST['rombongan_belajar_id'] ?? '')),
                ];

                if ($formData['nim'] === '' || strlen($formData['nim']) > 30) {
                    $errors[] = 'NIM wajib diisi dan maksimal 30 karakter.';
                }
                if ($formData['nama'] === '' || strlen($formData['nama']) > 100) {
                    $errors[] = 'Nama wajib diisi dan maksimal 100 karakter.';
                }
                if ($formData['email'] !== '' && (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL) || strlen($formData['email']) > 150)) {
                    $errors[] = 'Format email tidak valid atau email melebihi 150 karakter.';
                }

                $programStudiId = filter_var(
                    $formData['program_studi_id'],
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );
                if ($programStudiId === false || !$model->programStudiExists($programStudiId)) {
                    $errors[] = 'Silakan pilih program studi yang tersedia.';
                } else {
                    $formData['program_studi_id'] = $programStudiId;
                }

                $rombonganBelajarId = filter_var(
                    $formData['rombongan_belajar_id'],
                    FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1]]
                );
                if ($rombonganBelajarId === false || !$model->rombonganBelajarExists($rombonganBelajarId)) {
                    $errors[] = 'Silakan pilih kelas mahasiswa yang tersedia.';
                } else {
                    $formData['rombongan_belajar_id'] = $rombonganBelajarId;
                }

                if ($errors === []) {
                    $formData['email'] = $formData['email'] === '' ? null : $formData['email'];
                    try {
                        $model->create($formData);
                        $_SESSION['flash_success'] = 'Data mahasiswa berhasil ditambahkan. Data dapat dilihat di halaman Data Mahasiswa.';
                        header('Location: ' . ($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=mahasiswa');
                        exit;
                    } catch (mysqli_sql_exception $exception) {
                        if ($exception->getCode() === 1062) {
                            $errors[] = 'NIM atau email tersebut sudah digunakan oleh mahasiswa lain.';
                        } else {
                            $errors[] = 'Database gagal menyimpan perubahan: ' . $exception->getMessage();
                        }
                    }
                }
            } else {
                $errors[] = 'Halaman ini hanya digunakan untuk menambahkan data mahasiswa.';
            }
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        renderView('mahasiswa/index', [
            'programStudi' => $model->getAllProgramStudi(),
            'rombonganBelajar' => $model->getAllRombonganBelajar(),
            'errors' => $errors,
            'formData' => $formData,
            'success' => $success,
            'csrfToken' => $_SESSION['csrf_token'],
        ]);
    }

    private function enroll(Mahasiswa $model): void
    {
        $studentId = (int)currentUser()['mahasiswa_id'];
        $student = $model->findById($studentId);
        $activeSemester = (int)($student['semester_aktif'] ?? 1);
        $classes = $model->getAllKelasMataKuliah();
        $errors = [];
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = (string)($_POST['csrf_token'] ?? '');
            $submittedIds = $_POST['kelas_ids'] ?? [];
            $classIds = [];
            if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
                $errors[] = 'Form tidak valid. Muat ulang halaman lalu coba lagi.';
            } elseif (($_POST['action'] ?? '') !== 'enroll') {
                $errors[] = 'Aksi tidak dikenal.';
            } elseif (!is_array($submittedIds) || $submittedIds === []) {
                $errors[] = 'Pilih minimal satu mata kuliah baru.';
            } else {
                foreach ($submittedIds as $submittedId) {
                    $classId = filter_var(
                        $submittedId,
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
                if ($errors === [] && !$model->classIdsBelongToSemester($classIds, $activeSemester)) {
                    $errors[] = 'Pilih mata kuliah yang tersedia untuk semester aktif Anda saja.';
                }
                $alreadyEnrolled = $model->getEnrolledClassIds($studentId);
                if ($errors === [] && array_intersect($classIds, $alreadyEnrolled) !== []) {
                    $errors[] = 'Pilih mata kuliah baru saja; pilihan yang sudah terdaftar tidak dapat dipilih ulang.';
                }
                if ($errors === []) {
                    try {
                        $model->addCourses($studentId, $classIds);
                        $_SESSION['flash_success'] = 'Pilihan mata kuliah berhasil ditambahkan.';
                        header('Location: ' . ($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=mahasiswa');
                        exit;
                    } catch (mysqli_sql_exception $exception) {
                        $errors[] = 'Database gagal menyimpan pilihan mata kuliah.';
                    }
                }
            }
        }

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        renderView('mahasiswa/enroll', [
            'student' => $student,
            'classes' => $classes,
            'semesterClasses' => array_values(array_filter(
                $classes,
                fn(array $class): bool => (int)$class['semester_ke'] === $activeSemester
            )),
            'enrolledIds' => $model->getEnrolledClassIds($studentId),
            'errors' => $errors,
            'success' => $success,
            'csrfToken' => $_SESSION['csrf_token'],
        ]);
    }
}
