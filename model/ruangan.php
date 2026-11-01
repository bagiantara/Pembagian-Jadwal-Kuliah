<?php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Ruangan
{
    public function getAll(): array
    {
        $sql = 'SELECT r.id, r.kode, r.nama, r.gedung, r.kapasitas,
                       COUNT(j.id) AS jumlah_jadwal
                FROM ruangan r
                LEFT JOIN jadwal_perkuliahan j ON j.ruangan_id = r.id
                GROUP BY r.id, r.kode, r.nama, r.gedung, r.kapasitas
                ORDER BY r.nama, r.kode';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, kode, nama, gedung, kapasitas FROM ruangan WHERE id = ?'
        );
        $statement->bind_param('i', $id);
        $statement->execute();
        $room = $statement->get_result()->fetch_assoc();
        $statement->close();

        return $room ?: null;
    }

    public function create(array $data): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO ruangan (kode, nama, gedung, kapasitas) VALUES (?, ?, ?, ?)'
        );
        $statement->bind_param('sssi', $data['kode'], $data['nama'], $data['gedung'], $data['kapasitas']);
        $statement->execute();
        $statement->close();
    }

    public function update(int $id, array $data): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE ruangan SET kode = ?, nama = ?, gedung = ?, kapasitas = ? WHERE id = ?'
        );
        $statement->bind_param(
            'sssii',
            $data['kode'],
            $data['nama'],
            $data['gedung'],
            $data['kapasitas'],
            $id
        );
        $statement->execute();
        $statement->close();
    }

    public function delete(int $id): void
    {
        $statement = Database::connection()->prepare('DELETE FROM ruangan WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
    }
}
