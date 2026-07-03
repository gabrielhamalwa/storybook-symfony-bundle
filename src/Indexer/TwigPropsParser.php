<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Indexer;

final class TwigPropsParser
{
    /**
     * @return list<array{name: string, type: string, required: bool, default: string|int|float|bool|array|null}>
     */
    public static function parse(string $source): array
    {
        $block = self::extractPropsBlock($source);

        if (null === $block) {
            return [];
        }

        return self::parseDeclarations($block);
    }

    private static function extractPropsBlock(string $source): ?string
    {
        if (preg_match('/\{%\s*props\s*%\}(.*?)\{%\s*endprops\s*%\}/s', $source, $matches)) {
            return $matches[1];
        }

        if (preg_match('/\{%\s*props\s+(.+?)\s*%\}/s', $source, $matches)) {
            $content = trim($matches[1]);

            if (str_starts_with($content, '{') && str_ends_with($content, '}')) {
                $content = substr($content, 1, -1);
            }

            return $content;
        }

        return null;
    }

    /**
     * @return list<array{name: string, type: string, required: bool, default: string|int|float|bool|array|null}>
     */
    private static function parseDeclarations(string $content): array
    {
        $props = [];

        foreach (self::splitDeclarations($content) as $declaration) {
            $declaration = trim($declaration);
            if ('' === $declaration) {
                continue;
            }

            $props[] = self::parseDeclaration($declaration);
        }

        return $props;
    }

    /**
     * @return list<string>
     */
    private static function splitDeclarations(string $content): array
    {
        $tokens = [];
        $current = '';
        $inQuote = false;
        $quoteChar = null;
        $bracketDepth = 0;
        $braceDepth = 0;
        $length = strlen($content);

        for ($i = 0; $i < $length; $i++) {
            $char = $content[$i];

            if ($inQuote) {
                $current .= $char;
                if ($char === $quoteChar && ($i === 0 || $content[$i - 1] !== '\\')) {
                    $inQuote = false;
                    $quoteChar = null;
                }

                continue;
            }

            if ($char === '"' || $char === "'") {
                $inQuote = true;
                $quoteChar = $char;
                $current .= $char;

                continue;
            }

            if ($char === '[') {
                ++$bracketDepth;
                $current .= $char;

                continue;
            }

            if ($char === ']') {
                --$bracketDepth;
                $current .= $char;

                continue;
            }

            if ($char === '{') {
                ++$braceDepth;
                $current .= $char;

                continue;
            }

            if ($char === '}') {
                --$braceDepth;
                $current .= $char;

                continue;
            }

            if ($char === ',' || $char === "\n") {
                if (0 === $bracketDepth && 0 === $braceDepth) {
                    $tokens[] = $current;
                    $current = '';

                    continue;
                }
            }

            if ($char === "\r") {
                continue;
            }

            $current .= $char;
        }

        if ('' !== trim($current)) {
            $tokens[] = $current;
        }

        return $tokens;
    }

    /**
     * @return array{name: string, type: string, required: bool, default: string|int|float|bool|array|null}
     */
    private static function parseDeclaration(string $declaration): array
    {
        $declaration = trim($declaration);
        $separatorPos = self::findDeclarationSeparator($declaration);

        if (null === $separatorPos) {
            $name = $declaration;
            $hasDefault = false;
            $default = null;
        } else {
            $name = trim(substr($declaration, 0, $separatorPos));
            $hasDefault = true;
            $default = self::parseValue(substr($declaration, $separatorPos + 1));
        }

        return [
            'name' => $name,
            'type' => self::inferType($default),
            'required' => !$hasDefault,
            'default' => $default,
        ];
    }

    private static function findDeclarationSeparator(string $declaration): ?int
    {
        $inQuote = false;
        $quoteChar = null;
        $length = strlen($declaration);

        for ($i = 0; $i < $length; $i++) {
            $char = $declaration[$i];

            if ($inQuote) {
                if ($char === $quoteChar && ($i === 0 || $declaration[$i - 1] !== '\\')) {
                    $inQuote = false;
                    $quoteChar = null;
                }

                continue;
            }

            if ($char === '"' || $char === "'") {
                $inQuote = true;
                $quoteChar = $char;

                continue;
            }

            if ($char === '=' || $char === ':') {
                return $i;
            }
        }

        return null;
    }

    private static function parseValue(string $value): string|int|float|bool|array|null
    {
        $value = trim($value);

        if ('null' === $value) {
            return null;
        }

        if ('true' === $value) {
            return true;
        }

        if ('false' === $value) {
            return false;
        }

        if (preg_match('/^([\'"])(.*)\1$/s', $value, $matches)) {
            $quote = $matches[1];
            $string = $matches[2];

            return str_replace('\\'.$quote, $quote, $string);
        }

        if (preg_match('/^\[(.*)\]$/s', $value, $matches)) {
            $inner = trim($matches[1]);
            if ('' === $inner) {
                return [];
            }

            $items = [];
            foreach (self::splitDeclarations($inner) as $item) {
                $items[] = self::parseValue($item);
            }

            return $items;
        }

        if (preg_match('/^-?\d+$/', $value)) {
            return (int) $value;
        }

        if (preg_match('/^-?\d+\.\d+$/', $value)) {
            return (float) $value;
        }

        return $value;
    }

    private static function inferType(mixed $default): string
    {
        if (null === $default) {
            return 'string';
        }

        if (\is_bool($default)) {
            return 'boolean';
        }

        if (\is_int($default)) {
            return 'integer';
        }

        if (\is_float($default)) {
            return 'float';
        }

        if (\is_array($default)) {
            return 'array';
        }

        return 'string';
    }
}
