<?php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Kelas
{
    public function getAll(): array
    {
        $sql = 'SELECT id, kode, nama
                FROM rombongan_belajar
                ORDER BY FIELD(kode, "KLS-DPS", "KLS-BGL", "KLS-KNY"), nama';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
}
