# Sistem Login/Register — PHP Native + JSON

Sistem autentikasi sederhana menggunakan PHP native (tanpa framework) dengan file JSON sebagai penyimpanan data.

## Struktur File

```
login-system/
├── config.php          # Session, koneksi ke JSON, fungsi helper
├── index.php           # Entry point (redirect ke login/dashboard)
├── register.php        # Form & proses registrasi
├── login.php           # Form & proses login (+ remember me)
├── logout.php          # Proses logout
├── dashboard.php        # Halaman terproteksi
├── edit_profile.php    # Edit nama/email/password
├── data/
│   └── users.json      # Database (auto-generate)
└── css/
    └── style.css        # Styling
```

## Cara Menjalankan

1. Pastikan PHP terinstall (PHP 7.4+ disarankan).
2. Dari dalam folder `login-system`, jalankan server bawaan PHP:
   ```
   php -S localhost:8000
   ```
3. Buka `http://localhost:8000` di browser.
4. Pastikan folder `data/` bisa ditulis (writable) oleh web server.

## Fitur

| No | Fitur | Keterangan |
|----|-------|------------|
| 1  | Form registrasi | nama, email, password |
| 2  | Validasi email | `filter_var($email, FILTER_VALIDATE_EMAIL)` |
| 3  | Hash password | `password_hash()` + `password_verify()` |
| 4  | Penyimpanan JSON | `data/users.json` |
| 5  | Cek duplikasi email | Saat registrasi & edit profil |
| 6  | Login + session | `$_SESSION['user_id']`, dll |
| 7  | Dashboard terproteksi | Redirect ke `login.php` jika belum login |
| 8  | Logout | `session_destroy()` + hapus cookie |
| 9  | Sanitasi input | `htmlspecialchars()` di semua input |
| 10 | Pesan error/sukses | Ditampilkan jelas di setiap form |
| 11 | Bonus | Remember Me (cookie 30 hari), Edit Profile, CSS rapi & responsif |

## Catatan Keamanan

- Password **tidak pernah** disimpan dalam bentuk plain text — selalu melalui `password_hash()`.
- Remember-me token disimpan sebagai cookie `httponly` (tidak bisa diakses lewat JavaScript) untuk mengurangi risiko XSS-cookie-theft.
- File penulisan JSON menggunakan `LOCK_EX` untuk mencegah race condition.
- Untuk penggunaan produksi sungguhan, sebaiknya migrasi ke database (MySQL/PostgreSQL) dan tambahkan proteksi CSRF token pada setiap form.
