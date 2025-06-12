<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TestHelper.php';

class UserLoginTest extends TestCase
{
    private $baseUrl;
    private $testEmail = 'existing@example.com';
    private $testPassword = 'CorrectPass123!';

    protected function setUp(): void
    {
        TestHelper::resetDatabase();
        $config = TestHelper::getConfig();
        $this->baseUrl = rtrim($config['base_url'], '/');

        // Create a test user
        TestHelper::createTestUser('existinguser', $this->testEmail, $this->testPassword);
    }

    public function testNonExistentAccount()
    {
        $response = $this->makeLoginRequest('nonexistent@example.com', 'somepassword');

        // Should show error message for non-existent account
        $this->assertStringContainsString('Incorrect email address or password', $response);
    }
    
    public function testIncorrectPassword()
    {
        $response = $this->makeLoginRequest($this->testEmail, 'wrongpassword');

        // Should show error message for incorrect password
        $this->assertStringContainsString('Incorrect email address or password', $response);
    }

    public function testEmptyCredentials()
    {
        // Test empty email
        $response = $this->makeLoginRequest('', $this->testPassword);
        $this->assertStringContainsString('Email is required', $response);

        // Test empty password
        $response = $this->makeLoginRequest($this->testEmail, '');
        $this->assertStringContainsString('Password is required', $response);

        // Test both empty
        $response = $this->makeLoginRequest('', '');
        $this->assertStringContainsString('Email is required', $response);
        $this->assertStringContainsString('Password is required', $response);
    }

    public function testCaseSensitiveEmail()
    {
        // Test with different email case (should be case insensitive)
        $uppercaseEmail = strtoupper($this->testEmail);
        $response = $this->makeLoginRequest($uppercaseEmail, $this->testPassword);

        // Should still log in successfully
        $this->assertStringNotContainsString('Incorrect email address or password', $response);
    }

    private function makeLoginRequest($email, $password)
    {
        $ch = curl_init($this->baseUrl . '/login.php');

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'email' => $email,
                'password' => $password
            ]),
            CURLOPT_FOLLOWLOCATION => false, // Don't follow redirects
            CURLOPT_HEADER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $response;
    }
}
