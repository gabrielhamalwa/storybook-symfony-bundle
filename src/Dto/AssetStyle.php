<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Dto;

final readonly class AssetStyle
{
    /**
     * @param array<string, string|bool|null> $attributes
     */
    public function __construct(
        public string $href,
        public ?string $pipeline = null,
        public array $attributes = [],
    ) {
    }
}
