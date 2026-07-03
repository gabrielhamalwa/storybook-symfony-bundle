<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Dto;

final readonly class RenderResponse
{
    /**
     * @param string $html
     * @param array<string, mixed> $assets
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $html,
        public array $assets,
        public array $metadata,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'html' => $this->html,
            'assets' => $this->assets,
            'metadata' => $this->metadata,
        ];
    }
}
