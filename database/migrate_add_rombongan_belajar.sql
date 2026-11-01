USE akademik_mvc;

CREATE TABLE IF NOT EXISTS rombongan_belajar (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT IGNORE INTO rombongan_belajar (kode, nama) VALUES
    ('KLS-DPS', 'Kelas Denpasar'),
    ('KLS-BGL', 'Kelas Bangli'),
    ('KLS-KNY', 'Kelas Kenyeri');

ALTER TABLE mahasiswa
    ADD COLUMN rombongan_belajar_id INT UNSIGNED NULL AFTER program_studi_id;

UPDATE mahasiswa
SET rombongan_belajar_id = (
    SELECT id FROM rombongan_belajar WHERE kode = 'KLS-DPS'
)
WHERE rombongan_belajar_id IS NULL;

ALTER TABLE mahasiswa
    MODIFY COLUMN rombongan_belajar_id INT UNSIGNED NOT NULL,
    ADD CONSTRAINT fk_mahasiswa_rombongan_belajar
        FOREIGN KEY (rombongan_belajar_id) REFERENCES rombongan_belajar(id)
        ON UPDATE CASCADE ON DELETE RESTRICT;
