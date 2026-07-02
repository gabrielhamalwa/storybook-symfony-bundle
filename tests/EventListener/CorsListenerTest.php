<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\EventListener\CorsListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class CorsListenerTest extends TestCase
{
    public function testAddsCorsHeadersForStorybookEndpoints(): void
    {
        $listener = new CorsListener();
        $request = Request::create('/_storybook/render/button', 'POST');
        $response = new Response();
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $listener->onKernelResponse($event);

        $this->assertSame('*', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertSame('GET, POST, OPTIONS', $response->headers->get('Access-Control-Allow-Methods'));
        $this->assertSame('Content-Type', $response->headers->get('Access-Control-Allow-Headers'));
    }

    public function testIgnoresNonStorybookPaths(): void
    {
        $listener = new CorsListener();
        $request = Request::create('/some/other/path');
        $response = new Response();
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $listener->onKernelResponse($event);

        $this->assertFalse($response->headers->has('Access-Control-Allow-Origin'));
    }

    public function testReturnsNoContentForPreflightRequests(): void
    {
        $listener = new CorsListener();
        $request = Request::create('/_storybook/render/button', 'OPTIONS');
        $response = new Response();
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        $listener->onKernelResponse($event);

        $this->assertSame(204, $response->getStatusCode());
    }
}
