<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class StorybookBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
