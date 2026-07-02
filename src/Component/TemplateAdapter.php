<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Component;

use Storybook\SymfonyBundle\Dto\RenderRequest;
use Twig\Environment;

final readonly class TemplateAdapter implements ComponentAdapterInterface
{
    public function __construct(private Environment $twig)
    {
    }

    public function render(RenderRequest $request): string
    {
        $template = $request->template ?? $request->componentId;

        if (!\is_string($template) || '' === $template) {
            throw new \InvalidArgumentException('Missing template path.');
        }

        if (str_starts_with($template, 'templates/')) {
            $template = substr($template, \strlen('templates/'));
        }

        return $this->twig->render($template, $request->args);
    }
}
