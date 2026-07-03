<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Fixtures\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Card')]
final class Card
{
    public string $title = 'Card';

    public string $theme = 'light';
}
