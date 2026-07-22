<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Component;

use Storybook\SymfonyBundle\Dto\RenderRequest;
use Twig\Environment;

final readonly class LiveComponentAdapter implements ComponentAdapterInterface
{
    public function __construct(private Environment $twig)
    {
    }

    public function render(RenderRequest $request): string
    {
        if (!\class_exists('Symfony\\UX\\LiveComponent\\LiveComponentBundle')) {
            throw new \RuntimeException('Symfony UX Live Component is not installed.');
        }

        $componentId = $request->componentId;

        if (!\is_string($componentId) || '' === $componentId) {
            throw new \InvalidArgumentException('Missing component ID for live component adapter.');
        }

        return $this->twig
            ->createTemplate('{{ component(componentId, args) }}', '__storybook_live_component__')
            ->render([
                'componentId' => $componentId,
                'args' => $request->args,
            ]);
    }
}
