<?php
require_once 'config.php';

if (isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$success = getFlash('success');
$old = ['email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email      = sanitize($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $rememberMe = isset($_POST['remember_me']);

    $old['email'] = $email;

    if (empty($email) || empty($password)) {
        $errors[] = 'Email dan password wajib diisi.';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if (empty($errors)) {
        $user = findUserByEmail($email);

        // 6. Sistem login: verifikasi password terhadap hash
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email atau password salah.';
        } else {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['email']   = $user['email'];

            // 11. Remember Me dengan cookies
            if ($rememberMe) {
                $token = bin2hex(random_bytes(32));

                // Simpan token ke data user
                $users = readUsers();
                foreach ($users as &$u) {
                    if ($u['id'] === $user['id']) {
                        $u['remember_token'] = $token;
                    }
                }
                unset($u);
                saveUsers($users);

                // Cookie berlaku 30 hari
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            }

            $_SESSION['success'] = 'Login berhasil! Selamat datang kembali, ' . $user['nama'] . '.';
            redirect('dashboard.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <h1 class="auth-title">Selamat Datang</h1>
            <p class="auth-subtitle">Login untuk melanjutkan</p>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email']) ?>" placeholder="nama@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required>
                </div>

                <div class="form-group form-check">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember_me">
                        Ingat saya
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <p class="auth-footer">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
        </div>
    </div>
</body>
</html>
