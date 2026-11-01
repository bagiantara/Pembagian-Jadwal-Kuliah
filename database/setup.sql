CREATE DATABASE IF NOT EXISTS akademik_mvc
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE akademik_mvc;

CREATE TABLE IF NOT EXISTS program_studi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(10) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS dosen (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nidn VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rombongan_belajar (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mahasiswa (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nim VARCHAR(30) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(150) NULL UNIQUE,
    program_studi_id INT UNSIGNED NOT NULL,
    rombongan_belajar_id INT UNSIGNED NULL,
    semester_aktif TINYINT UNSIGNED NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_mahasiswa_semester_aktif CHECK (semester_aktif BETWEEN 1 AND 7),
    CONSTRAINT fk_mahasiswa_program_studi
        FOREIGN KEY (program_studi_id) REFERENCES program_studi(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_mahasiswa_rombongan_belajar
        FOREIGN KEY (rombongan_belajar_id) REFERENCES rombongan_belajar(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL,
    mahasiswa_id INT UNSIGNED NULL UNIQUE,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_mahasiswa
        FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mata_kuliah (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL UNIQUE,
    sks TINYINT UNSIGNED NOT NULL,
    CONSTRAINT chk_mata_kuliah_sks CHECK (sks BETWEEN 1 AND 6)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kelas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    mata_kuliah_id INT UNSIGNED NOT NULL,
    dosen_id INT UNSIGNED NOT NULL,
    semester_ke TINYINT UNSIGNED NOT NULL DEFAULT 1,
    semester ENUM('Ganjil', 'Genap') NOT NULL,
    tahun_ajaran VARCHAR(9) NOT NULL,
    CONSTRAINT chk_kelas_semester_ke CHECK (semester_ke BETWEEN 1 AND 7),
    CONSTRAINT fk_kelas_mata_kuliah
        FOREIGN KEY (mata_kuliah_id) REFERENCES mata_kuliah(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_kelas_dosen
        FOREIGN KEY (dosen_id) REFERENCES dosen(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ruangan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL UNIQUE,
    gedung VARCHAR(100) NULL,
    kapasitas SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS jadwal_perkuliahan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kelas_id INT UNSIGNED NOT NULL,
    ruangan_id INT UNSIGNED NOT NULL,
    rombongan_belajar_id INT UNSIGNED NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_jadwal_hari_waktu (hari, jam_mulai, jam_selesai),
    INDEX idx_jadwal_kelas (kelas_id),
    INDEX idx_jadwal_ruangan (ruangan_id),
    INDEX idx_jadwal_rombongan_belajar (rombongan_belajar_id),
    CONSTRAINT uq_jadwal_kelas_rombongan UNIQUE (kelas_id, rombongan_belajar_id),
    CONSTRAINT fk_jadwal_kelas
        FOREIGN KEY (kelas_id) REFERENCES kelas(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_jadwal_ruangan
        FOREIGN KEY (ruangan_id) REFERENCES ruangan(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_jadwal_rombongan_belajar
        FOREIGN KEY (rombongan_belajar_id) REFERENCES rombongan_belajar(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS mahasiswa_kelas (
    mahasiswa_id INT UNSIGNED NOT NULL,
    kelas_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (mahasiswa_id, kelas_id),
    CONSTRAINT fk_mahasiswa_kelas_mahasiswa
        FOREIGN KEY (mahasiswa_id) REFERENCES mahasiswa(id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_mahasiswa_kelas_kelas
        FOREIGN KEY (kelas_id) REFERENCES kelas(id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT IGNORE INTO program_studi (kode, nama) VALUES
    ('TI', 'Teknik Informatika'),
    ('SI', 'Sistem Informasi'),
    ('PGSD', 'Pendidikan Guru Sekolah Dasar'),
    ('DKV', 'Desain Komunikasi Visual'),
    ('ILKOM', 'Ilmu Komunikasi'),
    ('PARBUD', 'Pariwisata Budaya');

INSERT IGNORE INTO rombongan_belajar (kode, nama) VALUES
    ('KLS-DPS', 'Kelas Denpasar'),
    ('KLS-BGL', 'Kelas Bangli'),
    ('KLS-KNY', 'Kelas Kenyeri');

INSERT IGNORE INTO dosen (nidn, nama, email) VALUES
    ('0011223344', 'Dosen Contoh', 'dosen@example.test'),
    ('0011223345', 'Dr. Ayu Lestari, M.Kom.', 'ayu.lestari@example.test'),
    ('0011223346', 'Budi Santoso, S.Kom., M.T.', 'budi.santoso@example.test'),
    ('0011223347', 'Citra Maharani, M.Kom.', 'citra.maharani@example.test'),
    ('0011223348', 'Dewa Putu Wijaya, M.T.', 'dewa.wijaya@example.test'),
    ('0011223349', 'Eka Pratama, M.Kom.', 'eka.pratama@example.test');

INSERT IGNORE INTO ruangan (kode, nama, gedung, kapasitas) VALUES
    ('RM-001', 'Lab Komputer 1', 'Gedung A', 30),
    ('RM-002', 'Ruang 201', 'Gedung B', 40),
    ('RM-003', 'Lab Komputer 2', 'Gedung A', 30),
    ('RM-004', 'Ruang 202', 'Gedung B', 40),
    ('RM-005', 'Lab Jaringan', 'Gedung A', 25),
    ('RM-006', 'Ruang 203', 'Gedung B', 40);

INSERT IGNORE INTO mata_kuliah (nama, sks) VALUES
    ('Pengantar Informatika', 3),
    ('Algoritma dan Pemrograman', 3),
    ('Basis Data', 3),
    ('Struktur Data', 3),
    ('Jaringan Komputer', 3),
    ('Sistem Operasi', 3);

INSERT IGNORE INTO kelas (kode, mata_kuliah_id, dosen_id, semester, tahun_ajaran)
SELECT 'IF101-A', mk.id, d.id, 'Ganjil', '2026/2027'
FROM mata_kuliah mk
JOIN dosen d ON d.nidn = '0011223344'
WHERE mk.nama = 'Pengantar Informatika';

INSERT IGNORE INTO kelas (kode, mata_kuliah_id, dosen_id, semester, tahun_ajaran)
SELECT seed.kode_kelas, mk.id, d.id, 'Ganjil', '2026/2027'
FROM (
    SELECT 'IF102-A' AS kode_kelas, 'Algoritma dan Pemrograman' AS nama_mk, '0011223345' AS nidn
    UNION ALL SELECT 'IF103-A', 'Basis Data', '0011223346'
    UNION ALL SELECT 'IF104-A', 'Struktur Data', '0011223347'
    UNION ALL SELECT 'IF105-A', 'Jaringan Komputer', '0011223348'
    UNION ALL SELECT 'IF106-A', 'Sistem Operasi', '0011223349'
) AS seed
INNER JOIN mata_kuliah mk ON mk.nama = seed.nama_mk
INNER JOIN dosen d ON d.nidn = seed.nidn;

INSERT INTO jadwal_perkuliahan (kelas_id, ruangan_id, hari, jam_mulai, jam_selesai)
SELECT k.id, r.id, seed.hari, seed.jam_mulai, seed.jam_selesai
FROM (
    SELECT 'IF101-A' AS kode_kelas, 'Senin' AS hari, '08:00:00' AS jam_mulai, '10:30:00' AS jam_selesai, 'RM-001' AS kode_ruang
    UNION ALL SELECT 'IF102-A', 'Selasa', '08:00:00', '10:30:00', 'RM-002'
    UNION ALL SELECT 'IF103-A', 'Rabu', '08:00:00', '10:30:00', 'RM-003'
    UNION ALL SELECT 'IF104-A', 'Kamis', '08:00:00', '10:30:00', 'RM-004'
    UNION ALL SELECT 'IF105-A', 'Jumat', '08:00:00', '10:30:00', 'RM-005'
    UNION ALL SELECT 'IF106-A', 'Sabtu', '08:00:00', '10:30:00', 'RM-006'
) AS seed
INNER JOIN kelas k ON k.kode = seed.kode_kelas
INNER JOIN ruangan r ON r.kode = seed.kode_ruang
WHERE NOT EXISTS (
    SELECT 1
    FROM jadwal_perkuliahan j
    WHERE j.kelas_id = k.id
      AND j.hari = seed.hari
      AND j.jam_mulai = seed.jam_mulai
);

INSERT IGNORE INTO mahasiswa (nim, nama, email, program_studi_id)
SELECT '20231001', 'Romi', 'romi@example.test', ps.id
FROM program_studi ps
WHERE ps.kode = 'TI';

INSERT IGNORE INTO mahasiswa (nim, nama, email, program_studi_id)
SELECT '20231002', 'Widi', 'widi@example.test', ps.id
FROM program_studi ps
WHERE ps.kode = 'SI';

INSERT IGNORE INTO mahasiswa (nim, nama, email, program_studi_id)
SELECT '20231003', 'Agus', 'agus@example.test', ps.id
FROM program_studi ps
WHERE ps.kode = 'TI';

INSERT IGNORE INTO mahasiswa_kelas (mahasiswa_id, kelas_id)
SELECT m.id, k.id
FROM mahasiswa m
JOIN kelas k ON k.kode = 'IF101-A'
WHERE m.nim IN ('20231001', '20231002', '20231003');
