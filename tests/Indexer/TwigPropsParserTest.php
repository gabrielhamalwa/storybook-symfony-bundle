<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Indexer;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\Indexer\TwigPropsParser;

final class TwigPropsParserTest extends TestCase
{
    public function testParseReturnsEmptyArrayWhenNoPropsBlock(): void
    {
        self::assertSame([], TwigPropsParser::parse('<div></div>'));
    }

    public function testParseStringDefaults(): void
    {
        $props = TwigPropsParser::parse("{% props name = 'default', type = 'info' %}");

        self::assertCount(2, $props);

        $name = $this->findProp($props, 'name');
        self::assertNotNull($name);
        self::assertSame('string', $name['type']);
        self::assertFalse($name['required']);
        self::assertSame('default', $name['default']);

        $type = $this->findProp($props, 'type');
        self::assertNotNull($type);
        self::assertSame('string', $type['type']);
        self::assertFalse($type['required']);
        self::assertSame('info', $type['default']);
    }

    public function testParseRequiredProp(): void
    {
        $props = TwigPropsParser::parse("{% props message %}");

        self::assertCount(1, $props);

        $message = $this->findProp($props, 'message');
        self::assertNotNull($message);
        self::assertSame('string', $message['type']);
        self::assertTrue($message['required']);
        self::assertNull($message['default']);
    }

    public function testParseNumericDefaults(): void
    {
        $props = TwigPropsParser::parse("{% props count = 5, ratio = 1.5 %}");

        self::assertCount(2, $props);

        $count = $this->findProp($props, 'count');
        self::assertNotNull($count);
        self::assertSame('integer', $count['type']);
        self::assertFalse($count['required']);
        self::assertSame(5, $count['default']);

        $ratio = $this->findProp($props, 'ratio');
        self::assertNotNull($ratio);
        self::assertSame('float', $ratio['type']);
        self::assertFalse($ratio['required']);
        self::assertSame(1.5, $ratio['default']);
    }

    public function testParseBooleanDefaults(): void
    {
        $props = TwigPropsParser::parse("{% props active = true, disabled = false %}");

        self::assertCount(2, $props);

        $active = $this->findProp($props, 'active');
        self::assertNotNull($active);
        self::assertSame('boolean', $active['type']);
        self::assertFalse($active['required']);
        self::assertTrue($active['default']);

        $disabled = $this->findProp($props, 'disabled');
        self::assertNotNull($disabled);
        self::assertSame('boolean', $disabled['type']);
        self::assertFalse($disabled['required']);
        self::assertFalse($disabled['default']);
    }

    public function testParseArrayDefaults(): void
    {
        $props = TwigPropsParser::parse("{% props items = [], tags = ['a', 'b'] %}");

        self::assertCount(2, $props);

        $items = $this->findProp($props, 'items');
        self::assertNotNull($items);
        self::assertSame('array', $items['type']);
        self::assertFalse($items['required']);
        self::assertSame([], $items['default']);

        $tags = $this->findProp($props, 'tags');
        self::assertNotNull($tags);
        self::assertSame('array', $tags['type']);
        self::assertFalse($tags['required']);
        self::assertSame(['a', 'b'], $tags['default']);
    }

    public function testParseNullDefault(): void
    {
        $props = TwigPropsParser::parse("{% props description = null %}");

        self::assertCount(1, $props);

        $description = $this->findProp($props, 'description');
        self::assertNotNull($description);
        self::assertSame('string', $description['type']);
        self::assertFalse($description['required']);
        self::assertNull($description['default']);
    }

    public function testParseBlockWithNewlines(): void
    {
        $props = TwigPropsParser::parse("{% props %}\n    name = 'default'\n    message\n{% endprops %}");

        self::assertCount(2, $props);

        $name = $this->findProp($props, 'name');
        self::assertNotNull($name);
        self::assertFalse($name['required']);
        self::assertSame('default', $name['default']);

        $message = $this->findProp($props, 'message');
        self::assertNotNull($message);
        self::assertTrue($message['required']);
    }

    public function testParseInlinePropsWithBraces(): void
    {
        $props = TwigPropsParser::parse("{% props { title: 'Card', theme: 'dark' } %}");

        self::assertCount(2, $props);

        $title = $this->findProp($props, 'title');
        self::assertNotNull($title);
        self::assertSame('Card', $title['default']);

        $theme = $this->findProp($props, 'theme');
        self::assertNotNull($theme);
        self::assertSame('dark', $theme['default']);
    }

    public function testParseEscapesQuotes(): void
    {
        $props = TwigPropsParser::parse('{% props label = \'It\'s working\' %}');

        self::assertCount(1, $props);

        $label = $this->findProp($props, 'label');
        self::assertNotNull($label);
        self::assertSame("It's working", $label['default']);
    }

    /**
     * @param list<array<string, mixed>> $props
     *
     * @return array<string, mixed>|null
     */
    private function findProp(array $props, string $name): ?array
    {
        foreach ($props as $prop) {
            if ($prop['name'] === $name) {
                return $prop;
            }
        }

        return null;
    }
}
