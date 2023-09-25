<?php

declare(strict_types=1);

namespace App\User\SignIn\Domain;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Symfony\Component\Uid\Uuid;

/**
 * Репозиторий токенов пользователя
 */
#[AsService]
final readonly class UserTokens
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function getById(Uuid $userTokenId): UserToken
    {
        $userToken = $this->entityManager->getRepository(UserToken::class)->find($userTokenId);
        if (!$userToken instanceof UserToken) {
            throw new DomainException('Токен пользователя не найден.');
        }

        return $userToken;
    }

    public function remove(UserToken $userToken): void
    {
        $this->entityManager->remove($userToken);
    }

    public function add(UserToken $userToken): void
    {
        $this->entityManager->persist($userToken);
    }
}
