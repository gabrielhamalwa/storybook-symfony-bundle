<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Component;

use Storybook\SymfonyBundle\Dto\RenderRequest;
use Symfony\UX\TwigComponent\ComponentRendererInterface;

final readonly class TwigComponentAdapter implements ComponentAdapterInterface
{
    public function __construct(private ComponentRendererInterface $componentRenderer)
    {
    }

    public function render(RenderRequest $request): string
    {
        $componentId = $request->componentId;

        if (!\is_string($componentId) || '' === $componentId) {
            throw new \InvalidArgumentException('Missing component ID for Twig component adapter.');
        }

        return $this->componentRenderer->createAndRender($componentId, $request->args);
    }
}
