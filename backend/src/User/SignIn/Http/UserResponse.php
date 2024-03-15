<?php

declare(strict_types=1);

namespace App\User\SignIn\Http;

/**
 * Токен аутентификации
 */
final readonly class UserResponse
{
    /**
     * @param non-empty-string $token
     */
    public function __construct(
        public string $token,
    ) {}
}
