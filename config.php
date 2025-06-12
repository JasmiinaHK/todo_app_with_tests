<?php
// Prikazivanje svih PHP grešaka
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Konekcija sa bazom
$host = "sql207.infinityfree.com";
$user = "if0_39140399";
$password = "t9geS3o7oZp9"; 
$database = "if0_39140399_todo_app";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("The connection to the database failed: " . $e->getMessage());
}
?>
