<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TestHelper.php';

class LoginTest extends TestCase
{
    private $baseUrl;
    private $testUsername = 'testuser';
    private $testEmail = 'test@example.com';
    private $testPassword = 'Test123!';
    
    protected function setUp(): void
    {
        TestHelper::resetDatabase();
        $config = TestHelper::getConfig();
        $this->baseUrl = rtrim($config['base_url'], '/');
        
        // Create a test user
        TestHelper::createTestUser($this->testUsername, $this->testEmail, $this->testPassword);
    }
    
    public function testSuccessfulLogin()
    {
        // Initialize session
        $ch = curl_init();
        
        // Set up cURL options
        $options = [
            CURLOPT_URL => $this->baseUrl . '/login.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'email' => $this->testEmail,
                'password' => $this->testPassword
            ]),
            CURLOPT_COOKIEJAR => 'cookies.txt', // Save cookies to file
            CURLOPT_COOKIEFILE => 'cookies.txt', // Send cookies from file
            CURLOPT_HEADER => true // Include headers in output
        ];
        
        curl_setopt_array($ch, $options);
        
        // Execute login
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirectUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        
        // Check if login was successful (should redirect to dashboard)
        $this->assertEquals(200, $httpCode);
        $this->assertStringContainsString('dashboard.php', $redirectUrl);
        
        // Clean up
        curl_close($ch);
        if (file_exists('cookies.txt')) {
            unlink('cookies.txt');
        }
    }
    
    public function testRememberMeFunctionality()
    {
        // Initialize session
        $ch = curl_init();
        
        // Set up cURL options with remember me checked
        $options = [
            CURLOPT_URL => $this->baseUrl . '/login.php',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'email' => $this->testEmail,
                'password' => $this->testPassword,
                'remember' => '1' // Remember me checked
            ]),
            CURLOPT_COOKIEJAR => 'cookies.txt',
            CURLOPT_COOKIEFILE => 'cookies.txt',
            CURLOPT_HEADER => true
        ];
        
        curl_setopt_array($ch, $options);
        
        // Execute login
        curl_exec($ch);
        
        // Check if remember me cookie was set
        $cookies = file_get_contents('cookies.txt');
        $this->assertStringContainsString('remember_token', $cookies);
        
        // Clean up
        curl_close($ch);
        if (file_exists('cookies.txt')) {
            unlink('cookies.txt');
        }
    }
}
