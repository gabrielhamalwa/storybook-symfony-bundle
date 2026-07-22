<?php

declare(strict_types=1);
use Storybook\SymfonyBundle\EventListener\CorsListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
test('adds cors headers for storybook endpoints', function () {
    $listener = new CorsListener(['https://storybook.example.com']);
    $request = Request::create('/_storybook/render/button', 'POST');
    $request->headers->set('Origin', 'https://storybook.example.com');
    $response = new Response();
    $event = new ResponseEvent(
        $this->createMock(HttpKernelInterface::class),
        $request,
        HttpKernelInterface::MAIN_REQUEST,
        $response
    );

    $listener->onKernelResponse($event);

    expect($response->headers->get('Access-Control-Allow-Origin'))->toBe('https://storybook.example.com');
    expect($response->headers->get('Access-Control-Allow-Methods'))->toBe('GET, POST, OPTIONS');
    expect($response->headers->get('Access-Control-Allow-Headers'))->toBe('Content-Type');
});
test('ignores origins that are not allowed', function () {
    $listener = new CorsListener(['https://storybook.example.com']);
    $request = Request::create('/_storybook/render/button');
    $request->headers->set('Origin', 'https://untrusted.example.com');
    $response = new Response();
    $event = new ResponseEvent(
        $this->createMock(HttpKernelInterface::class),
        $request,
        HttpKernelInterface::MAIN_REQUEST,
        $response
    );

    $listener->onKernelResponse($event);

    expect($response->headers->has('Access-Control-Allow-Origin'))->toBeFalse();
});
test('returns no content for preflight requests', function () {
    $listener = new CorsListener(['*']);
    $request = Request::create('/_storybook/render/button', 'OPTIONS');
    $request->headers->set('Origin', 'https://storybook.example.com');
    $response = new Response();
    $event = new ResponseEvent(
        $this->createMock(HttpKernelInterface::class),
        $request,
        HttpKernelInterface::MAIN_REQUEST,
        $response
    );

    $listener->onKernelResponse($event);

    expect($response->getStatusCode())->toBe(204);
});
test('does not enable cors by default', function () {
    $listener = new CorsListener();
    $request = Request::create('/_storybook/render/button', 'POST');
    $request->headers->set('Origin', 'https://storybook.example.com');
    $response = new Response();
    $event = new ResponseEvent(
        $this->createMock(HttpKernelInterface::class),
        $request,
        HttpKernelInterface::MAIN_REQUEST,
        $response
    );

    $listener->onKernelResponse($event);

    expect($response->headers->has('Access-Control-Allow-Origin'))->toBeFalse();
});
