<?php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Mahasiswa
{
    public function getAll(?int $cohortId = null, string $searchTerm = ''): array
    {
        $connection = Database::connection();
        $studentRows = $connection->query(
            'SELECT m.id, m.nim, m.nama, m.email, m.program_studi_id, m.semester_aktif,
                    m.rombongan_belajar_id, rb.nama AS nama_kelas_mahasiswa,
                    ps.nama AS prodi
             FROM mahasiswa m
             INNER JOIN program_studi ps ON ps.id = m.program_studi_id
             LEFT JOIN rombongan_belajar rb ON rb.id = m.rombongan_belajar_id
             ORDER BY m.nama, m.nim'
        )->fetch_all(MYSQLI_ASSOC);

        $students = [];
        foreach ($studentRows as $student) {
            $student['kelas'] = [];
            $student['mata_kuliah'] = [];
            $student['_locations'] = [];
            $student['_courses'] = [];
            $students[(int)$student['id']] = $student;
        }

        if ($students === []) {
            return [];
        }

        $enrollmentRows = $connection->query(
            'SELECT mkelas.mahasiswa_id, k.id AS kelas_id,
                    mk.nama AS nama_mata_kuliah,
                    k.kode AS kode_kelas, k.semester_ke, d.nama AS nama_dosen,
                    j.id AS jadwal_id, j.hari, j.jam_mulai, j.jam_selesai,
                    j.created_at AS jadwal_dibuat,
                    rb.id AS rombongan_belajar_id, rb.nama AS nama_rombongan_belajar,
                    r.nama AS nama_ruangan
             FROM mahasiswa_kelas mkelas
             INNER JOIN mahasiswa m ON m.id = mkelas.mahasiswa_id
             INNER JOIN kelas k ON k.id = mkelas.kelas_id
             INNER JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
             INNER JOIN dosen d ON d.id = k.dosen_id
             LEFT JOIN jadwal_perkuliahan j
                ON j.kelas_id = k.id
               AND (m.rombongan_belajar_id IS NULL
                    OR j.rombongan_belajar_id = m.rombongan_belajar_id)
             LEFT JOIN rombongan_belajar rb ON rb.id = j.rombongan_belajar_id
             LEFT JOIN ruangan r ON r.id = j.ruangan_id
             ORDER BY mkelas.mahasiswa_id, mk.nama, (j.id IS NULL),
                      j.created_at, j.id, k.id'
        )->fetch_all(MYSQLI_ASSOC);

        foreach ($enrollmentRows as $enrollment) {
            $studentId = (int)$enrollment['mahasiswa_id'];
            if (!isset($students[$studentId])) {
                continue;
            }

            $classId = (int)$enrollment['kelas_id'];
            if (isset($students[$studentId]['_courses'][$classId])) {
                continue;
            }

            $students[$studentId]['_courses'][$classId] = true;
            if ($enrollment['jadwal_id'] === null) {
                $courseSchedule = 'Jadwal belum diatur untuk kelas mahasiswa';
            } else {
                $courseSchedule = ($enrollment['nama_rombongan_belajar'] ?? 'Kelas belum dipilih')
                    . ' · ' . $enrollment['hari']
                    . ' ' . substr((string)$enrollment['jam_mulai'], 0, 5)
                    . '-' . substr((string)$enrollment['jam_selesai'], 0, 5)
                    . ' · ' . ($enrollment['nama_ruangan'] ?? 'Ruangan belum ditentukan');

                if ($enrollment['rombongan_belajar_id'] !== null) {
                    $students[$studentId]['_locations'][(int)$enrollment['rombongan_belajar_id']] = [
                        'id' => (int)$enrollment['rombongan_belajar_id'],
                        'nama' => $enrollment['nama_rombongan_belajar'],
                    ];
                }
            }

            $students[$studentId]['mata_kuliah'][] = [
                'nama' => $enrollment['nama_mata_kuliah'],
                'kode' => $enrollment['kode_kelas'],
                'semester_ke' => (int)$enrollment['semester_ke'],
                'dosen' => $enrollment['nama_dosen'],
                'jadwal' => $courseSchedule,
            ];
        }

        $result = [];
        foreach ($students as $student) {
            $locations = array_values($student['_locations']);
            usort($locations, static fn(array $first, array $second): int => strcmp($first['nama'], $second['nama']));
            $studentCohortId = $student['rombongan_belajar_id'] === null
                ? null
                : (int)$student['rombongan_belajar_id'];
            if ($cohortId !== null
                && $studentCohortId !== $cohortId
                && ($studentCohortId !== null || !isset($student['_locations'][$cohortId]))) {
                continue;
            }
            if ($searchTerm !== ''
                && stripos((string)$student['nim'], $searchTerm) === false
                && stripos((string)$student['nama'], $searchTerm) === false) {
                continue;
            }

            $student['kelas'] = $student['nama_kelas_mahasiswa']
                ?? ($locations === [] ? 'Belum dipilih' : implode(', ', array_column($locations, 'nama')));
            unset($student['_locations'], $student['_courses']);
            $result[] = $student;
        }

        return $result;
    }

    public function getAllRombonganBelajar(): array
    {
        $sql = 'SELECT id, kode, nama
                FROM rombongan_belajar
                ORDER BY FIELD(kode, "KLS-DPS", "KLS-BGL", "KLS-KNY"), nama';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, nim, nama, email, program_studi_id, rombongan_belajar_id,
                    semester_aktif
             FROM mahasiswa WHERE id = ?'
        );
        $statement->bind_param('i', $id);
        $statement->execute();
        $mahasiswa = $statement->get_result()->fetch_assoc();
        $statement->close();

        return $mahasiswa ?: null;
    }

    public function getAllProgramStudi(): array
    {
        $sql = 'SELECT id, kode, nama FROM program_studi ORDER BY nama';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllKelasMataKuliah(): array
    {
        $sql = 'SELECT k.id, k.kode AS kode_kelas, k.semester_ke, k.semester, k.tahun_ajaran,
                       mk.nama AS nama_mata_kuliah,
                       d.nama AS nama_dosen
                FROM kelas k
                INNER JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
                INNER JOIN dosen d ON d.id = k.dosen_id
                ORDER BY k.semester_ke, mk.nama, k.kode';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getEnrolledClassIds(int $mahasiswaId): array
    {
        $statement = Database::connection()->prepare(
            'SELECT kelas_id FROM mahasiswa_kelas WHERE mahasiswa_id = ? ORDER BY kelas_id'
        );
        $statement->bind_param('i', $mahasiswaId);
        $statement->execute();
        $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
        $statement->close();

        return array_map(static fn(array $row): int => (int)$row['kelas_id'], $rows);
    }

    public function addCourses(int $mahasiswaId, array $classIds): void
    {
        $connection = Database::connection();
        $connection->begin_transaction();
        try {
            $statement = $connection->prepare(
                'INSERT IGNORE INTO mahasiswa_kelas (mahasiswa_id, kelas_id) VALUES (?, ?)'
            );
            foreach ($classIds as $classId) {
                $statement->bind_param('ii', $mahasiswaId, $classId);
                $statement->execute();
            }
            $statement->close();
            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollback();
            throw $exception;
        }
    }

    public function programStudiExists(int $id): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT id FROM program_studi WHERE id = ?'
        );
        $statement->bind_param('i', $id);
        $statement->execute();
        $exists = $statement->get_result()->num_rows > 0;
        $statement->close();

        return $exists;
    }

    public function rombonganBelajarExists(int $id): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT id FROM rombongan_belajar WHERE id = ?'
        );
        $statement->bind_param('i', $id);
        $statement->execute();
        $exists = $statement->get_result()->num_rows > 0;
        $statement->close();

        return $exists;
    }

    public function allClassIdsExist(array $classIds): bool
    {
        if ($classIds === []) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($classIds), '?'));
        $statement = Database::connection()->prepare(
            "SELECT COUNT(*) AS total FROM kelas WHERE id IN ($placeholders)"
        );
        $types = str_repeat('i', count($classIds));
        $statement->bind_param($types, ...$classIds);
        $statement->execute();
        $total = (int)$statement->get_result()->fetch_assoc()['total'];
        $statement->close();

        return $total === count($classIds);
    }

    public function classIdsBelongToSemester(array $classIds, int $semester): bool
    {
        if ($classIds === [] || $semester < 1 || $semester > 7) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($classIds), '?'));
        $statement = Database::connection()->prepare(
            "SELECT COUNT(*) AS total FROM kelas
             WHERE semester_ke = ? AND id IN ($placeholders)"
        );
        $types = 'i' . str_repeat('i', count($classIds));
        $parameters = array_merge([$semester], $classIds);
        $statement->bind_param($types, ...$parameters);
        $statement->execute();
        $total = (int)$statement->get_result()->fetch_assoc()['total'];
        $statement->close();

        return $total === count($classIds);
    }

    public function create(array $data): void
    {
        $connection = Database::connection();
        $statement = $connection->prepare(
            'INSERT INTO mahasiswa (nim, nama, email, program_studi_id,
                                    rombongan_belajar_id, semester_aktif)
             VALUES (?, ?, ?, ?, ?, 1)'
        );
        $statement->bind_param(
            'sssii',
            $data['nim'],
            $data['nama'],
            $data['email'],
            $data['program_studi_id'],
            $data['rombongan_belajar_id']
        );
        $statement->execute();
        $statement->close();
    }

    public function update(int $id, array $data, array $classIds): void
    {
        $connection = Database::connection();
        $connection->begin_transaction();
        try {
            $statement = $connection->prepare(
                'UPDATE mahasiswa
                 SET nim = ?, nama = ?, email = ?, program_studi_id = ?,
                     rombongan_belajar_id = ?, semester_aktif = ?
                 WHERE id = ?'
            );
            $statement->bind_param(
                'sssiiii',
                $data['nim'],
                $data['nama'],
                $data['email'],
                $data['program_studi_id'],
                $data['rombongan_belajar_id'],
                $data['semester_aktif'],
                $id
            );
            $statement->execute();
            $statement->close();

            $deleteStatement = $connection->prepare('DELETE FROM mahasiswa_kelas WHERE mahasiswa_id = ?');
            $deleteStatement->bind_param('i', $id);
            $deleteStatement->execute();
            $deleteStatement->close();

            $linkStatement = $connection->prepare(
                'INSERT INTO mahasiswa_kelas (mahasiswa_id, kelas_id) VALUES (?, ?)'
            );
            foreach ($classIds as $classId) {
                $linkStatement->bind_param('ii', $id, $classId);
                $linkStatement->execute();
            }
            $linkStatement->close();
            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollback();
            throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $statement = Database::connection()->prepare('DELETE FROM mahasiswa WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
    }
}
