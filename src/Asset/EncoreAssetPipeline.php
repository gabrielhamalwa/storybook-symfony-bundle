<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Asset;

use Storybook\SymfonyBundle\Dto\AssetCollection;
use Storybook\SymfonyBundle\Dto\AssetScript;
use Storybook\SymfonyBundle\Dto\AssetStyle;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;

final readonly class EncoreAssetPipeline implements AssetPipelineInterface, AssetExtractorInterface
{
    public function __construct(
        private EntrypointLookupInterface $entrypointLookup,
        private string $entrypoint = 'app',
    ) {
    }

    public function getAssets(): array
    {
        $collection = $this->extract();

        return [
            'pipeline' => $collection->pipeline,
            'styles' => array_map(
                static fn (AssetStyle $style): array => ['url' => $style->href],
                $collection->styles
            ),
            'scripts' => array_map(
                static fn (AssetScript $script): array => ['url' => $script->src, 'type' => $script->type],
                $collection->scripts
            ),
        ];
    }

    public function extract(): AssetCollection
    {
        try {
            $cssFiles = $this->entrypointLookup->getCssFiles($this->entrypoint);
            $jsFiles = $this->entrypointLookup->getJavaScriptFiles($this->entrypoint);
        } catch (\Throwable) {
            return new AssetCollection('webpack-encore');
        }

        $styles = [];
        foreach ($cssFiles as $cssFile) {
            $styles[] = new AssetStyle($cssFile, 'webpack-encore');
        }

        $scripts = [];
        foreach ($jsFiles as $jsFile) {
            $scripts[] = new AssetScript($jsFile, 'module', 'webpack-encore');
        }

        return new AssetCollection('webpack-encore', $scripts, $styles);
    }
}
