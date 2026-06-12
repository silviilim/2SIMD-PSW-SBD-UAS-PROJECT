<?php
session_start();

// Hapus semua data di dalam session
$_SESSION = array();

// Hancurkan session cookie di browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session di server
session_destroy();

// Tendang kembali ke halaman login
header("Location: login.php");
exit();
?>