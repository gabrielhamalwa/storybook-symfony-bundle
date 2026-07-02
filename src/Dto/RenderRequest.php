<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Dto;

final readonly class RenderRequest
{
    public function __construct(
        public string $id,
        public ?string $componentId = null,
        public ?string $adapter = null,
        public ?string $template = null,
        public ?string $controller = null,
        public array $args = [],
        public array $globals = [],
    ) {
    }
}
