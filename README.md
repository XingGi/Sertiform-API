# Sertiform - Aplikasi Form Builder

Sertiform adalah aplikasi form builder canggih yang dibangun dengan Laravel 11 untuk API backend dan Vue 3 (Vite) untuk dashboard admin.

## Prasyarat

Sebelum memulai, pastikan kamu sudah menginstal software berikut:

-   PHP 8.2 atau lebih baru
-   Composer
-   Node.js & NPM
-   Database (contoh: MySQL, MariaDB)

---

## 🚀 Instalasi Backend (sertiform-api)

1.  **Clone Repositori**

    ```bash
    git clone [https://github.com/username/sertiform-api.git](https://github.com/username/sertiform-api.git)
    cd sertiform-api
    ```

2.  **Install Dependensi PHP**

    ```bash
    composer install
    ```

3.  **Setup File Environment**
    Salin file `.env.example` menjadi `.env`.

    ```bash
    cp .env.example .env
    ```

    Kemudian, buat _application key_ baru.

    ```bash
    php artisan key:generate
    ```

4.  **Konfigurasi Database**
    Buka file `.env` dan sesuaikan koneksi database kamu (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD).

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=sertiform
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5.  **Jalankan Migrasi & Seeder**
    Perintah ini akan membuat semua tabel database dan sekaligus membuat akun master admin.

    ```bash
    php artisan migrate --seed
    ```

6.  **Buat Symbolic Link**
    Perintah ini penting agar file yang di-upload (seperti gambar background) bisa diakses publik.

    ```bash
    php artisan storage:link
    ```

7.  **Jalankan Server Development**
    ```bash
    php artisan serve
    ```
    Backend API sekarang berjalan di `http://127.0.0.1:8000`.

---
