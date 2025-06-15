<?php

class TestHelper {
    private static $config;
    private static $pdo;

    public static function getConfig() {
        if (!self::$config) {
            self::$config = require __DIR__ . '/../config.test.php';
        }
        return self::$config;
    }

    public static function getPdo() {
        if (!self::$pdo) {
            $config = self::getConfig()['db'];
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

            self::$pdo = new PDO($dsn, $config['username'], $config['password']);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        return self::$pdo;
    }

    public static function resetDatabase() {
        $pdo = self::getPdo();
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $pdo->exec("TRUNCATE TABLE `$table`");
        }

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    public static function createTestUser($username = null, $email = null, $password = 'password123') {
        $pdo = self::getPdo();

        if (!$username) {
            $username = 'user_' . uniqid();
        }

        if (!$email) {
            $email = $username . '@example.com';
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password) VALUES (?, ?, ?)'
        );

        $stmt->execute([$username, $email, $hashedPassword]);
        return $pdo->lastInsertId();
    }

    public static function deleteUserByEmail($email) {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
        $stmt->execute([$email]);
    }

    public static function createTestTask($userId, $title = 'Test Task', $status = 'pending') {
        $pdo = self::getPdo();
        $stmt = $pdo->prepare(
            'INSERT INTO tasks (user_id, title, status) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $title, $status]);
        return $pdo->lastInsertId();
    }
}
