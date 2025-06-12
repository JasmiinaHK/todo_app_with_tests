<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html"); // Ako korisnik nije prijavljen, vrati ga na login
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <h2>Welcome, <?php echo $_SESSION["user_name"]; ?>!</h2> <!-- Prikazujemo ime korisnika -->
        <div class="task-input">
            <input type="text" id="task-title" placeholder="Enter task...">
            <select id="task-category">
                <option value="Work">Work</option>
                <option value="Personal">Personal</option>
                <option value="Shopping">Shopping</option>
            </select>
            <input type="datetime-local" id="task-due">
            <button id="add-task">Add Task</button>
        </div>
        <div class="task-filters">
            <button id="filter-all">All</button>
            <button id="filter-completed">Completed</button>
            <button id="filter-pending">Pending</button>
        </div>
        <ul id="task-list"></ul>

        <p><a href="logout.php">Logout</a></p> <!-- Dodali smo dugme za logout -->
    </div>
    <script src="script.js"></script>
</body>
</html>
