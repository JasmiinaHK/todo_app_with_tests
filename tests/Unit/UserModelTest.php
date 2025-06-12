<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TestHelper.php';

class UserModelTest extends TestCase
{
    protected function setUp(): void
    {
        TestHelper::resetDatabase();
    }

    public function testCreateUser()
    {
        $userId = TestHelper::createTestUser('testuser1', 'test1@example.com');
        $this->assertIsNumeric($userId);
        $this->assertGreaterThan(0, $userId);
    }

    public function testFindUserByEmail()
    {
        $testEmail = 'test2@example.com';
        $testUsername = 'testuser2';
        
        // Create test user
        $userId = TestHelper::createTestUser($testUsername, $testEmail);
        
        // Test finding the user
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$testEmail]);
        $user = $stmt->fetch();
        
        $this->assertIsArray($user);
        $this->assertEquals($testEmail, $user['email']);
        $this->assertEquals($testUsername, $user['username']);
    }

    public function testPasswordHashing()
    {
        $plainPassword = 'testpass123';
        $userId = TestHelper::createTestUser('testuser3', 'test3@example.com', $plainPassword);
        
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $hashedPassword = $stmt->fetchColumn();
        
        $this->assertTrue(password_verify($plainPassword, $hashedPassword));
    }

    public function testUniqueEmailConstraint()
    {
        $this->expectException(PDOException::class);
        
        $email = 'duplicate@example.com';
        
        // First user with this email - should succeed
        TestHelper::createTestUser('user1', $email);
        
        // Second user with same email - should fail
        TestHelper::createTestUser('user2', $email);
    }
}
