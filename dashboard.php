<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$filter = $_GET['filter'] ?? 'all';

try {
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset={$config['db']['charset']}";
    $pdo = new PDO($dsn, $config['db']['username'], $config['db']['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT * FROM tasks WHERE user_id = ?";
    if ($filter === 'completed') {
        $query .= " AND status = 'completed'";
    } elseif ($filter === 'pending') {
        $query .= " AND status = 'pending'";
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id']]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>To-Do List</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app-container">
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>

    <div class="task-input">
        <form method="POST" action="add_task.php">
            <input type="text" name="title" placeholder="Enter task..." required>
            <select name="category">
                <option value="Work">Work</option>
                <option value="Personal">Personal</option>
                <option value="Shopping">Shopping</option>
            </select>
            <input type="datetime-local" name="due_date">
            <button type="submit">Add Task</button>
        </form>
    </div>

    <div class="task-filters">
        <a href="?filter=all"><button id="filter-all">All</button></a>
        <a href="?filter=completed"><button id="filter-completed">Completed</button></a>
        <a href="?filter=pending"><button id="filter-pending">Pending</button></a>
    </div>

    <ul id="task-list">
        <?php foreach ($tasks as $task): ?>
            <li><?= htmlspecialchars($task['title']) ?> - <?= htmlspecialchars($task['status']) ?></li>
        <?php endforeach; ?>
    </ul>

    <p><a href="logout.php">Logout</a></p>
</div>
</body>
</html>
