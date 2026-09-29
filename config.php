<?php
/**
 * config.php
 * Konfigurasi dasar: session, path data, dan helper function umum
 */

session_start();

// Path ke file JSON penyimpanan data user
define('USERS_FILE', __DIR__ . '/data/users.json');

/**
 * Membaca seluruh data user dari file JSON
 * @return array
 */
function readUsers(): array
{
    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, json_encode([]));
    }

    $json = file_get_contents(USERS_FILE);
    $data = json_decode($json, true);

    return is_array($data) ? $data : [];
}

/**
 * Menyimpan seluruh data user ke file JSON
 * @param array $users
 * @return bool
 */
function saveUsers(array $users): bool
{
    // LOCK_EX mencegah race condition saat beberapa proses menulis bersamaan
    return file_put_contents(
        USERS_FILE,
        json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    ) !== false;
}

/**
 * Mencari user berdasarkan email
 * @param string $email
 * @return array|null
 */
function findUserByEmail(string $email): ?array
{
    $users = readUsers();
    foreach ($users as $user) {
        if (strtolower($user['email']) === strtolower($email)) {
            return $user;
        }
    }
    return null;
}

/**
 * Mencari user berdasarkan ID, mengembalikan index array & data user
 * @param string $id
 * @return array|null [index, user]
 */
function findUserById(string $id): ?array
{
    $users = readUsers();
    foreach ($users as $index => $user) {
        if ($user['id'] === $id) {
            return ['index' => $index, 'user' => $user];
        }
    }
    return null;
}

/**
 * Membersihkan input dari karakter berbahaya (XSS)
 * @param string $data
 * @return string
 */
function sanitize(string $data): string
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Mengecek apakah user sedang login (via session ATAU cookie remember me)
 * @return bool
 */
function isLoggedIn(): bool
{
    if (isset($_SESSION['user_id'])) {
        return true;
    }

    // Cek cookie "remember me" jika session sudah habis
    if (isset($_COOKIE['remember_token'])) {
        $token = $_COOKIE['remember_token'];
        $users = readUsers();

        foreach ($users as $user) {
            if (isset($user['remember_token']) && $user['remember_token'] === $token) {
                // Restore session dari cookie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama']    = $user['nama'];
                $_SESSION['email']   = $user['email'];
                return true;
            }
        }
    }

    return false;
}

/**
 * Redirect ke halaman lain lalu hentikan eksekusi
 * @param string $url
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/**
 * Mengambil dan menghapus flash message dari session (pesan sukses/error sekali tampil)
 * @param string $key
 * @return string|null
 */
function getFlash(string $key): ?string
{
    if (isset($_SESSION[$key])) {
        $message = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $message;
    }
    return null;
}
