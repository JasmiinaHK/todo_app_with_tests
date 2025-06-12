<?php
session_start();
require_once "config.php"; // Konekcija sa bazom

header("Content-Type: application/json"); // Postavlja da PHP vrati JSON odgovor

// Proveri da li je metod POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Provera da li su polja prazna
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        echo json_encode(["status" => "error", "message" => "All fields are required!"]);
        exit();
    }

    // Provera da li se lozinke poklapaju
    if ($password !== $confirm_password) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match!"]);
        exit();
    }

    // Provera da li je email validan
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Email is not valid!"]);
        exit();
    }

    // Provera dužine lozinke
    if (strlen($password) < 6) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 6 characters long!"]);
        exit();
    }

    // Provera da li email već postoji u bazi
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(["status" => "error", "message" => "Email is already registered!"]);
        exit();
    }

    // Šifrovanje lozinke pomoću `password_hash`
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Ubacivanje korisnika u bazu
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$full_name, $email, $hashed_password])) {
        echo json_encode(["status" => "success", "message" => "Registration successful! Redirecting..."]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Error inserting into the database!"]);
        exit();
    }
}
?>
