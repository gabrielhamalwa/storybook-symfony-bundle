<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Fixtures\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Alert')]
final class Alert
{
    public function __construct(
        public string $message = 'Alert',
        public string $type = 'info',
    ) {
    }
}
