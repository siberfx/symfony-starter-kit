<?php

declare(strict_types=1);

namespace Dev\Tests\Functional\Task;

use Dev\Tests\Functional\SDK\ApiWebTestCase;
use Dev\Tests\Functional\SDK\Task;
use Dev\Tests\Functional\SDK\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

/**
 * @internal
 */
#[TestDox('Комментирование задачи')]
final class CreateCommentOnTaskTest extends ApiWebTestCase
{
    #[TestDox('Комментарий добавлен')]
    public function testSuccessfulCreationTask(): void
    {
        $token = User::auth();

        $taskId = Task::createAndReturnId(
            taskName: 'First task',
            token: $token,
        );

        $body = [];
        $body['commentBody'] = $commentText = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/add-comment', $taskId),
            body: $body,
            token: $token,
        );

        self::assertSuccessResponse($response);

        $response = self::request(
            method: Request::METHOD_GET,
            uri: \sprintf('/api/tasks/%s/comments', $taskId),
            token: $token,
        );

        /** @var array{
         *     data: array<int, array{
         *     id: string,
         *     body: string,
         *     createdAt: string,
         *     updatedAt: string|null,
         *    }>
         * } $comments */
        $comments = self::jsonDecode($response->getContent());

        self::assertCount(1, $comments['data']);
        self::assertSame($commentText, $comments['data'][0]['body']);
        self::assertNotEmpty($comments['data'][0]['id']);
        self::assertNotEmpty($comments['data'][0]['createdAt']);
        self::assertNull($comments['data'][0]['updatedAt']);
    }

    #[TestDox('Задача не найдена')]
    public function testNotFound(): void
    {
        $token = User::auth();

        Task::create(
            taskName: 'Тестовая задача 1',
            token: $token,
        );

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $taskId = (string) Uuid::v4();
        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/add-comment', $taskId),
            body: $body,
            token: $token,
        );

        self::assertNotFound($response);
    }

    #[TestDox('Нельзя комментировать выполненную задачу')]
    public function testAddCommentToCompletedTask(): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId(
            taskName: 'First task',
            token: $token,
        );

        self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/complete', $taskId),
            token: $token,
        );

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/add-comment', $taskId),
            body: $body,
            token: $token,
        );

        self::assertBadRequest($response);
    }

    #[DataProvider('notValidTokenDataProvider')]
    #[TestDox('Доступ запрещен')]
    public function testAccessDenied(string $notValidToken): void
    {
        $token = User::auth();
        $taskId = Task::createAndReturnId(
            taskName: 'Тестовая задача 1',
            token: $token,
        );

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/add-comment', $taskId),
            body: $body,
            token: $notValidToken,
        );

        self::assertAccessDenied($response);
    }

    #[TestDox('Комментирование разрешено только автору')]
    public function testNoAccessAnotherUser(): void
    {
        $token = User::auth();
        Task::create(
            taskName: 'Тестовая задача №1',
            token: $token,
        );

        $tokenSecond = User::auth('second@example.com');
        $taskId = Task::createAndReturnId(
            taskName: 'Тестовая задача №2 ',
            token: $tokenSecond,
        );

        $body = [];
        $body['commentBody'] = 'First comment';
        $body = json_encode($body, JSON_THROW_ON_ERROR);

        $response = self::request(
            method: Request::METHOD_POST,
            uri: \sprintf('/api/tasks/%s/add-comment', $taskId),
            body: $body,
            token: $token,
        );

        self::assertNotFound($response);
    }
}
