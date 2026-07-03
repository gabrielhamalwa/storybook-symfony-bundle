<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Fixtures\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('LiveCounter')]
final class LiveCounter
{
    use DefaultActionTrait;

    public int $count = 0;
}
