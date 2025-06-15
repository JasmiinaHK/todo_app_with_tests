<?php
session_start();
$config = require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validacija unosa
    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
        http_response_code(400);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
        http_response_code(400);
        exit;
    }

    if ($password !== $confirm) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
        http_response_code(400);
        exit;
    }

    if (strlen($password) < 6) {
        echo json_encode(['status' => 'error', 'message' => 'The password must be at least 6 characters long']);
        http_response_code(400);
        exit;
    }

    try {
        $pdo = new PDO(
            "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset={$config['db']['charset']}",
            $config['db']['username'],
            $config['db']['password']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Provjera duplikata e-maila
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email is already registered']);
            http_response_code(400);
            exit;
        }

        // Hash i unos
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password]);

        echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
        http_response_code(200);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        http_response_code(500);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    http_response_code(405);
    exit;
}
