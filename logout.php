<?php
require_once 'config.php';

// Hapus remember token dari data user (jika ada) supaya cookie lama tidak bisa dipakai lagi
if (isset($_SESSION['user_id'])) {
    $users = readUsers();
    foreach ($users as &$u) {
        if ($u['id'] === $_SESSION['user_id']) {
            $u['remember_token'] = null;
        }
    }
    unset($u);
    saveUsers($users);
}

// Hapus cookie remember me
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// 8. Hapus semua data session
$_SESSION = [];
session_destroy();

redirect('login.php');
