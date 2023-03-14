<?php

declare(strict_types=1);

namespace App\Task\Query\Task\FindUncompletedTasksByUserId;

use Symfony\Component\Uid\Uuid;

/**
 * Запрос на нахождение незавершенных задач по пользователю
 */
final readonly class FindUncompletedTasksByUserIdQuery
{
    public function __construct(public Uuid $userId)
    {
    }
}
