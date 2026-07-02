<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Indexer;

interface ComponentIndexerInterface
{
    /**
     * Discover all Twig components in the project.
     *
     * @return list<array<string, mixed>>
     */
    public function index(): array;

    /**
     * Find a component by its id (component name).
     *
     * @return array<string, mixed>|null
     */
    public function findComponent(string $id): ?array;

    /**
     * Return the Twig template and PHP class source for a component id.
     *
     * @return array{template: string|null, class: string|null}
     */
    public function getComponentSource(string $id): array;
}
