USE akademik_mvc;

INSERT IGNORE INTO dosen (nidn, nama, email) VALUES
    ('0011223345', 'Dr. Ayu Lestari, M.Kom.', 'ayu.lestari@example.test'),
    ('0011223346', 'Budi Santoso, S.Kom., M.T.', 'budi.santoso@example.test'),
    ('0011223347', 'Citra Maharani, M.Kom.', 'citra.maharani@example.test'),
    ('0011223348', 'Dewa Putu Wijaya, M.T.', 'dewa.wijaya@example.test'),
    ('0011223349', 'Eka Pratama, M.Kom.', 'eka.pratama@example.test');

INSERT IGNORE INTO mata_kuliah (nama, sks) VALUES
    ('Pengantar Informatika', 3),
    ('Algoritma dan Pemrograman', 3),
    ('Basis Data', 3),
    ('Struktur Data', 3),
    ('Jaringan Komputer', 3),
    ('Sistem Operasi', 3);

INSERT IGNORE INTO kelas (kode, mata_kuliah_id, dosen_id, semester, tahun_ajaran)
SELECT seed.kode_kelas, mk.id, d.id, 'Ganjil', '2026/2027'
FROM (
    SELECT 'IF101-A' AS kode_kelas, 'Pengantar Informatika' AS nama_mk, '0011223344' AS nidn
    UNION ALL SELECT 'IF102-A', 'Algoritma dan Pemrograman', '0011223345'
    UNION ALL SELECT 'IF103-A', 'Basis Data', '0011223346'
    UNION ALL SELECT 'IF104-A', 'Struktur Data', '0011223347'
    UNION ALL SELECT 'IF105-A', 'Jaringan Komputer', '0011223348'
    UNION ALL SELECT 'IF106-A', 'Sistem Operasi', '0011223349'
) AS seed
INNER JOIN mata_kuliah mk ON mk.nama = seed.nama_mk
INNER JOIN dosen d ON d.nidn = seed.nidn;
