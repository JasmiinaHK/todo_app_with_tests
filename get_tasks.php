<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    die(json_encode([]));
}

$user_id = $_SESSION["user_id"];

$stmt = $conn->prepare("SELECT id, description, category, due_date, status FROM tasks WHERE user_id = ?");
$stmt->execute([$user_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($tasks);
?>
