<?php

declare(strict_types=1);

namespace App\User\Profile\Command\SaveProfile;

use App\Infrastructure\ApiRequestResolver\ApiRequest;
use Webmozart\Assert\Assert;

/**
 * Команда сохранения профиля
 */
final readonly class SaveProfileCommand implements ApiRequest
{
    public function __construct(
        public string $phone,
        public string $name
    ) {
        Assert::notEmpty($phone, 'Укажите телефон');
        Assert::notEmpty($name, 'Укажите имя');
    }
}
