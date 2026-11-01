USE akademik_mvc;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL,
    mahasiswa_id INT UNSIGNED NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_mahasiswa
        FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE users
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER mahasiswa_id;
