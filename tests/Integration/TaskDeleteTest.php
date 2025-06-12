<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TestHelper.php';

class TaskDeleteTest extends TestCase
{
    private $baseUrl;
    private $testEmail = 'deleteuser@example.com';
    private $testPassword = 'Delete123!';
    private $cookieFile = 'delete_test_cookies.txt';
    private $userId;
    private $taskId;
    
    protected function setUp(): void
    {
        TestHelper::resetDatabase();
        $config = TestHelper::getConfig();
        $this->baseUrl = rtrim($config['base_url'], '/');
        
        // Create a test user and task
        $this->userId = TestHelper::createTestUser('deleteuser', $this->testEmail, $this->testPassword);
        $this->taskId = TestHelper::createTestTask($this->userId, 'Task to delete');
        
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
    
    public function testDeleteTask()
    {
        // First, verify task exists
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$this->taskId, $this->userId]);
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count, 'Task should exist before deletion');
        
        // Delete the task
        $ch = curl_init($this->baseUrl . '/delete_task.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['task_id' => $this->taskId]),
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
        
        // Verify task was deleted from database
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$this->taskId, $this->userId]);
        $count = $stmt->fetchColumn();
        
        $this->assertEquals(0, $count, 'Task should be deleted');
    }
    
    public function testDeleteNonExistentTask()
    {
        $nonExistentTaskId = 9999;
        
        // Try to delete a task that doesn't exist
        $ch = curl_init($this->baseUrl . '/delete_task.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['task_id' => $nonExistentTaskId]),
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Should handle non-existent task gracefully
        $this->assertEquals(200, $httpCode);
    }
    
    public function testDeleteOtherUsersTask()
    {
        // Create another user and task
        $otherUserId = TestHelper::createTestUser('otheruser', 'other@example.com', 'Other123!');
        $otherTaskId = TestHelper::createTestTask($otherUserId, 'Other user\'s task');
        
        // Try to delete the other user's task
        $ch = curl_init($this->baseUrl . '/delete_task.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['task_id' => $otherTaskId]),
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HEADER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Should not be able to delete other user's task
        $this->assertEquals(403, $httpCode, 'Should return 403 Forbidden when trying to delete another user\'s task');
        
        // Verify other user's task still exists
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$otherTaskId, $otherUserId]);
        $count = $stmt->fetchColumn();
        
        $this->assertEquals(1, $count, 'Other user\'s task should still exist');
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
