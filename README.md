# Sistem Terdistribusi: Arsitektur WhatsApp (Modifikasi)

Proyek sistem komunikasi terdistribusi berbasis arsitektur WhatsApp dengan multi-node application server, central cloud database, dan dedicated local desktop backup storage.

---

## Struktur Direktori

```
distributed-chat/
├── .env.example              # Template konfigurasi koneksi database
├── .env                      # Konfigurasi aktif (host, user, pass Cloud DB)
├── config.php                # Database connection (PDO) & session handler
├── index.php                 # Web client UI (WhatsApp web-style)
├── api/
│   ├── auth.php              # Login, register, logout, session check
│   ├── users.php             # Daftar kontak & preview chat terakhir
│   ├── messages.php          # Kirim (NOW() cloud time) & polling pesan terisolasi
│   └── time.php              # Endpoint sinkronisasi waktu Cloud DB vs Local
├── database/
│   ├── schema.sql            # DDL database, tabel users, messages, & seed data
│   └── CLOUD_SETUP.md        # Panduan setup Cloud DB (Aiven/TiDB) untuk Anggota 4
├── backup-storage/
│   ├── backup_manager.py     # Python CLI untuk backup, restore, list, & daemon
│   ├── backup_cli.php        # Core PHP CLI script untuk dump & restore SQL
│   └── backups/              # Direktori penyimpanan file .sql di PC desktop
└── PRESENTATION_GUIDE.md     # Panduan alur demo pengujian untuk presentasi
```

---

## Panduan Menjalankan Sistem

### 1. Untuk Client / User (Anggota 1, 2, 3)
1. Salin folder `distributed-chat` ke dalam root web server lokal:
   - **Laragon:** `C:\laragon\www\distributed-chat`
   - **XAMPP:** `C:\xampp\htdocs\distributed-chat`
   - **Atau via CLI:** Masuk ke folder proyek, lalu jalankan `php -S localhost:8000`
2. Pastikan file `.env` sudah diisi kredensial Cloud Database dari Anggota 4.
3. Buka browser ke alamat server lokal Anda (misal `http://localhost/distributed-chat` atau `http://localhost:8000`).
4. Login menggunakan akun pengujian:
   - User 1: `user1` / `password123`
   - User 2: `user2` / `password123`
   - User 3: `user3` / `password123`

### 2. Untuk Pengelola Cloud Database (Anggota 4)
- Ikuti panduan lengkap di [database/CLOUD_SETUP.md](file:///C:/Users/irfan/.gemini/antigravity/scratch/distributed-chat/database/CLOUD_SETUP.md) untuk setup MySQL di cloud dan import `database/schema.sql`.

### 3. Untuk Pengelola Backup Storage (Anggota 5)
1. Masuk ke folder `backup-storage` di terminal PC desktop.
2. Perintah yang tersedia:
   - Ambil backup: `python backup_manager.py backup` (atau `php backup_cli.php backup`)
   - Lihat daftar cadangan: `python backup_manager.py list`
   - Pulihkan data: `python backup_manager.py restore`
   - Backup otomatis tiap 60 detik: `python backup_manager.py daemon --interval 60`
