<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Fixtures\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Message')]
final class Message
{
    public string $message = 'Hello';

    public string $type = 'info';
}
