<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Adds CORS headers to Storybook backend responses so the Storybook preview
 * iframe can fetch rendered components from a different origin during development.
 */
final readonly class CorsListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/_storybook/')) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');

        if ($request->isMethod('OPTIONS')) {
            $response->setStatusCode(204);
        }
    }
}
