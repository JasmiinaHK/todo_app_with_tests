<?php
session_start();
header('Content-Type: application/json');

$config = require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset={$config['db']['charset']}",
        $config['db']['username'],
        $config['db']['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email is required']);
    exit;
}

if (empty($password)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Password is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['username'] = $user['username'] ?? $user['email'];

        if (isset($_POST['remember']) && $_POST['remember'] === '1') {
            $token = bin2hex(random_bytes(32));
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$hashedToken, $user['id']]);
            setcookie('remember_token', $token, time() + (86400 * 30), '/');
        }

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Login successful',
            'redirect' => 'dashboard.php'
        ]);
        exit;
    }

    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Incorrect email address or password'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred during login'
    ]);
}
?>
