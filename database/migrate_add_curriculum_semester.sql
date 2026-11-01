USE akademik_mvc;

ALTER TABLE mahasiswa
    ADD COLUMN semester_aktif TINYINT UNSIGNED NOT NULL DEFAULT 1
    AFTER rombongan_belajar_id,
    ADD CONSTRAINT chk_mahasiswa_semester_aktif
        CHECK (semester_aktif BETWEEN 1 AND 7);

ALTER TABLE kelas
    ADD COLUMN semester_ke TINYINT UNSIGNED NOT NULL DEFAULT 1
    AFTER dosen_id,
    ADD CONSTRAINT chk_kelas_semester_ke
        CHECK (semester_ke BETWEEN 1 AND 7);
