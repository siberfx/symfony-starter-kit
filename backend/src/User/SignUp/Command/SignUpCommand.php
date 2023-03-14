<?php

declare(strict_types=1);

namespace App\User\SignUp\Command;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Webmozart\Assert\Assert;

/**
 * Команда для регистрации
 */
final readonly class SignUpCommand implements ApiRequest
{
    public function __construct(
        public string $email,
        public string $password
    ) {
        Assert::notEmpty($email);
        Assert::email($email);

        Assert::notEmpty($password);
        Assert::minLength($password, 6);
    }
}
