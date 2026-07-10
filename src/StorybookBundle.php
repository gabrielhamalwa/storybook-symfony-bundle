<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle;

use Storybook\SymfonyBundle\DependencyInjection\Compiler\AssetPipelinePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class StorybookBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AssetPipelinePass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
