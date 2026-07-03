<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Indexer;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

final class ComponentIndexer implements ComponentIndexerInterface
{
    private const string DEFAULT_TEMPLATE_DIR = 'templates/components';

    private const string DEFAULT_COMPONENT_DIR = 'src/Twig/Components';

    private const string DEFAULT_TITLE_PREFIX = 'Components';

    private readonly string $liveComponentAttribute;

    /**
     * @param array<int, string> $componentPaths
     */
    public function __construct(
        private readonly string $projectDir,
        private readonly array $componentPaths = [self::DEFAULT_COMPONENT_DIR],
        private readonly string $templateDir = self::DEFAULT_TEMPLATE_DIR,
        private readonly string $titlePrefix = self::DEFAULT_TITLE_PREFIX,
    ) {
        $this->liveComponentAttribute = 'Symfony\\UX\\LiveComponent\\Attribute\\AsLiveComponent';
    }

    public function index(): array
    {
        $components = [];

        foreach ($this->componentPaths as $path) {
            $components = array_merge($components, $this->scanDirectory($path));
        }

        usort($components, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);

        return $components;
    }

    public function findComponent(string $id): ?array
    {
        foreach ($this->index() as $component) {
            if ($component['id'] === $id) {
                return $component;
            }
        }

        return null;
    }

    public function getComponentSource(string $id): array
    {
        $component = $this->findComponent($id);

        if (null === $component) {
            return ['template' => null, 'class' => null];
        }

        $templatePath = $this->resolveAbsoluteTemplatePath($component['template'] ?? null);
        $classPath = $this->resolveClassPath($component['class'] ?? null);

        return [
            'template' => $templatePath && is_file($templatePath) ? file_get_contents($templatePath) : null,
            'class' => $classPath && is_file($classPath) ? file_get_contents($classPath) : null,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function scanDirectory(string $relativePath): array
    {
        $absolutePath = $this->resolveProjectPath($relativePath);

        if (!is_dir($absolutePath)) {
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($absolutePath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $components = [];

        foreach ($iterator as $file) {
            if (!$file->isFile() || !$file->isReadable() || 'php' !== $file->getExtension()) {
                continue;
            }

            $metadata = $this->extractComponentMetadata($file->getRealPath());

            if (null !== $metadata) {
                $components[] = $metadata;
            }
        }

        return $components;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function extractComponentMetadata(string $filePath): ?array
    {
        $className = $this->extractClassName($filePath);

        if (null === $className || !class_exists($className)) {
            return null;
        }

        $reflection = new \ReflectionClass($className);
        $twigAttribute = $this->findAttribute($reflection, AsTwigComponent::class);

        if (null === $twigAttribute) {
            $isLive = false;
            $twigAttribute = $this->findAttribute($reflection, $this->liveComponentAttribute);

            if (null === $twigAttribute) {
                return null;
            }

            $isLive = true;
        } else {
            $isLive = $this->findAttribute($reflection, $this->liveComponentAttribute) !== null;
        }

        $config = $twigAttribute->serviceConfig();
        $componentName = $config['key'] ?? $this->deriveComponentNameFromClass($reflection);
        $templatePath = $config['template'] ?? $this->deriveDefaultTemplatePath($componentName);
        $props = $this->extractProps($reflection);
        $props = $this->mergeTwigProps($props, $templatePath);

        return [
            'id' => $componentName,
            'type' => $isLive ? 'live_component' : 'twig_component',
            'title' => $this->titlePrefix.'/'.$componentName,
            'template' => $templatePath,
            'class' => $className,
            'props' => $props,
        ];
    }

    private function findAttribute(\ReflectionClass $reflection, string $attributeClass): ?object
    {
        if (!class_exists($attributeClass) && !interface_exists($attributeClass)) {
            return null;
        }

        $attributes = $reflection->getAttributes($attributeClass, \ReflectionAttribute::IS_INSTANCEOF);

        if ([] === $attributes) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    private function deriveComponentNameFromClass(\ReflectionClass $reflection): string
    {
        return $reflection->getShortName();
    }

    private function deriveDefaultTemplatePath(string $componentName): string
    {
        $relativeName = str_replace(':', '/', $componentName);

        return $this->templateDir.'/'.$relativeName.'.html.twig';
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function extractProps(\ReflectionClass $reflection): array
    {
        $props = [];
        $constructor = $reflection->getConstructor();

        if (null !== $constructor) {
            foreach ($constructor->getParameters() as $parameter) {
                $props[] = $this->buildPropFromParameter($parameter);
            }
        }

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $propName = $property->getName();
            $existingIndex = $this->findPropIndex($props, $propName);

            if (null === $existingIndex) {
                $props[] = $this->buildPropFromProperty($property);
                continue;
            }

            $props[$existingIndex] = $this->mergePropWithProperty($props[$existingIndex], $property);
        }

        return $props;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPropFromParameter(\ReflectionParameter $parameter): array
    {
        $prop = [
            'name' => $parameter->getName(),
            'type' => $this->formatType($parameter->getType()),
            'required' => !$parameter->isOptional(),
        ];

        if ($parameter->isDefaultValueAvailable()) {
            $prop['default'] = $this->serializeDefaultValue($parameter->getDefaultValue());
        }

        return $prop;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPropFromProperty(\ReflectionProperty $property): array
    {
        $prop = [
            'name' => $property->getName(),
            'type' => $this->formatType($property->getType()),
            'required' => true,
        ];

        if ($property->hasDefaultValue()) {
            try {
                $prop['default'] = $this->serializeDefaultValue($property->getDefaultValue());
                $prop['required'] = false;
            } catch (\ReflectionException) {
            }
        }

        return $prop;
    }

    /**
     * @param array<string, mixed> $prop
     *
     * @return array<string, mixed>
     */
    private function mergePropWithProperty(array $prop, \ReflectionProperty $property): array
    {
        if ('mixed' === $prop['type'] || null === $prop['type']) {
            $prop['type'] = $this->formatType($property->getType());
        }

        if ($property->hasDefaultValue()) {
            try {
                $prop['default'] = $this->serializeDefaultValue($property->getDefaultValue());
                $prop['required'] = false;
            } catch (\ReflectionException) {
            }
        }

        return $prop;
    }

    /**
     * @param list<array<string, mixed>> $phpProps
     *
     * @return list<array<string, mixed>>
     */
    private function mergeTwigProps(array $phpProps, string $templatePath): array
    {
        $absoluteTemplatePath = $this->resolveAbsoluteTemplatePath($templatePath);

        if (null === $absoluteTemplatePath) {
            return $phpProps;
        }

        $templateSource = file_get_contents($absoluteTemplatePath);

        if (false === $templateSource) {
            return $phpProps;
        }

        $twigProps = TwigPropsParser::parse($templateSource);

        if ([] === $twigProps) {
            return $phpProps;
        }

        foreach ($twigProps as $twigProp) {
            $index = $this->findPropIndex($phpProps, $twigProp['name']);

            if (null === $index) {
                $phpProps[] = $twigProp;
            } else {
                $phpProps[$index] = $twigProp;
            }
        }

        return $phpProps;
    }

    /**
     * @param list<array<string, mixed>> $props
     */
    private function findPropIndex(array $props, string $name): ?int
    {
        foreach ($props as $index => $prop) {
            if ($prop['name'] === $name) {
                return $index;
            }
        }

        return null;
    }

    private function formatType(?\ReflectionType $type): ?string
    {
        if (null === $type) {
            return 'mixed';
        }

        if ($type instanceof \ReflectionNamedType) {
            $name = $type->getName();

            return $type->isBuiltin() ? $name : 'object';
        }

        if ($type instanceof \ReflectionUnionType || $type instanceof \ReflectionIntersectionType) {
            return 'mixed';
        }

        return 'mixed';
    }

    private function serializeDefaultValue(mixed $value): mixed
    {
        if (null === $value || \is_scalar($value)) {
            return $value;
        }

        if (\is_array($value)) {
            return $value;
        }

        return 'object';
    }

    private function extractClassName(string $filePath): ?string
    {
        $contents = file_get_contents($filePath);

        if (false === $contents) {
            return null;
        }

        $namespace = '';
        $class = '';

        if (preg_match('/namespace\s+([^;]+);\s*/', $contents, $matches)) {
            $namespace = $matches[1];
        }

        if (preg_match('/class\s+(\w+)/', $contents, $matches)) {
            $class = $matches[1];
        }

        if ('' === $class) {
            return null;
        }

        return '' === $namespace ? $class : $namespace.'\\'.$class;
    }

    private function resolveClassPath(?string $className): ?string
    {
        if (null === $className || !class_exists($className)) {
            return null;
        }

        $reflection = new \ReflectionClass($className);

        return $reflection->getFileName() ?: null;
    }

    private function resolveAbsoluteTemplatePath(?string $templatePath): ?string
    {
        if (null === $templatePath) {
            return null;
        }

        if (is_file($templatePath)) {
            return $templatePath;
        }

        $absolutePath = $this->resolveProjectPath($templatePath);

        return is_file($absolutePath) ? $absolutePath : null;
    }

    private function resolveProjectPath(string $relativePath): string
    {
        return $this->projectDir.'/'.$relativePath;
    }
}
