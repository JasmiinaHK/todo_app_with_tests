<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TestHelper.php';

class LogoutTest extends TestCase
{
    private $baseUrl;
    private $testEmail = 'test@example.com';
    private $testPassword = 'Test123!';
    private $cookieFile = 'test_cookies.txt';
    
    protected function setUp(): void
    {
        TestHelper::resetDatabase();
        $config = TestHelper::getConfig();
        $this->baseUrl = rtrim($config['base_url'], '/');
        
        // Create a test user
        TestHelper::createTestUser('testuser', $this->testEmail, $this->testPassword);
        
        // Initialize cookie file
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }
    
    protected function tearDown(): void
    {
        // Clean up cookie file
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }
    
    public function testLogoutDestroysSession()
    {
        // First, log in
        $this->loginUser();
        
        // Access a protected page to verify session
        $ch = curl_init($this->baseUrl . '/dashboard.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Should be able to access dashboard when logged in
        $this->assertEquals(200, $httpCode);
        curl_close($ch);
        
        // Now log out
        $ch = curl_init($this->baseUrl . '/logout.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => true
        ]);
        
        $response = curl_exec($ch);
        $redirectUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        
        // Should redirect to login page after logout
        $this->assertStringContainsString('login.php', $redirectUrl);
        
        // Try to access dashboard again
        $ch = curl_init($this->baseUrl . '/dashboard.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => true
        ]);
        
        $response = curl_exec($ch);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        
        // Should be redirected to login page
        $this->assertStringContainsString('login.php', $finalUrl);
    }
    
    public function testLogoutWithoutLogin()
    {
        // Try to access logout directly without logging in
        $ch = curl_init($this->baseUrl . '/logout.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => true
        ]);
        
        $response = curl_exec($ch);
        $redirectUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Should still redirect to login page
        $this->assertEquals(200, $httpCode);
        $this->assertStringContainsString('login.php', $redirectUrl);
    }
    
    private function loginUser()
    {
        $ch = curl_init($this->baseUrl . '/login.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'email' => $this->testEmail,
                'password' => $this->testPassword
            ]),
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200;
    }
}
