<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Adds explicitly configured CORS headers for a separately hosted static Storybook.
 */
final readonly class CorsListener
{
    public function __construct(private array $allowedOrigins = [])
    {
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $origin = $request->headers->get('Origin');
        if (null === $origin || (!\in_array('*', $this->allowedOrigins, true) && !\in_array($origin, $this->allowedOrigins, true))) {
            return;
        }

        $response = $event->getResponse();
        $response->headers->set('Access-Control-Allow-Origin', \in_array('*', $this->allowedOrigins, true) ? '*' : $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type');

        if ($request->isMethod('OPTIONS')) {
            $response->setStatusCode(204);
        }
    }
}
