<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use App\Infrastructure\AsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Сериализует объект ошибки в JsonResponse
 */
#[AsService]
final readonly class CreateExceptionJsonResponse
{
    public function __construct(
        private SerializerInterface $serializer,
        private TranslatorInterface $translator
    ) {}

    public function __invoke(ApiException $e): JsonResponse
    {
        $content = $this->serializer->serialize(
            new ApiErrorResponse($this->translator->trans($e->getErrorMessage()), $e->getErrors(), $e->getApiCode()),
            JsonEncoder::FORMAT
        );

        return new JsonResponse($content, $e->getHttpCode(), [], true);
    }
}
