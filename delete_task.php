<?php
session_start();
require_once "config.php";

if (!isset($_SESSION["user_id"])) {
    die("You are not logged in!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_id = $_POST["task_id"];

    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$task_id, $_SESSION["user_id"]])) {
        echo "Task deleted!";
    } else {
        echo "Error deleting task!";
    }
}
?>
