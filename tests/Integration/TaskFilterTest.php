<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TestHelper.php';

class TaskFilterTest extends TestCase
{
    private $baseUrl;
    private $testEmail = 'filteruser@example.com';
    private $testPassword = 'Filter123!';
    private $cookieFile = 'filter_test_cookies.txt';
    private $userId;
    private $pendingTasks = [];
    private $completedTasks = [];
    
    protected function setUp(): void
    {
        TestHelper::resetDatabase();
        $config = TestHelper::getConfig();
        $this->baseUrl = rtrim($config['base_url'], '/');
        
        // Create a test user
        $this->userId = TestHelper::createTestUser('filteruser', $this->testEmail, $this->testPassword);
        
        // Create test tasks
        $this->pendingTasks[] = TestHelper::createTestTask($this->userId, 'Pending Task 1', 'pending');
        $this->pendingTasks[] = TestHelper::createTestTask($this->userId, 'Pending Task 2', 'pending');
        $this->completedTasks[] = TestHelper::createTestTask($this->userId, 'Completed Task 1', 'completed');
        $this->completedTasks[] = TestHelper::createTestTask($this->userId, 'Completed Task 2', 'completed');
        
        // Initialize cookie file
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
        
        // Log in the user
        $this->loginUser();
    }
    
    protected function tearDown(): void
    {
        // Clean up cookie file
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }
    
    public function testShowAllTasks()
    {
        // Access the tasks page with no filter (should show all tasks)
        $ch = curl_init($this->baseUrl . '/todo.php?filter=all');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_HEADER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->assertEquals(200, $httpCode);
        
        // Check if both pending and completed tasks are shown
        foreach (['Pending Task 1', 'Pending Task 2', 'Completed Task 1', 'Completed Task 2'] as $taskTitle) {
            $this->assertStringContainsString($taskTitle, $response, "Task '$taskTitle' should be visible in 'all' filter");
        }
    }
    
    public function testShowPendingTasks()
    {
        // Access the tasks page with pending filter
        $ch = curl_init($this->baseUrl . '/todo.php?filter=pending');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_HEADER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->assertEquals(200, $httpCode);
        
        // Check if only pending tasks are shown
        foreach (['Pending Task 1', 'Pending Task 2'] as $taskTitle) {
            $this->assertStringContainsString($taskTitle, $response, "Pending task '$taskTitle' should be visible in 'pending' filter");
        }
        
        foreach (['Completed Task 1', 'Completed Task 2'] as $taskTitle) {
            $this->assertStringNotContainsString($taskTitle, $response, "Completed task '$taskTitle' should not be visible in 'pending' filter");
        }
    }
    
    public function testShowCompletedTasks()
    {
        // Access the tasks page with completed filter
        $ch = curl_init($this->baseUrl . '/todo.php?filter=completed');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_HEADER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->assertEquals(200, $httpCode);
        
        // Check if only completed tasks are shown
        foreach (['Completed Task 1', 'Completed Task 2'] as $taskTitle) {
            $this->assertStringContainsString($taskTitle, $response, "Completed task '$taskTitle' should be visible in 'completed' filter");
        }
        
        foreach (['Pending Task 1', 'Pending Task 2'] as $taskTitle) {
            $this->assertStringNotContainsString($taskTitle, $response, "Pending task '$taskTitle' should not be visible in 'completed' filter");
        }
    }
    
    public function testInvalidFilterShowsAllTasks()
    {
        // Access the tasks page with an invalid filter
        $ch = curl_init($this->baseUrl . '/todo.php?filter=invalid');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_HEADER => false,
            CURLOPT_FOLLOWLOCATION => true // Follow redirects in case of invalid filter
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirectUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        
        $this->assertEquals(200, $httpCode);
        
        // Check if it defaults to showing all tasks
        foreach (['Pending Task 1', 'Pending Task 2', 'Completed Task 1', 'Completed Task 2'] as $taskTitle) {
            $this->assertStringContainsString($taskTitle, $response, "Task '$taskTitle' should be visible with invalid filter");
        }
    }
    
    public function testFilterButtonsInUI()
    {
        // Access the tasks page
        $ch = curl_init($this->baseUrl . '/todo.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_HEADER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->assertEquals(200, $httpCode);
        
        // Check if filter buttons are present in the UI
        $this->assertStringContainsString('?filter=all', $response, 'All filter button should be present');
        $this->assertStringContainsString('?filter=pending', $response, 'Pending filter button should be present');
        $this->assertStringContainsString('?filter=completed', $response, 'Completed filter button should be present');
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
            CURLOPT_FOLLOWLOCATION => false
        ]);
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $httpCode === 200 || $httpCode === 302;
    }
}
