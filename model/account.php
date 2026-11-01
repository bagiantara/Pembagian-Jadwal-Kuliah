<?php

require_once __DIR__ . '/../config/DatabaseConnection.php';

class Account
{
    public function authenticate(string $username, string $password): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, username, password_hash, role, mahasiswa_id
             FROM users
             WHERE username = ? AND is_active = 1'
        );
        $statement->bind_param('s', $username);
        $statement->execute();
        $account = $statement->get_result()->fetch_assoc();
        $statement->close();

        if ($account === null || !password_verify($password, $account['password_hash'])) {
            return null;
        }

        unset($account['password_hash']);
        $account['id'] = (int)$account['id'];
        $account['mahasiswa_id'] = $account['mahasiswa_id'] === null
            ? null
            : (int)$account['mahasiswa_id'];

        return $account;
    }

    public function usernameExists(string $username): bool
    {
        $statement = Database::connection()->prepare('SELECT id FROM users WHERE username = ?');
        $statement->bind_param('s', $username);
        $statement->execute();
        $exists = $statement->get_result()->num_rows > 0;
        $statement->close();

        return $exists;
    }

    public function findById(int $id): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT u.id, u.username, u.role, u.mahasiswa_id, m.nama AS nama_mahasiswa
             FROM users u
             LEFT JOIN mahasiswa m ON m.id = u.mahasiswa_id
             WHERE u.id = ?'
        );
        $statement->bind_param('i', $id);
        $statement->execute();
        $account = $statement->get_result()->fetch_assoc();
        $statement->close();
        if ($account === null) {
            return null;
        }

        $account['id'] = (int)$account['id'];
        $account['mahasiswa_id'] = $account['mahasiswa_id'] === null
            ? null
            : (int)$account['mahasiswa_id'];

        return $account;
    }

    public function getAll(): array
    {
        $sql = 'SELECT u.id, u.username, u.role, u.is_active, m.nim, m.nama AS nama_mahasiswa
                FROM users u
                LEFT JOIN mahasiswa m ON m.id = u.mahasiswa_id
                ORDER BY u.role, u.username';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function getAvailableStudents(): array
    {
        $sql = 'SELECT m.id, m.nim, m.nama
                FROM mahasiswa m
                LEFT JOIN users u ON u.mahasiswa_id = m.id
                WHERE u.id IS NULL
                ORDER BY m.nama, m.nim';

        return Database::connection()->query($sql)->fetch_all(MYSQLI_ASSOC);
    }

    public function create(string $username, string $password, string $role, ?int $mahasiswaId): void
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $statement = Database::connection()->prepare(
            'INSERT INTO users (username, password_hash, role, mahasiswa_id)
             VALUES (?, ?, ?, ?)'
        );
        $statement->bind_param('sssi', $username, $passwordHash, $role, $mahasiswaId);
        $statement->execute();
        $statement->close();
    }

    public function createPendingStudentAccount(int $mahasiswaId): void
    {
        $usernameStatement = Database::connection()->prepare(
            'SELECT nim FROM mahasiswa WHERE id = ?'
        );
        $usernameStatement->bind_param('i', $mahasiswaId);
        $usernameStatement->execute();
        $student = $usernameStatement->get_result()->fetch_assoc();
        $usernameStatement->close();
        if ($student === null) {
            throw new RuntimeException('Mahasiswa tidak ditemukan.');
        }

        $placeholderHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
        $role = 'user';
        $statement = Database::connection()->prepare(
            'INSERT INTO users (username, password_hash, role, mahasiswa_id, is_active)
             VALUES (?, ?, ?, ?, 0)'
        );
        $statement->bind_param('sssi', $student['nim'], $placeholderHash, $role, $mahasiswaId);
        $statement->execute();
        $statement->close();
    }

    public function registerStudent(string $nim, string $email, string $password): bool
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $statement = Database::connection()->prepare(
            "INSERT INTO users (username, password_hash, role, mahasiswa_id)
             SELECT m.nim, ?, 'user', m.id
             FROM mahasiswa m
             WHERE m.nim = ? AND m.email = ? AND m.email IS NOT NULL
             ON DUPLICATE KEY UPDATE
                password_hash = IF(is_active = 0 AND role = 'user', VALUES(password_hash), password_hash),
                is_active = IF(is_active = 0 AND role = 'user', 1, is_active)"
        );
        $statement->bind_param('sss', $passwordHash, $nim, $email);
        $statement->execute();
        $registered = in_array($statement->affected_rows, [1, 2], true);
        $statement->close();

        return $registered;
    }

    public function delete(int $id): void
    {
        $statement = Database::connection()->prepare('DELETE FROM users WHERE id = ?');
        $statement->bind_param('i', $id);
        $statement->execute();
        $statement->close();
    }

    public function isLastAdmin(int $id): bool
    {
        $statement = Database::connection()->prepare(
            "SELECT COUNT(*) AS total FROM users WHERE role = 'admin' AND id != ?"
        );
        $statement->bind_param('i', $id);
        $statement->execute();
        $total = (int)$statement->get_result()->fetch_assoc()['total'];
        $statement->close();

        return $total === 0;
    }
}
