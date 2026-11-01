# Aplikasi Akademik MVC

Aplikasi web PHP untuk mengelola data mahasiswa, dosen, mata kuliah, kelas, jadwal, dan ruangan. Aplikasi menggunakan MySQL/MariaDB dan ekstensi PHP `mysqli`.

## Persyaratan

- PHP 8.0 atau lebih baru dengan ekstensi `mysqli`
- MySQL atau MariaDB
- Apache (misalnya melalui XAMPP)

Tidak ada dependensi Composer atau Node.js yang perlu dipasang.

## Menjalankan secara lokal dengan XAMPP

1. Salin atau clone seluruh repositori ke folder `htdocs`, misalnya `C:\xampp\htdocs\akademik-mvc`.
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka phpMyAdmin, lalu impor `database/setup.sql`. Skrip ini membuat database `akademik_mvc`, tabel, dan data contoh.
4. Jika pengaturan MySQL lokal berbeda dari nilai bawaan XAMPP, atur environment variable sebelum menjalankan Apache:

   | Variable | Nilai bawaan |
   | --- | --- |
   | `DB_HOST` | `127.0.0.1` |
   | `DB_PORT` | `3306` |
   | `DB_NAME` | `akademik_mvc` |
   | `DB_USERNAME` | `root` |
   | `DB_PASSWORD` | kosong |

   Untuk deployment, isi kredensial database melalui environment variable hosting. Jangan menyimpan password database di repositori.
5. Buat akun admin awal melalui PowerShell. Gunakan password kuat, bukan password contoh:

   ```powershell
   $env:ACADEMIC_ADMIN_USERNAME = "admin"
   $env:ACADEMIC_ADMIN_PASSWORD = "GantiDenganPasswordKuat"
   C:\xampp\php\php.exe .\database\create_admin.php
   Remove-Item Env:ACADEMIC_ADMIN_USERNAME
   Remove-Item Env:ACADEMIC_ADMIN_PASSWORD
   ```

6. Buka `http://localhost/akademik-mvc/`. Halaman admin tersedia di `http://localhost/akademik-mvc/admin`.

Untuk database yang sudah berisi data aplikasi, jangan impor `setup.sql` berulang kali. Jalankan hanya file migrasi `database/migrate_*.sql` yang diperlukan dan belum pernah diterapkan.

## Isi repositori

Commit kode dan file sumber yang diperlukan aplikasi: `index.php`, file PHP di root, serta seluruh folder `assets/`, `config/`, `controller/`, `database/`, `model/`, dan `views/`. File SQL setup dan migrasi adalah bagian dari sumber proyek; data database lokal atau dump yang berisi data pribadi bukan bagian dari repositori.

File `.gitignore` mengecualikan file environment, kredensial lokal, dependensi, log, dan file editor. Periksa `git status` sebelum commit dan jangan pernah mengunggah password, token, atau data pribadi.

## Mengunggah ke GitHub

1. Buat repositori baru di GitHub. Jika proyek ini sudah memiliki `README.md`, pilih repositori kosong (jangan meminta GitHub membuat README atau `.gitignore`).
2. Jika Git belum terpasang, instal Git for Windows. Buka terminal di folder proyek dan jalankan:

   ```powershell
   git init
   git add .
   git status
   git commit -m "Initial commit"
   git branch -M main
   git remote add origin https://github.com/USERNAME/NAMA-REPOSITORY.git
   git push -u origin main
   ```

   Ganti `USERNAME` dan `NAMA-REPOSITORY` sesuai alamat repositori GitHub. Pastikan `git status` tidak menampilkan file rahasia atau data lokal sebelum commit.

GitHub menyimpan dan membagikan kode sumber; GitHub Pages tidak menjalankan aplikasi PHP/MySQL. Agar aplikasi dapat diakses melalui internet, deploy repositori ini ke hosting yang mendukung PHP dan MySQL/MariaDB, lalu atur environment variable database dan buat database di hosting tersebut.
