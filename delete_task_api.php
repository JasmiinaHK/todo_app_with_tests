<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$task_id = $_POST['task_id'] ?? null;
if (!$task_id || !is_numeric($task_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Task ID is required']);
    exit;
}

try {
    $config = require __DIR__ . '/config.php';
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset={$config['db']['charset']}";
    $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Provjera da li task pripada korisniku
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $_SESSION['user_id']]);
    $task = $stmt->fetch();

    if (!$task) {
        // Task ne postoji ili ne pripada korisniku
        $stmt = $pdo->prepare("SELECT id FROM tasks WHERE id = ?");
        $stmt->execute([$task_id]);
        if ($stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Forbidden – not your task']);
        } else {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Task not found']);
        }
        exit;
    }

    // Brisanje taska
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $_SESSION['user_id']]);

    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Task deleted']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
