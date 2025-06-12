<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TestHelper.php';

class UserRegistrationTest extends TestCase
{
    private $baseUrl;
    
    protected function setUp(): void
    {
        TestHelper::resetDatabase();
        $config = TestHelper::getConfig();
        $this->baseUrl = rtrim($config['base_url'], '/');
    }
    
    public function testSuccessfulRegistration()
    {
        // Test data
        $testData = [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'Test123!',
            'confirm_password' => 'Test123!'
        ];
        
        // Create a temporary file to simulate uploaded file
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tmpFile, 'test image content');
        
        // Create a cURL file
        $avatar = new CURLFile($tmpFile, 'image/jpeg', 'test.jpg');
        
        // Add file to test data
        $testData['avatar'] = $avatar;
        
        // Initialize cURL
        $ch = curl_init($this->baseUrl . '/register.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // Execute the request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Clean up
        curl_close($ch);
        unlink($tmpFile);
        
        // Check if registration was successful (should redirect to login or dashboard)
        $this->assertEquals(200, $httpCode);
        
        // Verify user was created in database
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$testData['email']]);
        $user = $stmt->fetch();
        
        $this->assertIsArray($user);
        $this->assertEquals($testData['username'], $user['username']);
        $this->assertTrue(password_verify($testData['password'], $user['password']));
    }
}
