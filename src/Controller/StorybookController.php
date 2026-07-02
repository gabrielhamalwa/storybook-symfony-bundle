<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Controller;

use Storybook\SymfonyBundle\Asset\AssetExtractorInterface;
use Storybook\SymfonyBundle\Component\ComponentAdapterInterface;
use Storybook\SymfonyBundle\Component\ControllerFragmentAdapter;
use Storybook\SymfonyBundle\Component\LiveComponentAdapter;
use Storybook\SymfonyBundle\Component\TemplateAdapter;
use Storybook\SymfonyBundle\Component\TwigComponentAdapter;
use Storybook\SymfonyBundle\Dto\AssetCollection;
use Storybook\SymfonyBundle\Dto\AssetScript;
use Storybook\SymfonyBundle\Dto\AssetStyle;
use Storybook\SymfonyBundle\Dto\RenderRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class StorybookController
{
    public function __construct(
        private TwigComponentAdapter $twigComponentAdapter,
        private TemplateAdapter $templateAdapter,
        private ControllerFragmentAdapter $controllerFragmentAdapter,
        private AssetExtractorInterface $assetExtractor,
        private ?LiveComponentAdapter $liveComponentAdapter = null,
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
            $adapter = $this->resolveAdapter($renderRequest);
            $html = $adapter->render($renderRequest);

            return new JsonResponse([
                'html' => $html,
                'assets' => $this->serializeAssets($this->assetExtractor->extract()),
                'metadata' => [
                    'component' => $id,
                ],
            ]);
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
        // TODO: implement component discovery
        return new JsonResponse(['components' => []]);
    }

    #[Route('/_storybook/source/{id}', methods: ['GET'])]
    public function source(string $id): JsonResponse
    {
        // TODO: implement source extraction
        return new JsonResponse(['template' => null, 'class' => null]);
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

    private function resolveAdapter(RenderRequest $request): ComponentAdapterInterface
    {
        $adapter = $request->adapter;

        if ('template' === $adapter || (null === $adapter && (null !== $request->template || $this->isTemplatePath($request->componentId)))) {
            return $this->templateAdapter;
        }

        if ('controller' === $adapter || (null === $adapter && (null !== $request->controller || $this->isControllerReference($request->componentId)))) {
            return $this->controllerFragmentAdapter;
        }

        if ('live' === $adapter) {
            if (null === $this->liveComponentAdapter) {
                throw new \RuntimeException('Live component adapter is not available.');
            }

            return $this->liveComponentAdapter;
        }

        return $this->twigComponentAdapter;
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
