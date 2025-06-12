<?php
session_start();

// Provera da li je korisnik prijavljen
if (!isset($_SESSION["user_id"])) {
    header("Location: login.html");
    exit();
}

?>

<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION["user_name"]; ?>!</h1>
    <p>This is a protected page. Only logged-in users can view it.</p>
    <a href="logout.php">Log out</a>
</body>
</html>
