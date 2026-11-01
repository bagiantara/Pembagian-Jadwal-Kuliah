<?php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Dosen
{
    public function getAll(): array
    {
        $sql = 'SELECT id, nidn, nama, email FROM dosen ORDER BY nama, nidn';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, nidn, nama, email FROM dosen WHERE id = ?'
        );
        $statement->bind_param('i', $id);
        $statement->execute();
        $lecturer = $statement->get_result()->fetch_assoc();
        $statement->close();

        return $lecturer ?: null;
    }

    public function create(array $data): void
    {
        $statement = Database::connection()->prepare(
            'INSERT INTO dosen (nidn, nama, email) VALUES (?, ?, ?)'
        );
        $statement->bind_param('sss', $data['nidn'], $data['nama'], $data['email']);
        $statement->execute();
        $statement->close();
    }

    public function update(int $id, array $data): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE dosen SET nidn = ?, nama = ?, email = ? WHERE id = ?'
        );
        $statement->bind_param('sssi', $data['nidn'], $data['nama'], $data['email'], $id);
        $statement->execute();
        $statement->close();
    }

    public function delete(int $id): void
    {
        $statement = Database::connection()->prepare('DELETE FROM dosen WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
    }
}
