USE akademik_mvc;

ALTER TABLE jadwal_perkuliahan
    ADD COLUMN rombongan_belajar_id INT UNSIGNED NULL AFTER ruangan_id,
    ADD INDEX idx_jadwal_rombongan_belajar (rombongan_belajar_id),
    ADD CONSTRAINT fk_jadwal_rombongan_belajar
        FOREIGN KEY (rombongan_belajar_id) REFERENCES rombongan_belajar(id)
        ON UPDATE CASCADE ON DELETE RESTRICT;
