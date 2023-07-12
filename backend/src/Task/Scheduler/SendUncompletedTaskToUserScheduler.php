<?php

declare(strict_types=1);

namespace App\Task\Scheduler;

use App\Infrastructure\AsService;
use App\Task\Notification\SendUncompletedTaskToUserMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

/**
 * Шедулер отправки пользователю списка невыполненных задач
 */
#[AsService]
#[AsSchedule('uncompleted_tasks')]
final readonly class SendUncompletedTaskToUserScheduler implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())->add(
            RecurringMessage::cron(
                '15 21 */1 * *',
                new SendUncompletedTaskToUserMessage()),
        );
    }
}
