# Panduan Setup Cloud Database (Tugas Anggota 4)

Panduan ini ditujukan untuk **Pengelola Cloud Database** untuk menyediakan 1 database MySQL terpusat di cloud yang dapat diakses oleh semua anggota tim melalui internet.

---

## Opsi 1: Menggunakan Aiven for MySQL (Sangat Direkomendasikan - Gratis & Stabil)

1. Buka [https://aiven.io](https://aiven.io) dan daftar akun gratis.
2. Klik **Create Service** -> Pilih **MySQL**.
3. Pilih Cloud Provider (misal: AWS / Google Cloud di region Singapura `ap-southeast-1` agar latensi rendah).
4. Pilih Plan **Free Tier**.
5. Tunggu status service berubah menjadi **Running** (sekitar 1–2 menit).
6. Di halaman Overview, salin parameter koneksi:
   - **Host:** `mysql-xxxx.aivencloud.com`
   - **Port:** `misal 12345`
   - **User:** `avnadmin`
   - **Password:** `(klik tombol copy password)`
   - **Database Name:** `defaultdb` (atau buat database baru `distributed_chat`)

---

## Opsi 2: Menggunakan TiDB Serverless (Alternatif Gratis)

1. Buka [https://tidbcloud.com](https://tidbcloud.com) dan buat akun gratis.
2. Buat cluster **Serverless** (Region Singapore).
3. Klik **Connect** -> Pilih **General** (MySQL client).
4. Catat Host, Port (biasanya `4000`), User, dan Password.

---

## Langkah Import Skema Database

Setelah database cloud aktif:

1. Buka tool database favorit Anda (DBeaver, HeidiSQL, phpMyAdmin, atau Navicat).
2. Hubungkan ke Cloud Database dengan kredensial di atas.
3. Buka file `database/schema.sql` di proyek ini.
4. Jalankan script SQL tersebut untuk membuat tabel `users`, `messages`, indeks, serta data awal pengujian (User 1, User 2, User 3).

---

## Membagikan Konfigurasi ke Anggota Tim

Kirimkan parameter konfigurasi ke 4 anggota lainnya agar mereka mengedit file `.env` di laptop masing-masing:

```env
DB_HOST=host-cloud-database-anda
DB_PORT=port-cloud-database-anda
DB_NAME=nama-database
DB_USER=username-cloud
DB_PASS=password-cloud
```

Setiap anggota (User 1, 2, 3, dan Pengelola Backup) cukup menyimpan file `.env` ini di folder proyek mereka.
