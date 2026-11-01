<?php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class MataKuliah
{
    public function getAll(): array
    {
        $sql = 'SELECT mk.id, mk.nama, mk.sks,
                       k.id AS kelas_id, k.kode AS kode_kelas,
                       k.semester_ke, k.semester, k.tahun_ajaran,
                       d.id AS dosen_id, d.nama AS nama_dosen
                FROM mata_kuliah mk
                LEFT JOIN kelas k ON k.mata_kuliah_id = mk.id
                LEFT JOIN dosen d ON d.id = k.dosen_id
                ORDER BY k.semester_ke, mk.nama, k.kode';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllDosen(): array
    {
        $sql = 'SELECT id, nidn, nama FROM dosen ORDER BY nama, nidn';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function lecturerExists(int $id): bool
    {
        $statement = Database::connection()->prepare('SELECT id FROM dosen WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $exists = $statement->get_result()->num_rows > 0;
        $statement->close();

        return $exists;
    }

    public function lecturerHasScheduleConflict(int $classId, int $lecturerId): bool
    {
        $statement = Database::connection()->prepare(
            'SELECT COUNT(*) AS total
             FROM jadwal_perkuliahan current_schedule
             INNER JOIN jadwal_perkuliahan other_schedule
                 ON other_schedule.hari = current_schedule.hari
                AND other_schedule.jam_mulai < current_schedule.jam_selesai
                AND other_schedule.jam_selesai > current_schedule.jam_mulai
                AND other_schedule.id <> current_schedule.id
             INNER JOIN kelas other_class ON other_class.id = other_schedule.kelas_id
             WHERE current_schedule.kelas_id = ?
               AND other_class.dosen_id = ?'
        );
        $statement->bind_param('ii', $classId, $lecturerId);
        $statement->execute();
        $total = (int)$statement->get_result()->fetch_assoc()['total'];
        $statement->close();

        return $total > 0;
    }

    public function findById(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT mk.id, mk.nama, mk.sks,
                    k.id AS kelas_id, k.kode AS kode_kelas,
                    k.semester_ke, k.semester, k.tahun_ajaran, k.dosen_id
             FROM mata_kuliah mk
             LEFT JOIN kelas k ON k.mata_kuliah_id = mk.id
             WHERE mk.id = ?
             ORDER BY k.id
             LIMIT 1'
        );
        $statement->bind_param('i', $id);
        $statement->execute();
        $course = $statement->get_result()->fetch_assoc();
        $statement->close();

        return $course ?: null;
    }

    public function create(array $data): void
    {
        $connection = Database::connection();
        $connection->begin_transaction();

        try {
            $courseStatement = $connection->prepare(
                'INSERT INTO mata_kuliah (nama, sks) VALUES (?, ?)'
            );
            $courseStatement->bind_param('si', $data['nama'], $data['sks']);
            $courseStatement->execute();
            $courseId = $connection->insert_id;
            $courseStatement->close();

            $this->saveClass($connection, $courseId, null, $data);
            $connection->commit();
        } catch (mysqli_sql_exception $exception) {
            $connection->rollback();
            throw $exception;
        }
    }

    public function update(int $id, array $data): void
    {
        $connection = Database::connection();
        $connection->begin_transaction();

        try {
            $courseStatement = $connection->prepare(
                'UPDATE mata_kuliah SET nama = ?, sks = ? WHERE id = ?'
            );
            $courseStatement->bind_param('sii', $data['nama'], $data['sks'], $id);
            $courseStatement->execute();
            $courseStatement->close();

            $this->saveClass($connection, $id, $data['kelas_id'] ?? null, $data);
            $connection->commit();
        } catch (mysqli_sql_exception $exception) {
            $connection->rollback();
            throw $exception;
        }
    }

    public function delete(int $id): bool
    {
        $connection = Database::connection();
        $connection->begin_transaction();

        try {
            $statement = $connection->prepare(
                'SELECT COUNT(*) AS total
                 FROM mahasiswa_kelas mk
                 INNER JOIN kelas k ON k.id = mk.kelas_id
                 WHERE k.mata_kuliah_id = ?'
            );
            $statement->bind_param('i', $id);
            $statement->execute();
            $enrollmentCount = (int)$statement->get_result()->fetch_assoc()['total'];
            $statement->close();

            if ($enrollmentCount > 0) {
                $connection->rollback();
                return false;
            }

            $classStatement = $connection->prepare('DELETE FROM kelas WHERE mata_kuliah_id = ?');
            $classStatement->bind_param('i', $id);
            $classStatement->execute();
            $classStatement->close();

            $courseStatement = $connection->prepare('DELETE FROM mata_kuliah WHERE id = ?');
            $courseStatement->bind_param('i', $id);
            $courseStatement->execute();
            $courseStatement->close();
            $connection->commit();

            return true;
        } catch (mysqli_sql_exception $exception) {
            $connection->rollback();
            throw $exception;
        }
    }

    private function saveClass(mysqli $connection, int $courseId, ?int $classId, array $data): void
    {
        if ($classId !== null) {
            $statement = $connection->prepare(
                'UPDATE kelas
                 SET kode = ?, dosen_id = ?, semester_ke = ?, semester = ?, tahun_ajaran = ?
                 WHERE id = ? AND mata_kuliah_id = ?'
            );
            $statement->bind_param(
                'siissii',
                $data['kode_kelas'],
                $data['dosen_id'],
                $data['semester_ke'],
                $data['semester'],
                $data['tahun_ajaran'],
                $classId,
                $courseId
            );
            $statement->execute();
            $statement->close();
            return;
        }

        $statement = $connection->prepare(
            'INSERT INTO kelas (kode, mata_kuliah_id, dosen_id, semester_ke, semester, tahun_ajaran)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $statement->bind_param(
            'siiiss',
            $data['kode_kelas'],
            $courseId,
            $data['dosen_id'],
            $data['semester_ke'],
            $data['semester'],
            $data['tahun_ajaran']
        );
        $statement->execute();
        $statement->close();
    }
}
