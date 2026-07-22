<?php

declare(strict_types=1);
use Storybook\SymfonyBundle\Indexer\ComponentIndexer;
function createIndexer(): ComponentIndexer
{
    return new ComponentIndexer(
        projectDir: __DIR__.'/../Fixtures',
        componentPaths: ['Twig/Components'],
        templateDir: 'templates/components',
        titlePrefix: 'Components',
    );
}
test('index discovers twig components', function () {
    $indexer = createIndexer();
    $components = $indexer->index();

    self::assertCount(5, $components);

    $ids = array_map(static fn (array $component): string => $component['id'], $components);
    self::assertContains('Alert', $ids);
    self::assertContains('Button', $ids);
    self::assertContains('Card', $ids);
    self::assertContains('Message', $ids);
    self::assertContains('LiveCounter', $ids);
});
test('index extracts props from public properties', function () {
    $indexer = createIndexer();
    $components = $indexer->index();

    $button = null;
    foreach ($components as $component) {
        if ('Button' === $component['id']) {
            $button = $component;

            break;
        }
    }

    self::assertNotNull($button);
    self::assertSame('twig_component', $button['type']);
    self::assertSame('Components/Button', $button['title']);
    self::assertSame('Storybook\\SymfonyBundle\\Tests\\Fixtures\\Twig\\Components\\Button', $button['class']);
    self::assertSame('templates/components/Button.html.twig', $button['template']);

    $props = $button['props'];
    self::assertCount(2, $props);

    $label = findComponentProp($props, 'label');
    self::assertNotNull($label);
    self::assertSame('string', $label['type']);
    self::assertFalse($label['required']);
    self::assertSame('Button', $label['default']);

    $variant = findComponentProp($props, 'variant');
    self::assertNotNull($variant);
    self::assertSame('string', $variant['type']);
    self::assertFalse($variant['required']);
    self::assertSame('primary', $variant['default']);
});
test('index extracts props from constructor parameters', function () {
    $indexer = createIndexer();
    $components = $indexer->index();

    $alert = null;
    foreach ($components as $component) {
        if ('Alert' === $component['id']) {
            $alert = $component;

            break;
        }
    }

    self::assertNotNull($alert);
    self::assertSame('twig_component', $alert['type']);
    self::assertSame('Components/Alert', $alert['title']);

    $props = $alert['props'];
    self::assertCount(2, $props);

    $message = findComponentProp($props, 'message');
    self::assertNotNull($message);
    self::assertSame('string', $message['type']);
    self::assertFalse($message['required']);
    self::assertSame('Alert', $message['default']);

    $type = findComponentProp($props, 'type');
    self::assertNotNull($type);
    self::assertSame('string', $type['type']);
    self::assertFalse($type['required']);
    self::assertSame('info', $type['default']);
});
test('index extracts props from twig block', function () {
    $indexer = createIndexer();
    $components = $indexer->index();

    $card = null;
    foreach ($components as $component) {
        if ('Card' === $component['id']) {
            $card = $component;

            break;
        }
    }

    self::assertNotNull($card);
    self::assertSame('templates/components/Card.html.twig', $card['template']);

    $props = $card['props'];
    self::assertCount(3, $props);

    $title = findComponentProp($props, 'title');
    self::assertNotNull($title);
    self::assertSame('string', $title['type']);
    self::assertFalse($title['required']);
    self::assertSame('Card', $title['default']);

    $theme = findComponentProp($props, 'theme');
    self::assertNotNull($theme);
    self::assertSame('string', $theme['type']);
    self::assertFalse($theme['required']);
    self::assertSame('dark', $theme['default']);

    $items = findComponentProp($props, 'items');
    self::assertNotNull($items);
    self::assertSame('array', $items['type']);
    self::assertFalse($items['required']);
    self::assertSame([], $items['default']);
});
test('index merges twig props over php props', function () {
    $indexer = createIndexer();
    $components = $indexer->index();

    $message = null;
    foreach ($components as $component) {
        if ('Message' === $component['id']) {
            $message = $component;

            break;
        }
    }

    self::assertNotNull($message);

    $props = $message['props'];
    self::assertCount(2, $props);

    $content = findComponentProp($props, 'message');
    self::assertNotNull($content);
    self::assertSame('string', $content['type']);
    self::assertTrue($content['required']);
    self::assertNull($content['default']);

    $type = findComponentProp($props, 'type');
    self::assertNotNull($type);
    self::assertSame('string', $type['type']);
    self::assertFalse($type['required']);
    self::assertSame('warning', $type['default']);
});
test('find component returns matching component', function () {
    $indexer = createIndexer();
    $component = $indexer->findComponent('Button');

    self::assertNotNull($component);
    self::assertSame('Button', $component->id);
});
test('find component returns null for unknown component', function () {
    $indexer = createIndexer();

    self::assertNull($indexer->findComponent('Unknown'));
});
test('get component source returns template and class source', function () {
    $indexer = createIndexer();
    $source = $indexer->getComponentSource('Button');

    self::assertNotNull($source['template']);
    self::assertStringContainsString('btn-{{ variant }}', $source['template']);

    self::assertNotNull($source['class']);
    self::assertStringContainsString('AsTwigComponent', $source['class']);
});
test('get component source returns null for unknown component', function () {
    $indexer = createIndexer();
    $source = $indexer->getComponentSource('Unknown');

    self::assertNull($source['template']);
    self::assertNull($source['class']);
});
/**
 * @param list<array<string, mixed>> $props
 *
 * @return array<string, mixed>|null
 */
function findComponentProp(array $props, string $name): ?array
{
    foreach ($props as $prop) {
        if ($prop['name'] === $name) {
            return $prop;
        }
    }

    return null;
}
