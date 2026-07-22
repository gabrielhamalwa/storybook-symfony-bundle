<?php

declare(strict_types=1);
use Storybook\SymfonyBundle\DependencyInjection\Compiler\FragmentHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;
test('aliases fragment handler class when service exists', function () {
    $container = new ContainerBuilder();
    $container->register('fragment.handler', FragmentHandler::class);

    (new FragmentHandlerPass())->process($container);

    self::assertTrue($container->hasAlias(FragmentHandler::class));
    self::assertSame('fragment.handler', (string) $container->getAlias(FragmentHandler::class));
});
test('does not alias when service is missing', function () {
    $container = new ContainerBuilder();

    (new FragmentHandlerPass())->process($container);

    self::assertFalse($container->hasAlias(FragmentHandler::class));
});
