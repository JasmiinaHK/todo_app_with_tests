<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TestHelper.php';

class TaskTest extends TestCase
{
    private $baseUrl;
    private $testEmail = 'taskuser@example.com';
    private $testPassword = 'Task123!';
    private $cookieFile = 'task_test_cookies.txt';
    private $userId;
    
    protected function setUp(): void
    {
        TestHelper::resetDatabase();
        $config = TestHelper::getConfig();
        $this->baseUrl = rtrim($config['base_url'], '/');
        
        // Create a test user
        $this->userId = TestHelper::createTestUser('taskuser', $this->testEmail, $this->testPassword);
        
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
    
    public function testAddNewTask()
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'This is a test task description',
            'add_task' => '1' // Form submit button
        ];
        
        // Submit the form
        $ch = curl_init($this->baseUrl . '/add_task.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($taskData),
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => true
        ]);
        
        $response = curl_exec($ch);
        $redirectUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Should redirect back to dashboard or tasks page
        $this->assertEquals(200, $httpCode);
        $this->assertStringContainsString('dashboard.php', $redirectUrl);
        
        // Verify task was created in database
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? AND title = ?');
        $stmt->execute([$this->userId, $taskData['title']]);
        $task = $stmt->fetch();
        
        $this->assertIsArray($task);
        $this->assertEquals($taskData['description'], $task['description']);
        $this->assertEquals('pending', $task['status']);
    }
    
    public function testAddTaskWithEmptyTitle()
    {
        $taskData = [
            'title' => '', // Empty title
            'description' => 'This should fail',
            'add_task' => '1'
        ];
        
        $response = $this->submitTaskForm($taskData);
        
        // Should show error message
        $this->assertStringContainsString('The assignment title is mandatory.', $response);
        
        // Verify no task was created
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = ?');
        $stmt->execute([$this->userId]);
        $count = $stmt->fetchColumn();
        
        $this->assertEquals(0, $count);
    }
    
    public function testAddTaskWithLongTitle()
    {
        $longTitle = str_repeat('a', 256); // Exceeds typical 255 char limit
        
        $taskData = [
            'title' => $longTitle,
            'description' => 'This should fail due to long title',
            'add_task' => '1'
        ];
        
        $response = $this->submitTaskForm($taskData);
        
        // Should show error message or handle the long title appropriately
        // This depends on your validation
        $this->assertStringContainsString('The title is too long', $response);
    }
    
    private function submitTaskForm($data)
    {
        $ch = curl_init($this->baseUrl . '/add_task.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => false
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
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
