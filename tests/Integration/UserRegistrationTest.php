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
        $data = [
            'username' => 'testuser',
            'email' => 'user@example.com',
            'password' => 'Secure123!',
            'confirm_password' => 'Secure123!',
            'test_mode' => 'true'
        ];

        $response = $this->postRequest('/register.php', $data);
        $this->assertStringContainsString('"status":"success"', $response);
    }

    public function testMissingFields()
    {
        $data = [
            'email' => 'user@example.com',
            'password' => 'pass',
            'confirm_password' => 'pass',
            'test_mode' => 'true'
        ];

        $response = $this->postRequest('/register.php', $data);
        $this->assertStringContainsString('"status":"error"', $response);
        $this->assertStringContainsString('All fields are required', $response);
    }

    public function testPasswordMismatch()
    {
        $data = [
            'username' => 'testuser2',
            'email' => 'user2@example.com',
            'password' => 'pass123',
            'confirm_password' => 'diffpass',
            'test_mode' => 'true'
        ];

        $response = $this->postRequest('/register.php', $data);
        $this->assertStringContainsString('Passwords do not match', $response);
    }

    public function testInvalidEmail()
    {
        $data = [
            'username' => 'testuser3',
            'email' => 'invalid-email',
            'password' => 'pass123',
            'confirm_password' => 'pass123',
            'test_mode' => 'true'
        ];

        $response = $this->postRequest('/register.php', $data);
        $this->assertStringContainsString('Invalid email format', $response);
    }

    public function testDuplicateEmail()
    {
        // Prvi put
        $data = [
            'username' => 'testuser4',
            'email' => 'dupe@example.com',
            'password' => 'pass1234',
            'confirm_password' => 'pass1234',
            'test_mode' => 'true'
        ];
        $this->postRequest('/register.php', $data);

        // Pokušaj ponovo
        $response = $this->postRequest('/register.php', $data);
        $this->assertStringContainsString('Email is already registered', $response);
    }

    private function postRequest($endpoint, $data)
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }
}
