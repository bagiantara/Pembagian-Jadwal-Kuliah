# Login dan peran

1. Pastikan tabel akademik sudah dibuat. Untuk database yang sudah digunakan, jalankan `migrate_add_users.sql` pada database `akademik_mvc`. Tabel tersebut juga sudah disertakan pada `setup.sql` untuk instalasi baru.
2. Buat akun admin awal dari PowerShell di folder proyek. Tetapkan username dan password kuat; jangan gunakan password contoh di server publik:

   ```powershell
   $env:ACADEMIC_ADMIN_USERNAME = "admin"
   $env:ACADEMIC_ADMIN_PASSWORD = "GantiDenganPasswordKuat"
   C:\xampp\php\php.exe .\database\create_admin.php
   Remove-Item Env:ACADEMIC_ADMIN_USERNAME
   Remove-Item Env:ACADEMIC_ADMIN_PASSWORD
   ```

   Skrip hanya membuat akun jika belum ada admin dan menyimpan password dalam bentuk hash. Password harus 10–72 karakter.

3. Admin login melalui `/admin` menggunakan username dan password yang ditetapkan pada langkah sebelumnya. User login melalui `/user` menggunakan NIM dan password. Kedua halaman login dipisah; halaman admin tidak menampilkan pendaftaran user.
4. Admin menyiapkan user di **Kelola Akun** dengan memilih nama mahasiswa dan NIM saja; admin tidak mengatur password user. Mahasiswa kemudian membuat akun melalui tautan pendaftaran pada halaman `/user`, dengan NIM, email yang cocok dengan data mahasiswa, password baru, dan konfirmasi password. Username user otomatis menggunakan NIM. Password user minimal 5 karakter dan boleh berisi huruf, angka, spasi, atau simbol; demi keamanan, sarankan password yang lebih panjang dan tidak digunakan ulang. Jika email belum tercatat atau tidak cocok, admin perlu memperbarui email di data mahasiswa terlebih dahulu.

5. Untuk memakai kurikulum semester 1–7 pada database yang sudah ada, jalankan `migrate_add_curriculum_semester.sql` satu kali. Mahasiswa baru otomatis mulai pada semester 1.
6. Admin mengatur **Semester Kurikulum** 1–7 untuk setiap kelas di halaman **Mata Kuliah**, bersama dosen pengajar. Kolom **Semester** Ganjil/Genap tetap digunakan sebagai periode akademik dan berbeda dari semester kurikulum.
7. Saat mahasiswa naik semester, admin membuka **Data Mahasiswa → Edit**, lalu mengubah **Semester Aktif**. Pilihan lama tidak dihapus. User akan melihat kelas yang ditawarkan untuk semester aktifnya dan hanya dapat menambahkan mata kuliah pada semester tersebut. Jika daftar semester itu kosong, admin perlu menambahkan/mengatur kelas semester tersebut terlebih dahulu.

Admin dapat mengelola data akademik dan akun. User dapat melihat data, melihat kurikulum semester aktif, dan menambahkan mata kuliah pada profil mahasiswa yang ditautkan ke akunnya; user tidak dapat mengubah atau menghapus data.
