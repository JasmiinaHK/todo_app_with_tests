<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TestHelper.php';

class UserRegistrationInvalidTest extends TestCase
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
        // Očisti korisnika ako već postoji
        TestHelper::deleteUserByEmail('testuser@example.com');

        $testData = [
            'full_name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Test123!',
            'confirm_password' => 'Test123!'
        ];

        $response = $this->makeRequest($testData);

        $this->assertStringContainsString('"status":"success"', $response);
    }

    public function testInvalidEmail()
    {
        $testData = [
            'username' => 'testuser2',
            'email' => 'invalid-email',
            'password' => 'Test123!',
            'confirm_password' => 'Test123!'
        ];

        $response = $this->makeRequest($testData);

        $this->assertStringContainsString('Invalid email format', $response);
    }

    public function testDuplicateEmail()
    {
        // Kreiraj korisnika s istim emailom
        TestHelper::createTestUser('existinguser', 'testuser@example.com');

        $testData = [
            'full_name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'Test123!',
            'confirm_password' => 'Test123!'
        ];

        $response = $this->makeRequest($testData);

        $this->assertStringContainsString('Email is already registered', $response);
    }

    private function makeRequest($data)
    {
        $ch = curl_init($this->baseUrl . '/register.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
