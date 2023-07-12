<?php

declare(strict_types=1);

namespace App\Task\Notification;

use App\Infrastructure\AsService;
use App\Mailer\Notification\UncompletedTasks\UncompletedTasksMessage;
use App\Task\Query\Task\FindUncompletedTasksByUserId\FindUncompletedTasksByUserId;
use App\Task\Query\Task\FindUncompletedTasksByUserId\FindUncompletedTasksByUserIdQuery;
use App\User\SignUp\Query\FindAllUsers;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Отправляет пользователям список невыполненных задач
 */
#[AsService]
#[AsMessageHandler]
final readonly class SendUncompletedTaskToUserMessageHandler
{
    public function __construct(
        private FindUncompletedTasksByUserId $findUncompletedTasksByUserId,
        private FindAllUsers $findAllUsers,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(SendUncompletedTaskToUserMessage $message): void
    {
        $users = ($this->findAllUsers)();
        $emailSent = 0;
        foreach ($users as $user) {
            $uncompletedTasks = ($this->findUncompletedTasksByUserId)(new FindUncompletedTasksByUserIdQuery($user->id));
            if ($uncompletedTasks === []) {
                continue;
            }

            $this->messageBus->dispatch(new UncompletedTasksMessage($user->email, $uncompletedTasks));
            ++$emailSent;
        }

        $this->logger->info("Отправлено {$emailSent} писем о невыполненных задачах");
    }
}
