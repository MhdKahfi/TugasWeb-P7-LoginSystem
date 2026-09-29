<?php
require_once 'config.php';

// Jika sudah login, langsung ke dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$old = ['nama' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Ambil & sanitasi input
    $nama            = sanitize($_POST['nama'] ?? '');
    $email           = sanitize($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? ''; // jangan disanitasi sebelum hash
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $old['nama']  = $nama;
    $old['email'] = $email;

    // 1. Validasi field kosong
    if (empty($nama) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errors[] = 'Semua field wajib diisi.';
    }

    // 2. Validasi format email dengan filter_var()
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    // Validasi panjang password
    if (!empty($password) && strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    // Validasi konfirmasi password
    if ($password !== $confirmPassword) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // 5. Cek duplikasi email
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && findUserByEmail($email)) {
        $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
    }

    // Jika tidak ada error, simpan user baru
    if (empty($errors)) {
        $users = readUsers();

        $newUser = [
            'id'             => uniqid('user_', true),
            'nama'           => $nama,
            'email'          => $email,
            // 3. Password di-hash dengan password_hash()
            'password'       => password_hash($password, PASSWORD_DEFAULT),
            'remember_token' => null,
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        $users[] = $newUser;

        // 4. Data disimpan ke file JSON
        if (saveUsers($users)) {
            $_SESSION['success'] = 'Registrasi berhasil! Silakan login dengan akun Anda.';
            redirect('login.php');
        } else {
            $errors[] = 'Terjadi kesalahan saat menyimpan data. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1 class="auth-title">Buat Akun</h1>
            <p class="auth-subtitle">Daftar untuk mulai menggunakan aplikasi</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error /* sudah di-sanitize saat input */ ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" novalidate>
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($old['nama']) ?>" placeholder="Masukkan nama lengkap" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email']) ?>" placeholder="nama@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required>
                </div>

                <button type="submit" class="btn btn-primary">Daftar</button>
            </form>

            <p class="auth-footer">Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</body>
</html>
