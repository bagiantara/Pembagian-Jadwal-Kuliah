USE akademik_mvc;

ALTER TABLE mahasiswa
    ADD COLUMN rombongan_belajar_id INT UNSIGNED NULL AFTER program_studi_id,
    ADD CONSTRAINT fk_mahasiswa_rombongan_belajar
        FOREIGN KEY (rombongan_belajar_id) REFERENCES rombongan_belajar(id)
        ON UPDATE CASCADE ON DELETE RESTRICT;
