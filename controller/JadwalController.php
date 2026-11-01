<?php

require_once __DIR__ . '/../model/jadwal.php';

class JadwalController
{
    private const DAYS = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function index(): void
    {
        $model = new Jadwal();
        $cohorts = $model->getCohorts();
        $errors = [];
        $success = $_SESSION['flash_success'] ?? null;
        unset($_SESSION['flash_success']);
        $editingId = null;
        $filterValue = trim((string)($_GET['filter_kelas'] ?? ''));
        $filterCohortId = null;
        $unassignedOnly = false;

        if ($filterValue === 'belum_dipilih') {
            $unassignedOnly = true;
        } elseif ($filterValue !== '' && $filterValue !== 'semua') {
            $filterCohortId = $this->readId($filterValue);
            if ($filterCohortId === null || !$model->cohortExists($filterCohortId)) {
                $errors[] = 'Filter kelas tidak valid.';
                $filterCohortId = null;
                $filterValue = '';
            }
        }

        $formData = $this->emptyForm($filterCohortId);

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
                    $errors[] = 'Jadwal tidak ditemukan.';
                } else {
                    $model->delete($id);
                    $_SESSION['flash_success'] = 'Jadwal berhasil dihapus.';
                    $this->redirect();
                }
            } elseif ($action === 'create' || $action === 'update') {
                $formData = [
                    'kelas_id' => $this->readId($_POST['kelas_id'] ?? null),
                    'ruangan_id' => $this->readId($_POST['ruangan_id'] ?? null),
                    'rombongan_belajar_id' => $this->readId($_POST['rombongan_belajar_id'] ?? null),
                    'hari' => trim((string)($_POST['hari'] ?? '')),
                    'jam_mulai' => trim((string)($_POST['jam_mulai'] ?? '')),
                    'jam_selesai' => trim((string)($_POST['jam_selesai'] ?? '')),
                ];

                if ($action === 'update') {
                    $editingId = $this->readId($_POST['id'] ?? null);
                    if ($editingId === null || $model->findById($editingId) === null) {
                        $errors[] = 'Jadwal yang akan diubah tidak ditemukan.';
                        $editingId = null;
                    }
                }

                if ($formData['kelas_id'] === null || !$model->classExists($formData['kelas_id'])) {
                    $errors[] = 'Pilih kelas yang sudah memiliki mata kuliah dan dosen.';
                }
                if ($formData['ruangan_id'] === null || !$model->roomExists($formData['ruangan_id'])) {
                    $errors[] = 'Pilih ruangan dari daftar ruangan yang tersedia.';
                }
                if (
                    $formData['rombongan_belajar_id'] === null
                    || !$model->cohortExists($formData['rombongan_belajar_id'])
                ) {
                    $errors[] = 'Pilih kelas Denpasar, Bangli, atau Kenyeri.';
                }
                if (!in_array($formData['hari'], self::DAYS, true)) {
                    $errors[] = 'Pilih hari kuliah yang valid.';
                }
                if (!$this->isValidTime($formData['jam_mulai']) || !$this->isValidTime($formData['jam_selesai'])) {
                    $errors[] = 'Jam mulai dan selesai harus menggunakan format waktu yang valid.';
                } elseif ($formData['jam_mulai'] >= $formData['jam_selesai']) {
                    $errors[] = 'Jam selesai harus lebih akhir daripada jam mulai.';
                }
                if (
                    $errors === []
                    && $model->classAlreadyScheduledForCohort($formData, $editingId)
                ) {
                    $errors[] = 'Mata kuliah dan kelas ini sudah memiliki jadwal untuk lokasi mahasiswa yang dipilih. Edit jadwal yang sudah ada agar tidak tercatat dua kali.';
                }
                if ($errors === [] && $model->hasConflict($formData, $editingId)) {
                    $errors[] = 'Jadwal bentrok dengan kelas, dosen, atau ruang lain pada waktu tersebut.';
                }

                if ($errors === []) {
                    try {
                        if ($action === 'create') {
                            $model->create($formData);
                            $_SESSION['flash_success'] = 'Jadwal kelas berhasil ditambahkan.';
                        } else {
                            $model->update($editingId, $formData);
                            $_SESSION['flash_success'] = 'Jadwal kelas berhasil diperbarui.';
                        }
                        $this->redirect();
                    } catch (mysqli_sql_exception $exception) {
                        if ($exception->getCode() === 1062) {
                            $errors[] = 'Mata kuliah ini sudah memiliki jadwal untuk kelas mahasiswa tersebut. Edit jadwal yang ada agar tidak tercatat dua kali.';
                        } else {
                            $errors[] = 'Database gagal menyimpan jadwal kelas.';
                        }
                    }
                }
            } else {
                $errors[] = 'Aksi tidak dikenal.';
            }
        } elseif (($_GET['action'] ?? '') === 'edit') {
            $editingId = $this->readId($_GET['id'] ?? null);
            $schedule = $editingId === null ? null : $model->findById($editingId);

            if ($schedule === null) {
                $errors[] = 'Jadwal yang akan diubah tidak ditemukan.';
                $editingId = null;
            } else {
                $formData = $schedule;
            }
        }

        renderView('jadwal/index', [
            'jadwal' => $model->getAll($filterCohortId, $unassignedOnly),
            'kelas' => $model->getClasses(),
            'rombonganBelajar' => $cohorts,
            'ruangan' => $model->getRooms(),
            'errors' => $errors,
            'success' => $success,
            'editingId' => $editingId,
            'formData' => $formData,
            'csrfToken' => $_SESSION['csrf_token'],
            'days' => self::DAYS,
            'filterValue' => $filterValue,
            'unassignedCount' => count($model->getAll(null, true)),
        ]);
    }

    private function emptyForm(?int $cohortId = null): array
    {
        return [
            'kelas_id' => '',
            'ruangan_id' => '',
            'rombongan_belajar_id' => $cohortId ?? '',
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
        ];
    }

    private function isValidTime(string $time): bool
    {
        $date = DateTime::createFromFormat('!H:i', $time);

        return $date !== false && $date->format('H:i') === $time;
    }

    private function readId($value): ?int
    {
        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? null : $id;
    }

    private function redirect(): void
    {
        header('Location: ' . ($_SERVER['SCRIPT_NAME'] ?? 'index.php') . '?url=jadwal');
        exit;
    }
}
