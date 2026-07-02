<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Dto;

final readonly class AssetScript
{
    /**
     * @param array<string, string|bool|null> $attributes
     */
    public function __construct(
        public string $src,
        public string $type = 'module',
        public ?string $pipeline = null,
        public bool $async = false,
        public bool $defer = true,
        public array $attributes = [],
    ) {
    }
}
