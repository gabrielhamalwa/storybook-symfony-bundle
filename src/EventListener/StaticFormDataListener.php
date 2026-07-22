<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\EventListener;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Restores form fields transported alongside a static PHP-WASM request.
 */
final readonly class StaticFormDataListener
{
    private const HEADER = 'X-Storybook-Symfony-Form-Data';
    private const FILES_HEADER = 'X-Storybook-Symfony-Uploaded-Files';

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $encodedFormData = $request->headers->get(self::HEADER);
        $encodedFiles = $request->headers->get(self::FILES_HEADER);
        if (null === $encodedFormData && null === $encodedFiles) {
            return;
        }
        $request->headers->remove(self::HEADER);
        $request->headers->remove(self::FILES_HEADER);

        if (null !== $encodedFormData) {
            parse_str($this->decode($encodedFormData), $parameters);
            $request->request->replace($parameters);
        }

        if (null !== $encodedFiles) {
            $request->files->replace($this->createUploadedFiles($this->decode($encodedFiles)));
        }
    }

    private function decode(string $encoded): string
    {
        $decoded = base64_decode($encoded, true);
        if (false === $decoded) {
            throw new BadRequestHttpException('Invalid Storybook static request data.');
        }

        return $decoded;
    }

    /**
     * @return array<string, UploadedFile|array>
     */
    private function createUploadedFiles(string $encodedFiles): array
    {
        try {
            $descriptors = json_decode($encodedFiles, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw new BadRequestHttpException('Invalid Storybook static upload data.');
        }

        if (!is_array($descriptors)) {
            throw new BadRequestHttpException('Invalid Storybook static upload data.');
        }

        $files = [];
        foreach ($descriptors as $descriptor) {
            if (!is_array($descriptor)
                || !isset($descriptor['field'], $descriptor['name'], $descriptor['path'], $descriptor['type'])
                || !is_string($descriptor['field'])
                || !is_string($descriptor['name'])
                || !is_string($descriptor['path'])
                || !is_string($descriptor['type'])
                || !preg_match('/^[^\[\]]+(?:\[[^\[\]]*\])*$/D', $descriptor['field'])
                || !preg_match('#^/tmp/storybook-uploads/\d+/\d+$#D', $descriptor['path'])
                || !is_file($descriptor['path'])) {
                throw new BadRequestHttpException('Invalid Storybook static upload data.');
            }

            $this->addUploadedFile(
                $files,
                $descriptor['field'],
                new UploadedFile($descriptor['path'], $descriptor['name'], $descriptor['type'], test: true)
            );
        }

        return $files;
    }

    /**
     * @param array<string, UploadedFile|array> $files
     */
    private function addUploadedFile(array &$files, string $field, UploadedFile $file): void
    {
        preg_match_all('/[^\[\]]+|(?<=\[)(?=\])/', $field, $matches);
        $segments = $matches[0];

        $target = &$files;
        foreach ($segments as $index => $segment) {
            $last = $index === array_key_last($segments);
            if ($last) {
                if ('' === $segment) {
                    $target[] = $file;
                } else {
                    $target[$segment] = $file;
                }

                return;
            }

            if ('' === $segment) {
                $target[] = [];
                $target = &$target[array_key_last($target)];
            } else {
                $target[$segment] ??= [];
                $target = &$target[$segment];
            }
        }
    }
}
