<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Asset;

use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;

final readonly class PentatrionViteAssetPipeline implements AssetPipelineInterface
{
    public function __construct(
        private EntrypointsLookupCollection $entrypointsLookupCollection,
        private string $entrypoint = 'app',
    ) {
    }

    public function getAssets(): array
    {
        $lookup = $this->entrypointsLookupCollection->getEntrypointsLookup();

        if (!$lookup->hasFile()) {
            return [
                'styles' => [],
                'scripts' => [],
            ];
        }

        $base = $lookup->getBase();

        $styles = [];
        foreach ($lookup->getCSSFiles($this->entrypoint) as $cssFile) {
            $styles[] = ['url' => $base.$cssFile];
        }

        $scripts = [];
        foreach ($lookup->getJSFiles($this->entrypoint) as $jsFile) {
            $scripts[] = ['url' => $base.$jsFile, 'type' => 'module'];
        }

        return [
            'styles' => $styles,
            'scripts' => $scripts,
        ];
    }
}
