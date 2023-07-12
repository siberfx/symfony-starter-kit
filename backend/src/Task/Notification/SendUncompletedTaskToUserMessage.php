<?php

declare(strict_types=1);

namespace App\Task\Notification;

use App\Infrastructure\Message;

/**
 * Message для отправки пользователя списка невыполненных задач
 */
final readonly class SendUncompletedTaskToUserMessage implements Message
{
}
