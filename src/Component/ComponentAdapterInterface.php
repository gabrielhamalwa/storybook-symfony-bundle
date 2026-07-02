<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Component;

use Storybook\SymfonyBundle\Dto\RenderRequest;

interface ComponentAdapterInterface
{
    public function render(RenderRequest $request): string;
}
