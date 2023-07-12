<?php

declare(strict_types=1);

namespace App\Seo\Http\Admin;

use App\Infrastructure\ApiException\ApiBadRequestException;
use App\Infrastructure\ApiRequestResolver\ApiRequest;
use App\Infrastructure\Flush;
use App\Infrastructure\SuccessResponse;
use App\Seo\Command\SaveSeo;
use App\Seo\Command\SaveSeoCommand;
use App\User\SignUp\Domain\UserRole;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Ручка сохранения SEO
 */
#[IsGranted(UserRole::User->value)]
#[Route('/admin/seo/save', methods: [Request::METHOD_POST])]
#[AsController]
final readonly class SeoSaveAction
{
    public function __construct(
        private SaveSeo $saveSeo,
        private Flush $flush,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(#[ApiRequest] SaveSeoCommand $saveSeoCommand): SuccessResponse
    {
        try {
            $seo = ($this->saveSeo)($saveSeoCommand);

            ($this->flush)();

            $this->logger->info('SEO сохранено', [
                'id' => $seo->getId(),
                'type' => $seo->getType()->value,
                self::class => __FUNCTION__,
            ]);
        } catch (InvalidArgumentException $e) {
            throw new ApiBadRequestException($e->getMessage());
        }

        return new SuccessResponse();
    }
}
