<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

final readonly class FragmentHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has('fragment.handler') && !$container->has(FragmentHandler::class)) {
            $container->setAlias(FragmentHandler::class, 'fragment.handler');
        }
    }
}
