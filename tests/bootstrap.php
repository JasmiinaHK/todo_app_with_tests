<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../config.test.php';

// Function to initialize test database
function initializeTestDatabase($config) {
    try {
        // Connect to MySQL without selecting a database
        $pdo = new PDO(
            "mysql:host={$config['db']['host']}",
            $config['db']['username'],
            $config['db']['password']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create test database if it doesn't exist
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$config['db']['dbname']}`");
        $pdo->exec("USE `{$config['db']['dbname']}`");

        // Import database schema from the main database or schema file
        // You'll need to create a schema.sql file with your database structure
        if (file_exists(__DIR__ . '/schema.sql')) {
            $sql = file_get_contents(__DIR__ . '/schema.sql');
            $pdo->exec($sql);
        }

        return true;
    } catch (PDOException $e) {
        die("Test database setup failed: " . $e->getMessage());
    }
}

// Get test configuration
$config = require __DIR__ . '/../config.test.php';

// Initialize test database
initializeTestDatabase($config);
