<?php

declare(strict_types=1);
use Storybook\SymfonyBundle\Indexer\TwigPropsParser;
test('parse returns empty array when no props block', function () {
    self::assertSame([], TwigPropsParser::parse('<div></div>'));
});
test('parse string defaults', function () {
    $props = TwigPropsParser::parse("{% props name = 'default', type = 'info' %}");

    self::assertCount(2, $props);

    $name = findTwigProp($props, 'name');
    self::assertNotNull($name);
    self::assertSame('string', $name['type']);
    self::assertFalse($name['required']);
    self::assertSame('default', $name['default']);

    $type = findTwigProp($props, 'type');
    self::assertNotNull($type);
    self::assertSame('string', $type['type']);
    self::assertFalse($type['required']);
    self::assertSame('info', $type['default']);
});
test('parse required prop', function () {
    $props = TwigPropsParser::parse("{% props message %}");

    self::assertCount(1, $props);

    $message = findTwigProp($props, 'message');
    self::assertNotNull($message);
    self::assertSame('string', $message['type']);
    self::assertTrue($message['required']);
    self::assertNull($message['default']);
});
test('parse numeric defaults', function () {
    $props = TwigPropsParser::parse("{% props count = 5, ratio = 1.5 %}");

    self::assertCount(2, $props);

    $count = findTwigProp($props, 'count');
    self::assertNotNull($count);
    self::assertSame('integer', $count['type']);
    self::assertFalse($count['required']);
    self::assertSame(5, $count['default']);

    $ratio = findTwigProp($props, 'ratio');
    self::assertNotNull($ratio);
    self::assertSame('float', $ratio['type']);
    self::assertFalse($ratio['required']);
    self::assertSame(1.5, $ratio['default']);
});
test('parse boolean defaults', function () {
    $props = TwigPropsParser::parse("{% props active = true, disabled = false %}");

    self::assertCount(2, $props);

    $active = findTwigProp($props, 'active');
    self::assertNotNull($active);
    self::assertSame('boolean', $active['type']);
    self::assertFalse($active['required']);
    self::assertTrue($active['default']);

    $disabled = findTwigProp($props, 'disabled');
    self::assertNotNull($disabled);
    self::assertSame('boolean', $disabled['type']);
    self::assertFalse($disabled['required']);
    self::assertFalse($disabled['default']);
});
test('parse array defaults', function () {
    $props = TwigPropsParser::parse("{% props items = [], tags = ['a', 'b'] %}");

    self::assertCount(2, $props);

    $items = findTwigProp($props, 'items');
    self::assertNotNull($items);
    self::assertSame('array', $items['type']);
    self::assertFalse($items['required']);
    self::assertSame([], $items['default']);

    $tags = findTwigProp($props, 'tags');
    self::assertNotNull($tags);
    self::assertSame('array', $tags['type']);
    self::assertFalse($tags['required']);
    self::assertSame(['a', 'b'], $tags['default']);
});
test('parse null default', function () {
    $props = TwigPropsParser::parse("{% props description = null %}");

    self::assertCount(1, $props);

    $description = findTwigProp($props, 'description');
    self::assertNotNull($description);
    self::assertSame('string', $description['type']);
    self::assertFalse($description['required']);
    self::assertNull($description['default']);
});
test('parse block with newlines', function () {
    $props = TwigPropsParser::parse("{% props %}\n    name = 'default'\n    message\n{% endprops %}");

    self::assertCount(2, $props);

    $name = findTwigProp($props, 'name');
    self::assertNotNull($name);
    self::assertFalse($name['required']);
    self::assertSame('default', $name['default']);

    $message = findTwigProp($props, 'message');
    self::assertNotNull($message);
    self::assertTrue($message['required']);
});
test('parse inline props with braces', function () {
    $props = TwigPropsParser::parse("{% props { title: 'Card', theme: 'dark' } %}");

    self::assertCount(2, $props);

    $title = findTwigProp($props, 'title');
    self::assertNotNull($title);
    self::assertSame('Card', $title['default']);

    $theme = findTwigProp($props, 'theme');
    self::assertNotNull($theme);
    self::assertSame('dark', $theme['default']);
});
test('parse escapes quotes', function () {
    $props = TwigPropsParser::parse('{% props label = \'It\'s working\' %}');

    self::assertCount(1, $props);

    $label = findTwigProp($props, 'label');
    self::assertNotNull($label);
    self::assertSame("It's working", $label['default']);
});
/**
 * @param list<array<string, mixed>> $props
 *
 * @return array<string, mixed>|null
 */
function findTwigProp(array $props, string $name): ?array
{
    foreach ($props as $prop) {
        if ($prop['name'] === $name) {
            return $prop;
        }
    }

    return null;
}
