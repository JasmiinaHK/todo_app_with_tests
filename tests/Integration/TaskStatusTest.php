<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TestHelper.php';

class TaskStatusTest extends TestCase
{
    private $baseUrl;
    private $testEmail = 'statususer@example.com';
    private $testPassword = 'Status123!';
    private $cookieFile = 'status_test_cookies.txt';
    private $userId;
    private $taskId;
    
    protected function setUp(): void
    {
        TestHelper::resetDatabase();
        $config = TestHelper::getConfig();
        $this->baseUrl = rtrim($config['base_url'], '/');
        
        // Create a test user and task
        $this->userId = TestHelper::createTestUser('statususer', $this->testEmail, $this->testPassword);
        $this->taskId = TestHelper::createTestTask($this->userId, 'Task to update status');
        
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
    
    public function testMarkTaskAsCompleted()
    {
        // First, verify task is pending
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('SELECT status FROM tasks WHERE id = ?');
        $stmt->execute([$this->taskId]);
        $status = $stmt->fetchColumn();
        $this->assertEquals('pending', $status, 'Task should be pending initially');
        
        // Mark task as completed
        $ch = curl_init($this->baseUrl . '/update_task_status.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'task_id' => $this->taskId,
                'status' => 'completed'
            ]),
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest'],
            CURLOPT_HEADER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Should return 200 OK for AJAX request
        $this->assertEquals(200, $httpCode);
        
        // Verify task was updated in database
        $stmt = $pdo->prepare('SELECT status FROM tasks WHERE id = ?');
        $stmt->execute([$this->taskId]);
        $newStatus = $stmt->fetchColumn();
        
        $this->assertEquals('completed', $newStatus, 'Task status should be updated to completed');
    }
    
    public function testToggleTaskStatus()
    {
        $pdo = TestHelper::getPdo();
        
        // Toggle from pending to completed
        $this->toggleTaskStatus($this->taskId, 'completed');
        
        // Verify task is completed
        $stmt = $pdo->prepare('SELECT status FROM tasks WHERE id = ?');
        $stmt->execute([$this->taskId]);
        $status = $stmt->fetchColumn();
        $this->assertEquals('completed', $status, 'Task should be completed after first toggle');
        
        // Toggle back to pending
        $this->toggleTaskStatus($this->taskId, 'pending');
        
        // Verify task is pending again
        $stmt->execute([$this->taskId]);
        $status = $stmt->fetchColumn();
        $this->assertEquals('pending', $status, 'Task should be pending after second toggle');
    }
    
    public function testUpdateNonExistentTask()
    {
        $nonExistentTaskId = 9999;
        
        // Try to update a task that doesn't exist
        $ch = curl_init($this->baseUrl . '/update_task_status.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'task_id' => $nonExistentTaskId,
                'status' => 'completed'
            ]),
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest'],
            CURLOPT_HEADER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Should return 404 for non-existent task
        $this->assertEquals(404, $httpCode, 'Should return 404 for non-existent task');
    }
    
    public function testUpdateOtherUsersTask()
    {
        // Create another user and task
        $otherUserId = TestHelper::createTestUser('otherstatususer', 'otherstatus@example.com', 'Other123!');
        $otherTaskId = TestHelper::createTestTask($otherUserId, 'Other user\'s task');
        
        // Try to update the other user's task
        $ch = curl_init($this->baseUrl . '/update_task_status.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'task_id' => $otherTaskId,
                'status' => 'completed'
            ]),
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest'],
            CURLOPT_HEADER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Should return 403 Forbidden
        $this->assertEquals(403, $httpCode, 'Should return 403 when trying to update another user\'s task');
        
        // Verify other user's task was not updated
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('SELECT status FROM tasks WHERE id = ?');
        $stmt->execute([$otherTaskId]);
        $status = $stmt->fetchColumn();
        
        $this->assertEquals('pending', $status, 'Other user\'s task should still be pending');
    }
    
    private function toggleTaskStatus($taskId, $newStatus)
    {
        $ch = curl_init($this->baseUrl . '/update_task_status.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'task_id' => $taskId,
                'status' => $newStatus
            ]),
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_HTTPHEADER => ['X-Requested-With: XMLHttpRequest'],
            CURLOPT_HEADER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $this->assertEquals(200, $httpCode, 'Failed to toggle task status');
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
