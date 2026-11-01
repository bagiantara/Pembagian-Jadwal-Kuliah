USE akademik_mvc;

ALTER TABLE mata_kuliah
    DROP INDEX kode,
    DROP COLUMN kode,
    ADD CONSTRAINT uq_mata_kuliah_nama UNIQUE (nama);
