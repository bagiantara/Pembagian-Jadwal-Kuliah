USE akademik_mvc;

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
