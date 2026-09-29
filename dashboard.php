<?php
require_once 'config.php';

// 7. Proteksi halaman: redirect ke login jika belum login
if (!isLoggedIn()) {
    redirect('login.php');
}

$success = getFlash('success');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <div class="card">
            <h1>Halo, <?= htmlspecialchars($_SESSION['nama']) ?> 👋</h1>
            <p class="text-muted">Selamat datang di dashboard Anda.</p>

            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Nama</span>
                    <span class="info-value"><?= htmlspecialchars($_SESSION['nama']) ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email</span>
                    <span class="info-value"><?= htmlspecialchars($_SESSION['email']) ?></span>
                </div>
            </div>

            <a href="edit_profile.php" class="btn btn-secondary">Edit Profil</a>
        </div>
    </div>
</body>
</html>
