<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    die(" You are not logged in!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = trim($_POST["description"]); 
    $category = trim($_POST["category"]);
    $due_date = $_POST["due_date"];
    $user_id = $_SESSION["user_id"];

    if (empty($description)) {
        die(" Task name is required!");
    }

    $stmt = $conn->prepare("INSERT INTO tasks (user_id, description, category, due_date, status) VALUES (?, ?, ?, ?, 'pending')");
    if ($stmt->execute([$user_id, $description, $category, $due_date])) {
        echo json_encode(["success" => true, "message" => " Task successfully added!", "task_id" => $conn->lastInsertId()]);
    } else {
        echo json_encode(["success" => false, "message" => " Error adding task!"]);
    }
}
?>

