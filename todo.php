<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

$userName = $_SESSION['username'] ?? 'User';

// Dohvati taskove
$filter = $_GET['filter'] ?? 'all';
$allowed = ['all', 'completed', 'pending'];
if (!in_array($filter, $allowed)) {
    $filter = 'all';
}

$pdo = getPdo();
$sql = "SELECT id, title, status FROM tasks WHERE user_id = ?";
$args = [$_SESSION['user_id']];

if ($filter !== 'all') {
    $sql .= " AND status = ?";
    $args[] = $filter;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$tasks = $stmt->fetchAll();
?>
