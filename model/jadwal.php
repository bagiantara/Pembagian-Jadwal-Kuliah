<?php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Jadwal
{
    public function getAll(?int $cohortId = null, bool $unassignedOnly = false): array
    {
        $sql = 'SELECT j.id, j.kelas_id, j.ruangan_id, j.hari, j.jam_mulai, j.jam_selesai,
                       r.kode AS kode_ruangan, r.nama AS nama_ruangan, r.gedung,
                       rb.id AS rombongan_belajar_id, rb.nama AS nama_rombongan_belajar,
                       k.kode AS kode_kelas, k.semester, k.tahun_ajaran,
                       mk.nama AS nama_mata_kuliah, mk.sks,
                       d.id AS dosen_id, d.nama AS nama_dosen, d.nidn
                FROM jadwal_perkuliahan j
                INNER JOIN ruangan r ON r.id = j.ruangan_id
                LEFT JOIN rombongan_belajar rb ON rb.id = j.rombongan_belajar_id
                INNER JOIN kelas k ON k.id = j.kelas_id
                INNER JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
                INNER JOIN dosen d ON d.id = k.dosen_id';

        if ($cohortId !== null) {
            $sql .= ' WHERE j.rombongan_belajar_id = ?';
        } elseif ($unassignedOnly) {
            $sql .= ' WHERE j.rombongan_belajar_id IS NULL';
        }

        $sql .= ' ORDER BY FIELD(j.hari, "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"),
                          j.jam_mulai, mk.nama, k.kode';

        $connection = Database::connection();
        if ($cohortId !== null) {
            $statement = $connection->prepare($sql);
            $statement->bind_param('i', $cohortId);
            $statement->execute();
            $schedules = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
            $statement->close();

            return $schedules;
        }

        return $connection->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getCohorts(): array
    {
        $sql = 'SELECT id, kode, nama
                FROM rombongan_belajar
                ORDER BY FIELD(kode, "KLS-DPS", "KLS-BGL", "KLS-KNY"), nama';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getClasses(): array
    {
        $sql = 'SELECT k.id, k.kode, k.semester, k.tahun_ajaran,
                       mk.nama AS nama_mata_kuliah,
                       d.nama AS nama_dosen, d.nidn
                FROM kelas k
                INNER JOIN mata_kuliah mk ON mk.id = k.mata_kuliah_id
                INNER JOIN dosen d ON d.id = k.dosen_id
                ORDER BY mk.nama, k.kode';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getRooms(): array
    {
        $sql = 'SELECT id, kode, nama, gedung, kapasitas
                FROM ruangan
                ORDER BY nama, kode';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, kelas_id, ruangan_id, rombongan_belajar_id, hari,
                    TIME_FORMAT(jam_mulai, "%H:%i") AS jam_mulai,
                    TIME_FORMAT(jam_selesai, "%H:%i") AS jam_selesai
             FROM jadwal_perkuliahan
             WHERE id = ?'
        );
        $statement->bind_param('i', $id);
        $statement->execute();
        $schedule = $statement->get_result()->fetch_assoc();
        $statement->close();

        return $schedule ?: null;
    }

    public function classExists(int $id): bool
    {
        $statement = Database::connection()->prepare('SELECT id FROM kelas WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $exists = $statement->get_result()->num_rows > 0;
        $statement->close();

        return $exists;
    }

    public function roomExists(int $id): bool
    {
        $statement = Database::connection()->prepare('SELECT id FROM ruangan WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $exists = $statement->get_result()->num_rows > 0;
        $statement->close();

        return $exists;
    }

    public function cohortExists(int $id): bool
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

    public function hasConflict(array $data, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) AS total
                FROM jadwal_perkuliahan j
                INNER JOIN kelas existing_class ON existing_class.id = j.kelas_id
                INNER JOIN kelas requested_class ON requested_class.id = ?
                WHERE j.hari = ?
                  AND j.jam_mulai < ?
                  AND j.jam_selesai > ?
                  AND (
                      j.kelas_id = ?
                      OR existing_class.dosen_id = requested_class.dosen_id
                      OR j.ruangan_id = ?
                  )';

        if ($excludeId !== null) {
            $sql .= ' AND j.id <> ?';
        }

        $statement = Database::connection()->prepare($sql);
        if ($excludeId === null) {
            $statement->bind_param(
                'isssii',
                $data['kelas_id'],
                $data['hari'],
                $data['jam_selesai'],
                $data['jam_mulai'],
                $data['kelas_id'],
                $data['ruangan_id']
            );
        } else {
            $statement->bind_param(
                'isssiii',
                $data['kelas_id'],
                $data['hari'],
                $data['jam_selesai'],
                $data['jam_mulai'],
                $data['kelas_id'],
                $data['ruangan_id'],
                $excludeId
            );
        }
        $statement->execute();
        $total = (int)$statement->get_result()->fetch_assoc()['total'];
        $statement->close();

        return $total > 0;
    }

    public function classAlreadyScheduledForCohort(array $data, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) AS total
                FROM jadwal_perkuliahan
                WHERE kelas_id = ?
                  AND rombongan_belajar_id = ?';

        if ($excludeId !== null) {
            $sql .= ' AND id <> ?';
        }

        $statement = Database::connection()->prepare($sql);
        if ($excludeId === null) {
            $statement->bind_param(
                'ii',
                $data['kelas_id'],
                $data['rombongan_belajar_id']
            );
        } else {
            $statement->bind_param(
                'iii',
                $data['kelas_id'],
                $data['rombongan_belajar_id'],
                $excludeId
            );
        }

        $statement->execute();
        $total = (int)$statement->get_result()->fetch_assoc()['total'];
        $statement->close();

        return $total > 0;
    }

    public function create(array $data): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO jadwal_perkuliahan
                (kelas_id, ruangan_id, rombongan_belajar_id, hari, jam_mulai, jam_selesai)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $statement->bind_param(
            'iiisss',
            $data['kelas_id'],
            $data['ruangan_id'],
            $data['rombongan_belajar_id'],
            $data['hari'],
            $data['jam_mulai'],
            $data['jam_selesai']
        );
        $statement->execute();
        $statement->close();
    }

    public function update(int $id, array $data): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE jadwal_perkuliahan
             SET kelas_id = ?, ruangan_id = ?, rombongan_belajar_id = ?,
                 hari = ?, jam_mulai = ?, jam_selesai = ?
             WHERE id = ?'
        );
        $statement->bind_param(
            'iiisssi',
            $data['kelas_id'],
            $data['ruangan_id'],
            $data['rombongan_belajar_id'],
            $data['hari'],
            $data['jam_mulai'],
            $data['jam_selesai'],
            $id
        );
        $statement->execute();
        $statement->close();
    }

    public function delete(int $id): void
    {
        $statement = Database::connection()->prepare('DELETE FROM jadwal_perkuliahan WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
    }
}
