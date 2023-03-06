<?php

declare(strict_types=1);

namespace App\Setting\Domain;

use App\Infrastructure\AsService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Репозиторий Setting
 */
#[AsService]
final readonly class Settings
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function add(Setting $entity): void
    {
        $this->entityManager->persist($entity);
    }

    /**
     * @throws SettingNotFoundException
     */
    public function getByType(string $type): Setting
    {
        $setting = $this->entityManager->getRepository(Setting::class)->findOneBy(['type' => $type]);

        if ($setting === null) {
            throw new SettingNotFoundException();
        }

        return $setting;
    }

    /**
     * @return Setting[]
     */
    public function getAll(): array
    {
        /** @var Setting[] $settings */
        $settings = $this->entityManager->getRepository(Setting::class)->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $settings;
    }

    /**
     * @return Setting[]
     */
    public function getAllForPublic(): array
    {
        /** @var Setting[] $settings */
        $settings = $this->entityManager->getRepository(Setting::class)->createQueryBuilder('s')
            ->where('s.isPublic = 1')
            ->getQuery()->getResult();

        return $settings;
    }
}
