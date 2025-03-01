<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use App\Infrastructure\AsService;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\ValinorBundle\Configurator\MapperBuilderConfigurator;
use Override;
use Symfony\Component\Uid\Uuid;
use Throwable;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Конфигурация Valinor
 * - настройка кэширования
 * - обработка кастомных исключений
 *
 * @see https://github.com/CuyZ/Valinor-Bundle/#customizing-mapper-builder
 */
#[AsService]
final readonly class ValinorConfigurator implements MapperBuilderConfigurator
{
    #[Override]
    public function configure(MapperBuilder $builder): MapperBuilder
    {
        return $builder
            ->registerConstructor(Uuid::fromString(...))
            ->filterExceptions(static function (Throwable $exception): ErrorMessage {
                if ($exception instanceof InvalidArgumentException) {
                    return MessageBuilder::from($exception);
                }

                throw $exception;
            });
    }
}
