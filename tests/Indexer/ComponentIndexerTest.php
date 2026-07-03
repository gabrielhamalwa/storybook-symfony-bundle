<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\Indexer;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\Indexer\ComponentIndexer;

final class ComponentIndexerTest extends TestCase
{
    private function createIndexer(): ComponentIndexer
    {
        return new ComponentIndexer(
            projectDir: __DIR__.'/../Fixtures',
            componentPaths: ['Twig/Components'],
            templateDir: 'templates/components',
            titlePrefix: 'Components',
        );
    }

    public function testIndexDiscoversTwigComponents(): void
    {
        $indexer = $this->createIndexer();
        $components = $indexer->index();

        self::assertCount(5, $components);

        $ids = array_map(static fn (array $component): string => $component['id'], $components);
        self::assertContains('Alert', $ids);
        self::assertContains('Button', $ids);
        self::assertContains('Card', $ids);
        self::assertContains('Message', $ids);
        self::assertContains('LiveCounter', $ids);
    }

    public function testIndexExtractsPropsFromPublicProperties(): void
    {
        $indexer = $this->createIndexer();
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

        $label = $this->findProp($props, 'label');
        self::assertNotNull($label);
        self::assertSame('string', $label['type']);
        self::assertFalse($label['required']);
        self::assertSame('Button', $label['default']);

        $variant = $this->findProp($props, 'variant');
        self::assertNotNull($variant);
        self::assertSame('string', $variant['type']);
        self::assertFalse($variant['required']);
        self::assertSame('primary', $variant['default']);
    }

    public function testIndexExtractsPropsFromConstructorParameters(): void
    {
        $indexer = $this->createIndexer();
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

        $message = $this->findProp($props, 'message');
        self::assertNotNull($message);
        self::assertSame('string', $message['type']);
        self::assertFalse($message['required']);
        self::assertSame('Alert', $message['default']);

        $type = $this->findProp($props, 'type');
        self::assertNotNull($type);
        self::assertSame('string', $type['type']);
        self::assertFalse($type['required']);
        self::assertSame('info', $type['default']);
    }

    public function testIndexExtractsPropsFromTwigBlock(): void
    {
        $indexer = $this->createIndexer();
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

        $title = $this->findProp($props, 'title');
        self::assertNotNull($title);
        self::assertSame('string', $title['type']);
        self::assertFalse($title['required']);
        self::assertSame('Card', $title['default']);

        $theme = $this->findProp($props, 'theme');
        self::assertNotNull($theme);
        self::assertSame('string', $theme['type']);
        self::assertFalse($theme['required']);
        self::assertSame('dark', $theme['default']);

        $items = $this->findProp($props, 'items');
        self::assertNotNull($items);
        self::assertSame('array', $items['type']);
        self::assertFalse($items['required']);
        self::assertSame([], $items['default']);
    }

    public function testIndexMergesTwigPropsOverPhpProps(): void
    {
        $indexer = $this->createIndexer();
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

        $content = $this->findProp($props, 'message');
        self::assertNotNull($content);
        self::assertSame('string', $content['type']);
        self::assertTrue($content['required']);
        self::assertNull($content['default']);

        $type = $this->findProp($props, 'type');
        self::assertNotNull($type);
        self::assertSame('string', $type['type']);
        self::assertFalse($type['required']);
        self::assertSame('warning', $type['default']);
    }

    public function testFindComponentReturnsMatchingComponent(): void
    {
        $indexer = $this->createIndexer();
        $component = $indexer->findComponent('Button');

        self::assertNotNull($component);
        self::assertSame('Button', $component['id']);
    }

    public function testFindComponentReturnsNullForUnknownComponent(): void
    {
        $indexer = $this->createIndexer();

        self::assertNull($indexer->findComponent('Unknown'));
    }

    public function testGetComponentSourceReturnsTemplateAndClassSource(): void
    {
        $indexer = $this->createIndexer();
        $source = $indexer->getComponentSource('Button');

        self::assertNotNull($source['template']);
        self::assertStringContainsString('btn-{{ variant }}', $source['template']);

        self::assertNotNull($source['class']);
        self::assertStringContainsString('AsTwigComponent', $source['class']);
    }

    public function testGetComponentSourceReturnsNullForUnknownComponent(): void
    {
        $indexer = $this->createIndexer();
        $source = $indexer->getComponentSource('Unknown');

        self::assertNull($source['template']);
        self::assertNull($source['class']);
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
