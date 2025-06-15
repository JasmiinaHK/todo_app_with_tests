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

        $this->userId = TestHelper::createTestUser('deleteuser', $this->testEmail, $this->testPassword);
        $this->taskId = TestHelper::createTestTask($this->userId, 'Task to delete');

        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }

        $this->loginUser();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->cookieFile)) {
            unlink($this->cookieFile);
        }
    }

    public function testDeleteTask()
    {
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$this->taskId, $this->userId]);
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count, 'Task should exist before deletion');

        $ch = curl_init($this->baseUrl . '/delete_task_api.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['task_id' => $this->taskId]),
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);
        $data = json_decode($response, true);
        $this->assertEquals('success', $data['status']);

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE id = ? AND user_id = ?');
        $stmt->execute([$this->taskId, $this->userId]);
        $count = $stmt->fetchColumn();

        $this->assertEquals(0, $count, 'Task should be deleted');
    }

    public function testDeleteNonExistentTask()
    {
        $nonExistentTaskId = 9999;

        $ch = curl_init($this->baseUrl . '/delete_task_api.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['task_id' => $nonExistentTaskId]),
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(404, $httpCode);
        $data = json_decode($response, true);
        $this->assertEquals('error', $data['status']);
    }

    public function testDeleteOtherUsersTask()
    {
        $otherUserId = TestHelper::createTestUser('otheruser', 'other@example.com', 'Other123!');
        $otherTaskId = TestHelper::createTestTask($otherUserId, 'Other user task');

        $ch = curl_init($this->baseUrl . '/delete_task_api.php');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['task_id' => $otherTaskId]),
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(403, $httpCode);
        $data = json_decode($response, true);
        $this->assertEquals('error', $data['status']);

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
