# Panduan Skenario Presentasi & Demo Tugas

Dokumen ini memandu kelompok 5 orang saat melakukan presentasi langsung di hadapan dosen/asisten penguji.

---

## 1. Persiapan Sebelum Demo Dimulai

1. **Pengelola Cloud DB (Anggota 4):**
   - Pastikan database cloud aktif dan terisi data awal dari `database/schema.sql`.
   - Pastikan semua laptop anggota tim terhubung ke internet dan file `.env` mengarah ke host Cloud DB yang sama.
2. **Client 1, 2, 3 (Anggota 1, 2, 3):**
   - Jalankan server di laptop masing-masing:
     - **Jika pakai Laragon/XAMPP:** Letakkan folder `distributed-chat` di `C:/laragon/www/` atau `htdocs/`, lalu akses `http://localhost/distributed-chat`.
     - **Jika via CLI bawaan PHP:** Jalankan terminal: `php -S localhost:8000` lalu akses `http://localhost:8000`.
   - Buka browser:
     - Laptop 1: Login sebagai **User 1** (username: `user1`, pass: `password123`)
     - Laptop 2: Login sebagai **User 2** (username: `user2`, pass: `password123`)
     - Laptop 3: Login sebagai **User 3** (username: `user3`, pass: `password123`)
3. **Pengelola Backup Storage (Anggota 5):**
   - Buka terminal di folder `backup-storage` pada laptop desktop backup.
   - Siapkan perintah: `python backup_manager.py list` atau `php backup_cli.php list`.

---

## 2. Alur Pengujian / Skenario Demo

### Skenario 1: Pertukaran Pesan Multi-Node Terdistribusi (User 1 <-> User 2)
* **Tujuan:** Membuktikan arsitektur multi application server berkomunikasi melalui satu Cloud Database bersama.
* **Langkah:**
  1. Di Laptop 1 (User 1), klik kontak **User 2**.
  2. Ketik pesan: `"Halo User 2, ini pesan dari Application Server Laptop 1."` lalu klik Kirim.
  3. Perhatikan di Laptop 2 (User 2): Dalam hitungan 1–2 detik, pesan otomatis muncul tanpa refresh halaman manual (mekanisme HTTP Polling).
  4. Laptop 2 membalas: `"Pesan diterima di Application Server Laptop 2."`
  5. Pesan balasan langsung tampil di Laptop 1.

### Skenario 2: Pengujian Privasi & Otorisasi Ketat (User 3)
* **Tujuan:** Memenuhi syarat *"Data pribadi dan informasi milik setiap user hanya dapat diakses oleh user yang bersangkutan. User tidak diperbolehkan melihat pesan user lain tanpa izin."*
* **Langkah:**
  1. Tunjukkan layar Laptop 3 (User 3).
  2. Klik kontak User 1 atau User 2 di Laptop 3.
  3. Ruang obrolan User 3 kosong (atau hanya menampilkan chat langsung antara User 3 dan User 1/2 jika pernah ada).
  4. **Pembuktian Teknis:** Buka browser Developer Tools (F12) -> tab Network di Laptop 3. Perlihatkan bahwa query `api/messages.php` selalu mengikat filter `sender_id = 3 OR receiver_id = 3` secara ketat di sisi server. User 3 tidak memiliki akses database query untuk membaca pertukaran pesan antara User 1 dan User 2.

### Skenario 3: Pembuktian Sinkronisasi Waktu Server Cloud
* **Tujuan:** Memenuhi syarat *"Waktu yang digunakan mengikuti waktu server cloud sebagai acuan sinkronisasi waktu."*
* **Langkah:**
  1. Di header aplikasi salah satu laptop, klik tombol **"Waktu Cloud: HH:MM:SS"**.
  2. Modal detail akan menampilkan:
     - Cloud DB Server Time (berasal dari `SELECT NOW()`).
     - Local App Server Time (jam lokal PHP laptop).
  3. Jelaskan kepada dosen bahwa setiap `INSERT` pesan mengeksekusi fungsi `NOW()` di level Cloud Database. Dengan demikian, jika ada laptop anggota yang jam sistemnya terlambat atau dipercepat, urutan pesan tetap valid dan akurat mengikuti waktu server cloud.

### Skenario 4: Simulasi Kegagalan Sistem & Pemulihan (Disaster Recovery via Backup PC)
* **Tujuan:** Memenuhi syarat *"Menyimpan salinan data dari cloud database... dan digunakan sebagai media pemulihan apabila database utama mengalami gangguan. Tidak menggunakan Google Drive."*
* **Langkah:**
  1. **Langkah Backup:**
     - Anggota 5 menjalankan perintah di PC Desktop miliknya:
       ```bash
       python backup_manager.py backup
       # atau: php backup_cli.php backup
       ```
     - Tunjukkan bahwa file `.sql` tersimpan di harddisk lokal laptop (`backup-storage/backups/backup_YYYYMMDD_HHMMSS.sql`).
  2. **Langkah Simulasi Kerusakan Data (Disaster):**
     - Anggota 4 (Cloud Admin) menghapus seluruh isi tabel pesan di Cloud Database:
       `TRUNCATE TABLE messages;`
     - Perlihatkan di laptop User 1 dan User 2 bahwa semua riwayat chat hilang.
  3. **Langkah Pemulihan (Restore):**
     - Anggota 5 menjalankan perintah pemulihan dari file cadangan lokal ke Cloud DB:
       ```bash
       python backup_manager.py restore
       # atau: php backup_cli.php restore
       ```
     - Output: `Sukses: Database berhasil dipulihkan dari backup_...sql`.
  4. **Verifikasi:**
     - Refresh halaman chat di Laptop 1 & 2. Seluruh riwayat percakapan kembali utuh seperti semula.
