<?php

declare(strict_types=1);

namespace App\Task\Console;

use App\Task\Notification\SendUncompletedTaskToUserMessage;
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

        $this->messageBus->dispatch(new SendUncompletedTaskToUserMessage());

        $this->release();

        return Command::SUCCESS;
    }
}
