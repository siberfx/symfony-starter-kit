<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;

/**
 * @internal
 *
 * @testdox Список задач
 */
final class TaskListTest extends ApiWebTestCase
{
    /**
     * @testdox Получение списка из 2 созданных задач
     */
    public function testSuccess(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $tasks = Task::list($token);

        self::assertCount(2, $tasks);

        foreach ($tasks as $task) {
            self::assertNotNull($task['id']);
            self::assertFalse($task['isCompleted']);
            self::assertNotNull($task['taskName']);
        }
    }

    /**
     * @testdox Создано 2 статьи, limit = 1, получена 1 статья
     */
    public function testLimit(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $tasks = Task::list($token, 1);

        self::assertCount(1, $tasks);
    }

    /**
     * @testdox Создано 2 статьи, limit = 10, offset = 3, получено 0 статей
     */
    public function testOffsetEmptyResult(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $tasks = Task::list($token, 10, 3);

        self::assertCount(0, $tasks);
    }

    /**
     * @testdox Создано 3 статьи, limit = 1, offset = 2, получена 1 статья
     */
    public function testOffsetNotEmptyResult(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);
        Task::create('Тестовая задача 3', $token);

        $tasks = Task::list($token, 1, 2);

        self::assertCount(1, $tasks);
    }

    /**
     * @testdox Доступ разрешен только автору
     */
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача 1', $token);
        Task::create('Тестовая задача 2', $token);

        $this->tearDown();
        $tokenSecond = User::auth('second@example.com');
        $taskId3 = Task::createAndReturnId($taskName3 = 'Тестовая задача 3', $tokenSecond);
        $taskId4 = Task::createAndReturnId($taskName4 = 'Тестовая задача 4', $tokenSecond);

        $tasks = Task::list($token);

        foreach ($tasks as $task) {
            self::assertNotSame($task['id'], $taskId3);
            self::assertNotSame($task['id'], $taskId4);
            self::assertNotSame($task['taskName'], $taskName3);
            self::assertNotSame($task['taskName'], $taskName4);
        }
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $response = self::request('GET', '/api/tasks', token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
