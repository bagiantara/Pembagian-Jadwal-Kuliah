USE akademik_mvc;

DELETE newer_schedule
FROM jadwal_perkuliahan AS newer_schedule
INNER JOIN jadwal_perkuliahan AS older_schedule
    ON older_schedule.kelas_id = newer_schedule.kelas_id
   AND older_schedule.rombongan_belajar_id = newer_schedule.rombongan_belajar_id
   AND (
       older_schedule.created_at < newer_schedule.created_at
       OR (
           older_schedule.created_at = newer_schedule.created_at
           AND older_schedule.id < newer_schedule.id
       )
   );

ALTER TABLE jadwal_perkuliahan
    ADD CONSTRAINT uq_jadwal_kelas_rombongan UNIQUE (kelas_id, rombongan_belajar_id);
