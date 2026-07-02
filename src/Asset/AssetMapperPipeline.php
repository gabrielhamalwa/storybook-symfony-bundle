<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Asset;

use Storybook\SymfonyBundle\Dto\AssetCollection;
use Storybook\SymfonyBundle\Dto\AssetScript;
use Storybook\SymfonyBundle\Dto\AssetStyle;
use Symfony\Component\AssetMapper\ImportMap\ImportMapGenerator;

final readonly class AssetMapperPipeline implements AssetPipelineInterface, AssetExtractorInterface
{
    public function __construct(
        private ImportMapGenerator $importMapGenerator,
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
        try {
            $rawImportMap = $this->importMapGenerator->getRawImportMapData();
            $eagerImports = $this->importMapGenerator->findEagerEntrypointImports($this->entrypoint);
        } catch (\Throwable) {
            return new AssetCollection('asset-mapper');
        }

        $importmap = ['imports' => []];
        $styles = [];
        $scripts = [];

        foreach ($rawImportMap as $importName => $data) {
            $path = $data['path'] ?? null;
            $type = $data['type'] ?? 'js';

            if (!is_string($path)) {
                continue;
            }

            $importmap['imports'][$importName] = $path;

            if (!in_array($importName, $eagerImports, true)) {
                continue;
            }

            if ('css' === $type) {
                $styles[] = new AssetStyle($path, 'asset-mapper');
            } else {
                $scripts[] = new AssetScript($path, 'module', 'asset-mapper');
            }
        }

        return new AssetCollection('asset-mapper', $scripts, $styles, $importmap);
    }
}
