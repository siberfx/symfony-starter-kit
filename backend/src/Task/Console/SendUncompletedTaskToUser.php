<?php

declare(strict_types=1);

namespace App\Task\Console;

use App\Mailer\UncompletedTasks\Command\UncompletedTaskData as MailerTaskData;
use App\Mailer\UncompletedTasks\Command\UncompletedTasksMessage;
use App\Task\Query\Task\FindUncompletedTasksByUserId\FindUncompletedTasksByUserId;
use App\Task\Query\Task\FindUncompletedTasksByUserId\FindUncompletedTasksByUserIdQuery;
use App\Task\Query\Task\FindUncompletedTasksByUserId\TaskData;
use App\User\SignUp\Query\FindAllUsers;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Консольная команда отправки пользователю списка невыполненных задач
 */
#[AsCommand(name: 'app:task:send-uncompleted', description: 'Отправляет пользователю список невыполненных задач')]
final class SendUncompletedTaskToUser extends Command
{
    use LockableTrait;

    public function __construct(
        private readonly FindUncompletedTasksByUserId $findUncompletedTasksByUserId,
        private readonly FindAllUsers $findAllUsers,
        private readonly LoggerInterface $logger,
        private readonly MessageBusInterface $messageBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $this->logger->info('Команда по отправке невыполненных задач уже запущена');

            return Command::SUCCESS;
        }

        $users = ($this->findAllUsers)();
        $emailSent = 0;
        foreach ($users as $user) {
            $uncompletedTasks = ($this->findUncompletedTasksByUserId)(new FindUncompletedTasksByUserIdQuery($user->id));
            if ($uncompletedTasks === []) {
                continue;
            }

            $mailerUncompletedTasks = array_map(
                static fn (TaskData $taskData): MailerTaskData => new MailerTaskData($taskData->taskName, $taskData->createdAt),
                $uncompletedTasks
            );

            $this->messageBus->dispatch(new UncompletedTasksMessage($user->email, $mailerUncompletedTasks));
            ++$emailSent;
        }

        $this->logger->info("Отправлено {$emailSent} писем о невыполненных задачах");

        $this->release();

        return Command::SUCCESS;
    }
}
