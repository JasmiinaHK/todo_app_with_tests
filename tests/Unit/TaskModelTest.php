<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../TestHelper.php';

class TaskModelTest extends TestCase
{
    private $testUserId;
    
    protected function setUp(): void
    {
        TestHelper::resetDatabase();
        // Create a test user for task tests
        $this->testUserId = TestHelper::createTestUser('taskuser', 'taskuser@example.com');
    }

    public function testCreateTask()
    {
        $taskId = TestHelper::createTestTask($this->testUserId, 'Test Task 1');
        
        $this->assertIsNumeric($taskId);
        $this->assertGreaterThan(0, $taskId);
        
        // Verify task was created
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
        $stmt->execute([$taskId]);
        $task = $stmt->fetch();
        
        $this->assertEquals('Test Task 1', $task['title']);
        $this->assertEquals('pending', $task['status']);
        $this->assertEquals($this->testUserId, $task['user_id']);
    }

    public function testUpdateTaskStatus()
    {
        // Create a test task
        $taskId = TestHelper::createTestTask($this->testUserId, 'Task to update');
        
        // Update the task status
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('UPDATE tasks SET status = ? WHERE id = ?');
        $result = $stmt->execute(['completed', $taskId]);
        
        $this->assertTrue($result);
        
        // Verify the update
        $stmt = $pdo->prepare('SELECT status FROM tasks WHERE id = ?');
        $stmt->execute([$taskId]);
        $status = $stmt->fetchColumn();
        
        $this->assertEquals('completed', $status);
    }

    public function testDeleteTask()
    {
        // Create a test task
        $taskId = TestHelper::createTestTask($this->testUserId, 'Task to delete');
        
        // Delete the task
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
        $result = $stmt->execute([$taskId]);
        
        $this->assertTrue($result);
        
        // Verify deletion
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE id = ?');
        $stmt->execute([$taskId]);
        $count = $stmt->fetchColumn();
        
        $this->assertEquals(0, $count);
    }

    public function testTaskUserRelationship()
    {
        // Create a task for test user
        $taskId = TestHelper::createTestTask($this->testUserId, 'User relationship test');
        
        // Get task with user info using JOIN
        $pdo = TestHelper::getPdo();
        $stmt = $pdo->prepare(
            'SELECT t.*, u.username, u.email 
             FROM tasks t 
             JOIN users u ON t.user_id = u.id 
             WHERE t.id = ?'
        );
        $stmt->execute([$taskId]);
        $taskWithUser = $stmt->fetch();
        
        $this->assertIsArray($taskWithUser);
        $this->assertEquals('taskuser', $taskWithUser['username']);
        $this->assertEquals('taskuser@example.com', $taskWithUser['email']);
    }
}
