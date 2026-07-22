<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Asset;

use Storybook\SymfonyBundle\Dto\AssetCollection;

final class NullAssetPipeline implements AssetPipelineInterface, AssetExtractorInterface
{
    public function __construct(
        string $entrypoint = 'app',
    ) {
    }

    public function getAssets(): array
    {
        return [
            'pipeline' => 'none',
            'styles' => [],
            'scripts' => [],
        ];
    }

    public function extract(): AssetCollection
    {
        return new AssetCollection('none');
    }
}
