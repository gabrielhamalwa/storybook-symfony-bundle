<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Dto;

final readonly class ComponentMetadata
{
    /**
     * @param string $id
     * @param string $type
     * @param string $title
     * @param string $template
     * @param string $class
     * @param list<array<string, mixed>> $props
     * @param list<string> $tags
     */
    public function __construct(
        public string $id,
        public string $type,
        public string $title,
        public string $template,
        public string $class,
        public array $props = [],
        public array $tags = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'template' => $this->template,
            'class' => $this->class,
            'props' => $this->props,
            'tags' => $this->tags,
        ];
    }
}
