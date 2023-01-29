<?php

declare(strict_types=1);

namespace App\Tests\Functional\Task;

use App\Tests\Functional\SDK\ApiWebTestCase;
use App\Tests\Functional\SDK\Task;
use App\Tests\Functional\SDK\User;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 *
 * @testdox Функциональный тест получения информации о сущности Task
 */
final class TaskInfoTest extends ApiWebTestCase
{
    /**
     * @testdox Получение информации по сущности Task
     */
    public function testSuccess(): void
    {
        $token = User::auth();

        $taskId = Task::createAndReturnId($taskName = 'Тестовая задача 1', $token);

        $tasks = Task::list($token);

        self::assertCount(1, $tasks);

        $response = self::request('GET', "/api/tasks/{$taskId}", token: $token);
        self::assertSuccessResponse($response);

        $task = self::jsonDecode($response->getContent());

        self::assertNotNull($task['id']);
        self::assertSame($taskName, $task['taskName']);
        self::assertFalse($task['isCompleted']);
        self::assertNotNull($task['createdAt']);
        self::assertNull($task['completedAt']);
        self::assertNull($task['updatedAt']);
    }

    /**
     * @testdox Task не найден
     */
    public function testNotFound(): void
    {
        $token = User::auth();

        Task::create('Тестовая задача 1', $token);

        $taskId = (string) Uuid::v4();
        $response = self::request('GET', "/api/tasks/{$taskId}", token: $token);
        self::assertNotFound($response);
    }

    /**
     * @testdox Доступ запрещен для пользователя не автора
     */
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create('Тестовая задача №1', $token);

        $this->tearDown();
        $tokenSecond = User::auth('second@example.com');
        $taskId = Task::createAndReturnId('Тестовая задача №2 ', $tokenSecond);

        $response = self::request('GET', "/api/tasks/{$taskId}", token: $token);
        self::assertNotFound($response);
    }

    /**
     * @dataProvider notValidTokenDataProvider
     *
     * @testdox Доступ запрещен, невалидный токен
     */
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId('Тестовая задача 1', $token);

        $response = self::request('GET', "/api/tasks/{$taskId}", token: $notValidToken);

        self::assertAccessDenied($response);
    }
}
