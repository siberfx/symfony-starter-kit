<?php

declare(strict_types=1);

namespace Dev\Infrastructure;

use Dev\Infrastructure\ConsoleCommand\OpenApiRoutesDiffCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $di): void {
    $services = $di->services()->defaults()->autowire()->autoconfigure();

    $services->set(OpenApiRoutesDiffCommand::class);
};
