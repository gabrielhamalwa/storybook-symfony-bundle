<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Controller;

use Storybook\SymfonyBundle\Asset\AssetPipelineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\TwigComponent\ComponentRendererInterface;

final readonly class StorybookController
{
    public function __construct(
        private ComponentRendererInterface $componentRenderer,
        private AssetPipelineInterface $assetPipeline,
    ) {
    }

    #[Route('/_storybook/health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        return new JsonResponse(['status' => 'ok']);
    }

    #[Route('/_storybook/render/{id}', methods: ['POST'])]
    public function render(string $id, Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $componentId = $payload['componentId'] ?? null;
        $args = $payload['args'] ?? [];

        if (!is_string($componentId)) {
            return new JsonResponse(['error' => 'Missing component ID'], 400);
        }

        try {
            $html = $this->componentRenderer->createAndRender($componentId, is_array($args) ? $args : []);

            return new JsonResponse([
                'html' => $html,
                'assets' => $this->assetPipeline->getAssets(),
                'metadata' => [
                    'component' => $id,
                ],
            ]);
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
}
