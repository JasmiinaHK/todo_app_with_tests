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
        
        // Create a test user for duplicate email test
        TestHelper::createTestUser('existinguser', 'existing@example.com');
    }
    
    public function testInvalidEmail()
    {
        $testData = [
            'username' => 'testuser',
            'email' => 'invalid-email',
            'password' => 'Test123!',
            'confirm_password' => 'Test123!'
        ];
        
        $response = $this->makeRequest($testData);
        
        // Should not redirect on error
        $this->assertStringContainsString('Invalid email address', $response);
    }
    
    public function testDuplicateEmail()
    {
        $testData = [
            'username' => 'newuser',
            'email' => 'existing@example.com', // Already exists
            'password' => 'Test123!',
            'confirm_password' => 'Test123!'
        ];
        
        $response = $this->makeRequest($testData);
        
        $this->assertStringContainsString('Email already exists', $response);
    }
    
    public function testPasswordMismatch()
    {
        $testData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Test123!',
            'confirm_password' => 'Different123!' // Mismatch
        ];
        
        $response = $this->makeRequest($testData);
        
        $this->assertStringContainsString('Passwords do not match', $response);
    }
    
    public function testWeakPassword()
    {
        $testData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'weak',
            'confirm_password' => 'weak'
        ];
        
        $response = $this->makeRequest($testData);
        
        $this->assertStringContainsString('The password must be at least 6 characters long', $response);
    }
    
    public function testEmptyFields()
    {
        $testData = [
            'username' => '',
            'email' => '',
            'password' => '',
            'confirm_password' => ''
        ];
        
        $response = $this->makeRequest($testData);
        
        $this->assertStringContainsString('All fields are required', $response);
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
