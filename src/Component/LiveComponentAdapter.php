<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Component;

use Storybook\SymfonyBundle\Dto\RenderRequest;
use Symfony\UX\TwigComponent\ComponentRendererInterface;

final readonly class LiveComponentAdapter implements ComponentAdapterInterface
{
    public function __construct(private ComponentRendererInterface $componentRenderer)
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

        return $this->componentRenderer->createAndRender($componentId, $request->args);
    }
}
