<?php
session_start();
require_once "config.php"; // Konekcija sa bazom

header("Content-Type: application/json"); // Postavi JSON response

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Provera da li su polja prazna
    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "Email and password are required!"]);
        exit();
    }

    // Provera da li korisnik postoji u bazi
    $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_name"] = $user["full_name"];
        echo json_encode(["status" => "success", "message" => "Login successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid email or password!"]);
    }
    exit();
}
?>
