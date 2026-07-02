<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\DependencyInjection\Compiler\FragmentHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

final class FragmentHandlerPassTest extends TestCase
{
    public function testAliasesFragmentHandlerClassWhenServiceExists(): void
    {
        $container = new ContainerBuilder();
        $container->register('fragment.handler', FragmentHandler::class);

        (new FragmentHandlerPass())->process($container);

        self::assertTrue($container->hasAlias(FragmentHandler::class));
        self::assertSame('fragment.handler', (string) $container->getAlias(FragmentHandler::class));
    }

    public function testDoesNotAliasWhenServiceIsMissing(): void
    {
        $container = new ContainerBuilder();

        (new FragmentHandlerPass())->process($container);

        self::assertFalse($container->hasAlias(FragmentHandler::class));
    }
}
