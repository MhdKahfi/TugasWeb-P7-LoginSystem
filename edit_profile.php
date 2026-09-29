<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$result = findUserById($_SESSION['user_id']);
if (!$result) {
    // Data user tidak ditemukan (mungkin dihapus manual dari JSON)
    session_destroy();
    redirect('login.php');
}

$currentUser = $result['user'];
$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama        = sanitize($_POST['nama'] ?? '');
    $email       = sanitize($_POST['email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';

    if (empty($nama) || empty($email)) {
        $errors[] = 'Nama dan email wajib diisi.';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    // Cek apakah email dipakai user lain
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $existing = findUserByEmail($email);
        if ($existing && $existing['id'] !== $currentUser['id']) {
            $errors[] = 'Email sudah digunakan oleh akun lain.';
        }
    }

    if (!empty($newPassword) && strlen($newPassword) < 6) {
        $errors[] = 'Password baru minimal 6 karakter.';
    }

    if (empty($errors)) {
        $users = readUsers();
        $index = $result['index'];

        $users[$index]['nama']  = $nama;
        $users[$index]['email'] = $email;

        if (!empty($newPassword)) {
            $users[$index]['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        if (saveUsers($users)) {
            // Update session dengan data terbaru
            $_SESSION['nama']  = $nama;
            $_SESSION['email'] = $email;

            $success = 'Profil berhasil diperbarui.';
            $currentUser = $users[$index];
        } else {
            $errors[] = 'Gagal menyimpan perubahan. Coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <span class="navbar-brand">MyApp</span>
        <div class="navbar-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="edit_profile.php">Edit Profil</a>
            <a href="logout.php" class="link-danger">Logout</a>
        </div>
    </nav>

    <div class="page-container">
        <div class="card">
            <h1>Edit Profil</h1>
            <p class="text-muted">Perbarui data akun Anda di bawah ini.</p>

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

            <form method="POST" action="edit_profile.php" novalidate>
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?= htmlspecialchars($currentUser['nama']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($currentUser['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="new_password">Password Baru</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Kosongkan jika tidak ingin mengubah password">
                    <small class="form-hint">Minimal 6 karakter. Biarkan kosong jika tidak ingin mengganti.</small>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="dashboard.php" class="btn btn-outline">Batal</a>
            </form>
        </div>
    </div>
</body>
</html>
