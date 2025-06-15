<?php
session_start();
require_once 'config.php'; // Dodato da bi $config radio

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$category = $_POST['category'] ?? '';
$due_date = $_POST['due_date'] ?? '';

if (empty($title)) {
    echo json_encode(['status' => 'error', 'message' => 'The assignment title is mandatory.']);
    exit;
}

if (strlen($title) > 255) {
    echo json_encode(['status' => 'error', 'message' => 'The title is too long']);
    exit;
}

try {
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset={$config['db']['charset']}";
    $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, category, due_date, status) VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$_SESSION['user_id'], $title, $category, $due_date]);

    echo json_encode(['status' => 'success']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
