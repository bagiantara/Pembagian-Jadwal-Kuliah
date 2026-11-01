USE akademik_mvc;

CREATE TABLE IF NOT EXISTS jadwal_perkuliahan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kelas_id INT UNSIGNED NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    ruang VARCHAR(50) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_jadwal_hari_waktu (hari, jam_mulai, jam_selesai),
    INDEX idx_jadwal_kelas (kelas_id),
    CONSTRAINT fk_jadwal_kelas
        FOREIGN KEY (kelas_id) REFERENCES kelas(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO jadwal_perkuliahan (kelas_id, hari, jam_mulai, jam_selesai, ruang)
SELECT k.id, seed.hari, seed.jam_mulai, seed.jam_selesai, seed.ruang
FROM (
    SELECT 'IF101-A' AS kode_kelas, 'Senin' AS hari, '08:00:00' AS jam_mulai, '10:30:00' AS jam_selesai, 'Lab Komputer 1' AS ruang
    UNION ALL SELECT 'IF102-A', 'Selasa', '08:00:00', '10:30:00', 'Ruang 201'
    UNION ALL SELECT 'IF103-A', 'Rabu', '08:00:00', '10:30:00', 'Lab Komputer 2'
    UNION ALL SELECT 'IF104-A', 'Kamis', '08:00:00', '10:30:00', 'Ruang 202'
    UNION ALL SELECT 'IF105-A', 'Jumat', '08:00:00', '10:30:00', 'Lab Jaringan'
    UNION ALL SELECT 'IF106-A', 'Sabtu', '08:00:00', '10:30:00', 'Ruang 203'
) AS seed
INNER JOIN kelas k ON k.kode = seed.kode_kelas
WHERE NOT EXISTS (
    SELECT 1
    FROM jadwal_perkuliahan j
    WHERE j.kelas_id = k.id
      AND j.hari = seed.hari
      AND j.jam_mulai = seed.jam_mulai
);
