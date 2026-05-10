# FB Affiliate Comment Bot

Aplikasi PHP Native MVC untuk otomasi komentar Facebook affiliate, notifikasi Telegram, dashboard monitoring, dan cron job. Dibuat agar bisa berjalan di shared hosting/cPanel dengan PHP 8+ dan MySQL.

## Fitur Utama

- Dashboard admin responsif dengan Bootstrap 5, Tabler, DataTables, dan Chart.js.
- Manajemen target grup/feed Facebook.
- Manajemen template komentar yang dirotasi otomatis.
- Deteksi duplikat post agar tidak komentar berulang.
- Delay acak, cooldown, dan lock cron untuk mengurangi risiko spam.
- Penyimpanan cookie Facebook berbasis file.
- Notifikasi Telegram untuk proses sukses/gagal.
- Logging aplikasi dan logging Telegram.
- Tetap bisa berjalan tanpa Composer.

## Kebutuhan Server

- PHP 8.0 atau lebih baru.
- MySQL/MariaDB.
- Ekstensi PHP: `pdo_mysql`, `curl`, `json`, `mbstring`.
- Apache dengan document root diarahkan ke folder `public/`.
- Cron job cPanel untuk menjalankan bot otomatis.

## Instalasi di cPanel

1. Upload semua file project ke hosting.
2. Arahkan document root domain/subdomain ke folder `public/`.
3. Copy file `.env.example` menjadi `.env`.
4. Buat database MySQL dari cPanel.
5. Import file `database.sql` ke database tersebut melalui phpMyAdmin.
6. Isi konfigurasi database di file `.env`:

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database
```

7. Isi `APP_URL` sesuai domain:

```env
APP_URL=https://domainmu.com
```

## Composer

Composer tidak wajib.

Aplikasi ini sudah memiliki fallback autoloader, jadi tetap bisa berjalan di cPanel meskipun tidak ada Composer dan tidak ada folder `vendor/`.

Jika hosting menyediakan Composer, boleh jalankan:

```bash
composer install --no-dev --optimize-autoloader
```

Jika tidak ada Composer, lewati langkah tersebut.

## Membuat Admin

### Cara 1: Melalui Terminal cPanel

Jika cPanel menyediakan Terminal, jalankan:

```bash
php cron/create_admin.php admin@example.com password-kuat
```

Password minimal 8 karakter.

### Cara 2: Melalui Browser

Jika tidak ada Terminal, gunakan installer admin sekali pakai.

1. Isi token acak panjang di `.env`:

```env
INSTALL_ADMIN_TOKEN=isi-token-acak-panjang-di-sini
```

2. Buka URL berikut:

```text
https://domainmu.com/install_admin.php?token=isi-token-acak-panjang-di-sini
```

3. Buat akun admin dari halaman tersebut.
4. Setelah selesai, segera hapus file:

```text
public/install_admin.php
```

Atau kosongkan nilai:

```env
INSTALL_ADMIN_TOKEN=
```

## Login Dashboard

Setelah admin dibuat, buka:

```text
https://domainmu.com/login
```

Masuk menggunakan email dan password admin yang sudah dibuat.

## Konfigurasi Cookie Facebook

1. Login ke dashboard.
2. Buka menu `Bot`.
3. Masukkan cookie Facebook pada bagian `Facebook Cookie`.
4. Simpan.

Cookie digunakan oleh cURL untuk membaca post dan mengirim komentar. Jika cookie kedaluwarsa, bot akan mencatat error dan perlu cookie baru.

## Konfigurasi Telegram

Telegram bersifat opsional.

Isi nilai berikut di `.env` atau dari menu `Settings`:

```env
TELEGRAM_ENABLED=true
TELEGRAM_BOT_TOKEN=token_bot_telegram
TELEGRAM_CHAT_ID=chat_id_telegram
```

Jika aktif, bot akan mengirim notifikasi saat komentar berhasil atau gagal.

## Cron Job

Tambahkan cron job dari cPanel.

Untuk menjalankan semua proses sekaligus:

```bash
php /full/path/to/project/cron/run_bot.php
```

Untuk hanya mengambil post:

```bash
php /full/path/to/project/cron/fetch_posts.php
```

Untuk hanya mengirim komentar pending:

```bash
php /full/path/to/project/cron/send_comments.php
```

Contoh jadwal aman:

```text
*/10 * * * * php /home/username/bot-fb/cron/run_bot.php
```

`run_bot.php` sudah memakai lock agar tidak ada dua proses bot berjalan bersamaan.

## Struktur Project

```text
app/
  config/
  controllers/
  Core/
  helpers/
  models/
  services/
  views/
cron/
public/
routes/
storage/
```

## Catatan Keamanan

- Semua query database memakai PDO prepared statements.
- Password admin disimpan dengan `password_hash`.
- Semua form POST memakai CSRF token.
- Output view memakai escaping untuk mengurangi risiko XSS.
- Jangan hardcode password, token, atau cookie di source code.
- Hapus `public/install_admin.php` setelah admin dibuat.
- Jangan commit file `.env`, cookie Facebook, log, cache, atau file upload pengguna.
- Gunakan bot secara bertanggung jawab dan patuhi aturan platform yang digunakan.

## Persiapan Push ke GitHub

Sebelum push ke GitHub, pastikan file sensitif tidak ikut ter-commit.

File yang tidak boleh masuk repository:

```text
.env
storage/cookies/*
storage/logs/*
storage/cache/*
public/uploads/*
vendor/
```

Project ini sudah menyediakan `.gitignore` untuk mencegah file tersebut ikut masuk Git.

Cek status file sebelum commit:

```bash
git status --short
```

Jika aman, lakukan commit:

```bash
git add .
git commit -m "Initial PHP MVC Facebook comment bot"
```

Tambahkan remote GitHub:

```bash
git remote add origin https://github.com/username/nama-repo.git
git branch -M main
git push -u origin main
```

Jika remote sudah ada, cukup jalankan:

```bash
git push
```

## Troubleshooting

Jika muncul error database, periksa nilai `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` di `.env`.

Jika halaman tidak bisa dibuka, pastikan document root mengarah ke folder `public/`.

Jika route selain `/login` menghasilkan 404, pastikan file `public/.htaccess` ter-upload dan Apache mengizinkan rewrite.

Jika bot tidak mengirim komentar, periksa cookie Facebook, target aktif, komentar aktif, dan log di menu `Logs`.
