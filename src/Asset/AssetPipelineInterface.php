<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Asset;

interface AssetPipelineInterface
{
    /**
     * Return normalized assets for the configured entrypoint.
     *
     * @return array{
     *     styles: list<array{url?: string, content?: string}>,
     *     scripts: list<array{url?: string, content?: string, type?: 'module'|'classic'}>,
     *     importmap?: array{imports?: array<string, string>, scopes?: array<string, array<string, string>>}
     * }
     */
    public function getAssets(): array;
}
