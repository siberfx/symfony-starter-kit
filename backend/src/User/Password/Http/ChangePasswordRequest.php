<?php

declare(strict_types=1);

namespace App\User\Password\Http;

use Webmozart\Assert\Assert;

/**
 * Запрос на смену текущего пароля
 */
final readonly class ChangePasswordRequest
{
    /**
     * @param non-empty-string $currentPassword Текущий пароль
     * @param non-empty-string $newPassword Новый пароль
     * @param non-empty-string $newPasswordConfirmation Подтверждение нового пароля
     */
    public function __construct(
        public string $currentPassword,
        public string $newPassword,
        public string $newPasswordConfirmation,
    ) {
        Assert::minLength($this->newPassword, 6);
        Assert::eq($this->newPassword, $this->newPasswordConfirmation);
        Assert::notEq($this->newPassword, $this->currentPassword);
    }
}
