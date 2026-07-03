<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Component;

use Storybook\SymfonyBundle\Dto\RenderRequest;

final readonly class ComponentResolver
{
    public function __construct(
        private TwigComponentAdapter $twigComponentAdapter,
        private TemplateAdapter $templateAdapter,
        private ControllerFragmentAdapter $controllerFragmentAdapter,
        private ?LiveComponentAdapter $liveComponentAdapter = null,
    ) {
    }

    public function resolve(RenderRequest $request): ComponentAdapterInterface
    {
        $adapter = $request->adapter;

        if ('template' === $adapter || (null === $adapter && (null !== $request->template || $this->isTemplatePath($request->componentId)))) {
            return $this->templateAdapter;
        }

        if ('controller' === $adapter || (null === $adapter && (null !== $request->controller || $this->isControllerReference($request->componentId)))) {
            return $this->controllerFragmentAdapter;
        }

        if ('live' === $adapter) {
            if (null === $this->liveComponentAdapter) {
                throw new \RuntimeException('Live component adapter is not available.');
            }

            return $this->liveComponentAdapter;
        }

        return $this->twigComponentAdapter;
    }

    private function isTemplatePath(?string $componentId): bool
    {
        return \is_string($componentId) && str_ends_with($componentId, '.twig');
    }

    private function isControllerReference(?string $componentId): bool
    {
        return \is_string($componentId) && str_contains($componentId, '::');
    }
}
