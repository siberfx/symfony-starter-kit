<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiException;

use App\Infrastructure\AsService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Проверяет тип ошибки, проеобразовывает ее в JsonResponse
 */
#[AsService]
final readonly class ExceptionEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private CreateExceptionJsonResponse $createExceptionJsonResponse)
    {
    }

    /**
     * @return array<string, array{0: string, 1: int}|list<array{0: string, 1?: int}>|string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => [
                ['setResponse', -1],
            ],
        ];
    }

    public function setResponse(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!\in_array('application/json', $request->getAcceptableContentTypes(), true)) {
            return;
        }

        $e = $event->getThrowable();
        if (!$e instanceof ApiException) {
            return;
        }

        $response = ($this->createExceptionJsonResponse)($e);
        $event->setResponse($response);
        $event->allowCustomResponseCode();
    }
}
