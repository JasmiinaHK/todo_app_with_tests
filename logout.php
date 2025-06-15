<?php
// Isključi bilo kakav prethodni output
ob_start();
session_start();

// Uništi sesiju
$_SESSION = [];
session_destroy();

// Obriši remember_token cookie ako postoji
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Postavi eksplicitno HTTP 200 status
http_response_code(200);

// Da li je AJAX poziv?
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Vrati JSON ako je AJAX
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Logged out successfully'
    ]);
    exit;
}

// Inače redirect na login
header('Location: login.php');
exit;
