<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Component;

use Storybook\SymfonyBundle\Dto\RenderRequest;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Fragment\FragmentHandler;

final readonly class ControllerFragmentAdapter implements ComponentAdapterInterface
{
    public function __construct(private FragmentHandler $fragmentHandler)
    {
    }

    public function render(RenderRequest $request): string
    {
        $controller = $request->controller ?? $request->componentId;

        if (!\is_string($controller) || '' === $controller) {
            throw new \InvalidArgumentException('Missing controller reference.');
        }

        $reference = new ControllerReference($controller, [], $request->args);

        return $this->fragmentHandler->render($reference, 'inline', ['ignore_errors' => false]) ?? '';
    }
}
