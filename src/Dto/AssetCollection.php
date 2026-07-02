<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Dto;

final readonly class AssetCollection
{
    /**
     * @param list<AssetScript> $scripts
     * @param list<AssetStyle>  $styles
     * @param array{imports?: array<string, string>, scopes?: array<string, array<string, string>>}|null $importmap
     */
    public function __construct(
        public string $pipeline,
        public array $scripts = [],
        public array $styles = [],
        public ?array $importmap = null,
    ) {
    }
}
