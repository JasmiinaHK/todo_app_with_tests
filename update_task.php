<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "Invalid task ID.";
    exit;
}

$task_id = (int) $_GET['id'];
$user_id = $_SESSION['user_id'];

// Provjera da li zadatak pripada korisniku
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$task_id, $user_id]);
$task = $stmt->fetch();

if (!$task) {
    http_response_code(403);
    echo "You do not have permission to modify this task.";
    exit;
}

// Promjena statusa
$new_status = $task['is_completed'] ? 0 : 1;
$stmtUpdate = $pdo->prepare("UPDATE tasks SET is_completed = ? WHERE id = ?");
$stmtUpdate->execute([$new_status, $task_id]);

header("Location: dashboard.php");
exit;
