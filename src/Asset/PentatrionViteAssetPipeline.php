<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Asset;

use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;
use Storybook\SymfonyBundle\Dto\AssetCollection;
use Storybook\SymfonyBundle\Dto\AssetScript;
use Storybook\SymfonyBundle\Dto\AssetStyle;

final readonly class PentatrionViteAssetPipeline implements AssetPipelineInterface, AssetExtractorInterface
{
    public function __construct(
        private EntrypointsLookupCollection $entrypointsLookupCollection,
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
            'importmap' => $collection->importmap,
        ];
    }

    public function extract(): AssetCollection
    {
        $lookup = $this->entrypointsLookupCollection->getEntrypointsLookup();

        if (!$lookup->hasFile()) {
            return new AssetCollection('pentatrion-vite');
        }

        $base = $lookup->getBase();

        $styles = [];
        foreach ($lookup->getCSSFiles($this->entrypoint) as $cssFile) {
            $styles[] = new AssetStyle($this->resolveAssetUrl($base, $cssFile), 'pentatrion-vite');
        }

        $scripts = [];
        foreach ($lookup->getJSFiles($this->entrypoint) as $jsFile) {
            $scripts[] = new AssetScript($this->resolveAssetUrl($base, $jsFile), 'module', 'pentatrion-vite');
        }

        return new AssetCollection('pentatrion-vite', $scripts, $styles);
    }

    private function resolveAssetUrl(string $base, string $file): string
    {
        if (str_starts_with($file, 'http://') || str_starts_with($file, 'https://')) {
            return $file;
        }

        return $base.$file;
    }
}
