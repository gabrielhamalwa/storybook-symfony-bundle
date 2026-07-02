<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Asset;

final class NullAssetPipeline implements AssetPipelineInterface
{
    public function getAssets(): array
    {
        return [
            'styles' => [],
            'scripts' => [],
        ];
    }
}
