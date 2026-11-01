USE akademik_mvc;

CREATE TABLE IF NOT EXISTS ruangan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL UNIQUE,
    gedung VARCHAR(100) NULL,
    kapasitas SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO ruangan (kode, nama, gedung, kapasitas)
SELECT CONCAT('TMP-', REPLACE(UUID(), '-', '')), rooms.nama, NULL, 30
FROM (
    SELECT DISTINCT TRIM(ruang) AS nama
    FROM jadwal_perkuliahan
    WHERE TRIM(ruang) <> ''
) AS rooms
WHERE NOT EXISTS (
    SELECT 1 FROM ruangan existing_room WHERE existing_room.nama = rooms.nama
);

INSERT INTO ruangan (kode, nama, gedung, kapasitas)
SELECT 'LEGACY-UNSET', 'Ruangan belum ditentukan', NULL, 30
WHERE EXISTS (
    SELECT 1 FROM jadwal_perkuliahan WHERE TRIM(ruang) = ''
)
AND NOT EXISTS (
    SELECT 1 FROM ruangan WHERE nama = 'Ruangan belum ditentukan'
);

UPDATE ruangan
SET kode = CONCAT('LEG-', LPAD(id, 6, '0'))
WHERE kode LIKE 'TMP-%';

ALTER TABLE jadwal_perkuliahan
    ADD COLUMN ruangan_id INT UNSIGNED NULL AFTER kelas_id;

UPDATE jadwal_perkuliahan j
INNER JOIN ruangan r
    ON r.nama = CASE
        WHEN TRIM(j.ruang) = '' THEN 'Ruangan belum ditentukan'
        ELSE TRIM(j.ruang)
    END
SET j.ruangan_id = r.id;

ALTER TABLE jadwal_perkuliahan
    MODIFY COLUMN ruangan_id INT UNSIGNED NOT NULL,
    ADD INDEX idx_jadwal_ruangan (ruangan_id),
    ADD CONSTRAINT fk_jadwal_ruangan
        FOREIGN KEY (ruangan_id) REFERENCES ruangan(id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    DROP COLUMN ruang;
