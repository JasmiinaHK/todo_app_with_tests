<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$task_id = $_POST['task_id'] ?? null;

if (!$task_id) {
    header("Location: dashboard.php?error=missing_id");
    exit;
}

require_once __DIR__ . '/config.php';

try {
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset={$config['db']['charset']}";
    $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$task_id, $_SESSION['user_id']]);

    header("Location: dashboard.php?deleted=" . ($stmt->rowCount() > 0 ? "1" : "0"));
    exit;
} catch (PDOException $e) {
    header("Location: dashboard.php?error=server");
    exit;
}
