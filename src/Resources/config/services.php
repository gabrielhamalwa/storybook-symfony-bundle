<?php

declare(strict_types=1);

use Storybook\SymfonyBundle\Controller\StorybookController;
use Storybook\SymfonyBundle\EventListener\CorsListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(StorybookController::class)
        ->public()
        ->tag('controller.service_arguments');

    $services
        ->set(CorsListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.response']);
};
