<?php

declare(strict_types=1);

namespace App\User\SignUp\Domain;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Токен подтверждения регистрации
 */
#[ORM\Embeddable]
final readonly class ConfirmToken
{
    #[ORM\Column(type: 'uuid', unique: true)]
    public Uuid $value;

    public function __construct(Uuid $value)
    {
        $this->value = $value;
    }

    /**
     * @param object $other
     */
    public function equalTo(mixed $other): bool
    {
        return $other::class === self::class && $this->value->equals($other->value);
    }
}
