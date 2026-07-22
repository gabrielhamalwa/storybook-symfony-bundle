<?php

declare(strict_types=1);

use Storybook\SymfonyBundle\Component\ComponentResolver;
use Storybook\SymfonyBundle\Component\ControllerFragmentAdapter;
use Storybook\SymfonyBundle\Component\LiveComponentAdapter;
use Storybook\SymfonyBundle\Component\TemplateAdapter;
use Storybook\SymfonyBundle\Component\TwigComponentAdapter;
use Storybook\SymfonyBundle\Controller\StorybookController;
use Storybook\SymfonyBundle\EventListener\CorsListener;
use Storybook\SymfonyBundle\EventListener\StaticFormDataListener;
use Storybook\SymfonyBundle\Indexer\ComponentIndexer;
use Storybook\SymfonyBundle\Indexer\ComponentIndexerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services
        ->set(TwigComponentAdapter::class);

    $services
        ->set(TemplateAdapter::class);

    $services
        ->set(ControllerFragmentAdapter::class);

    $services
        ->set(LiveComponentAdapter::class);

    $services
        ->set(ComponentResolver::class);

    $services
        ->set(ComponentIndexer::class)
        ->arg('$projectDir', '%kernel.project_dir%');

    $services
        ->alias(ComponentIndexerInterface::class, ComponentIndexer::class);

    $services
        ->set(StorybookController::class)
        ->public()
        ->tag('controller.service_arguments');

    $services
        ->set(CorsListener::class)
        ->arg('$allowedOrigins', '%storybook.cors_allowed_origins%')
        ->tag('kernel.event_listener', ['event' => 'kernel.response']);

    $services
        ->set(StaticFormDataListener::class)
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'priority' => 2048]);
};
