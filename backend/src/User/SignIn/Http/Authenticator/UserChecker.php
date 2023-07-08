<?php

declare(strict_types=1);

namespace App\User\SignIn\Http\Authenticator;

use App\Infrastructure\ApiException\ApiBadResponseException;
use App\Infrastructure\ApiException\ApiErrorCode;
use App\Infrastructure\AsService;
use App\Mailer\EmailConfirmation\Command\ConfirmEmailMessage;
use App\User\SignUp\Domain\User;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @see https://symfony.com/doc/current/security/user_checkers.html
 *
 * Класс проверяет подтвержден ли email пользователя
 */
#[AsService]
final readonly class UserChecker implements UserCheckerInterface
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isConfirmed()) {
            return;
        }

        $this->messageBus->dispatch(new ConfirmEmailMessage($user->confirmToken->value, $user->userEmail->value));

        throw new ApiBadResponseException('user.exception.not_confirmed_email', ApiErrorCode::EmailIsNotConfirmed);
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
