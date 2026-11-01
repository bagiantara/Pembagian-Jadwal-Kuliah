USE akademik_mvc;

ALTER TABLE mahasiswa
    DROP FOREIGN KEY fk_mahasiswa_rombongan_belajar,
    DROP COLUMN rombongan_belajar_id;
