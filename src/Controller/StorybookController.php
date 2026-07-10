<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Controller;

use Storybook\SymfonyBundle\Asset\AssetExtractorInterface;
use Storybook\SymfonyBundle\Component\ComponentResolver;
use Storybook\SymfonyBundle\Dto\AssetCollection;
use Storybook\SymfonyBundle\Dto\AssetScript;
use Storybook\SymfonyBundle\Dto\AssetStyle;
use Storybook\SymfonyBundle\Dto\RenderRequest;
use Storybook\SymfonyBundle\Dto\RenderResponse;
use Storybook\SymfonyBundle\Indexer\ComponentIndexerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class StorybookController
{
    public function __construct(
        private ComponentResolver $componentResolver,
        private AssetExtractorInterface $assetExtractor,
        private ?ComponentIndexerInterface $componentIndexer = null,
    ) {
    }

    #[Route('/_storybook/health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/_storybook/render/{id}', methods: ['POST'], defaults: ['_locale' => null])]
    public function render(string $id, Request $request): JsonResponse
    {
        $renderRequest = $this->parseRequest($request, $id);

        try {
            $adapter = $this->componentResolver->resolve($renderRequest);
            $html = $adapter->render($renderRequest);

            $response = new RenderResponse(
                html: $html,
                assets: $this->serializeAssets($this->assetExtractor->extract()),
                metadata: [
                    'component' => $id,
                ],
            );

            return new JsonResponse($response->toArray());
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], 400);
        } catch (\Throwable $exception) {
            return new JsonResponse([
                'error' => 'Failed to render component',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    #[Route('/_storybook/index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        if (null === $this->componentIndexer) {
            return new JsonResponse(['components' => []]);
        }

        try {
            return new JsonResponse(['components' => $this->componentIndexer->index()]);
        } catch (\Throwable $exception) {
            return new JsonResponse([
                'error' => 'Failed to index components',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    #[Route('/_storybook/source/{id}', methods: ['GET'])]
    public function source(string $id): JsonResponse
    {
        if (null === $this->componentIndexer) {
            return new JsonResponse(['template' => null, 'class' => null]);
        }

        try {
            $source = $this->componentIndexer->getComponentSource($id);

            return new JsonResponse($source);
        } catch (\Throwable $exception) {
            return new JsonResponse([
                'error' => 'Failed to read source',
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    private function parseRequest(Request $request, string $id): RenderRequest
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $args = $payload['args'] ?? [];
        $globals = $payload['globals'] ?? [];

        return new RenderRequest(
            id: $id,
            componentId: \is_string($payload['componentId'] ?? null) ? $payload['componentId'] : null,
            adapter: \is_string($payload['adapter'] ?? null) ? $payload['adapter'] : null,
            template: \is_string($payload['template'] ?? null) ? $payload['template'] : null,
            controller: \is_string($payload['controller'] ?? null) ? $payload['controller'] : null,
            args: \is_array($args) ? $args : [],
            globals: \is_array($globals) ? $globals : [],
        );
    }

    private function serializeAssets(AssetCollection $collection): array
    {
        return [
            'pipeline' => $collection->pipeline,
            'styles' => array_map(
                static fn (AssetStyle $style): array => ['url' => $style->href],
                $collection->styles
            ),
            'scripts' => array_map(
                static fn (AssetScript $script): array => [
                    'url' => $script->src,
                    'type' => $script->type,
                    'async' => $script->async,
                    'defer' => $script->defer,
                ],
                $collection->scripts
            ),
            'importmap' => $collection->importmap,
        ];
    }
}
    }

    private function isTemplatePath(?string $componentId): bool
    {
        return \is_string($componentId) && str_ends_with($componentId, '.twig');
    }

    private function isControllerReference(?string $componentId): bool
    {
        return \is_string($componentId) && str_contains($componentId, '::');
    }

    private function serializeAssets(AssetCollection $collection): array
    {
        return [
            'pipeline' => $collection->pipeline,
            'styles' => array_map(
                static fn (AssetStyle $style): array => ['url' => $style->href],
                $collection->styles
            ),
            'scripts' => array_map(
                static fn (AssetScript $script): array => [
                    'url' => $script->src,
                    'type' => $script->type,
                    'async' => $script->async,
                    'defer' => $script->defer,
                ],
                $collection->scripts
            ),
            'importmap' => $collection->importmap,
        ];
    }
}
    private function isTemplatePath(?string $componentId): bool
    {
        return \is_string($componentId) && str_ends_with($componentId, '.twig');
    }

    private function isControllerReference(?string $componentId): bool
    {
        return \is_string($componentId) && str_contains($componentId, '::');
    }

    private function serializeAssets(AssetCollection $collection): array
    {
        return [
            'pipeline' => $collection->pipeline,
            'styles' => array_map(
                static fn (AssetStyle $style): array => ['url' => $style->href],
                $collection->styles
            ),
            'scripts' => array_map(
                static fn (AssetScript $script): array => [
                    'url' => $script->src,
                    'type' => $script->type,
                    'async' => $script->async,
                    'defer' => $script->defer,
                ],
                $collection->scripts
            ),
            'importmap' => $collection->importmap,
        ];
    }
}
