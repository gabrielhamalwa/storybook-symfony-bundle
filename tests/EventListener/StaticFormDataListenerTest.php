<?php

declare(strict_types=1);

namespace Storybook\SymfonyBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use Storybook\SymfonyBundle\EventListener\StaticFormDataListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class StaticFormDataListenerTest extends TestCase
{
    public function testRestoresEncodedFormFieldsAndRemovesTheTransportHeader(): void
    {
        $request = Request::create('/_components/Counter/increment', 'POST');
        $request->headers->set(
            'X-Storybook-Symfony-Form-Data',
            base64_encode('data=%7B%22message%22%3A%22Gr%C3%BC%C3%9Fe%22%7D&tags%5B%5D=one&tags%5B%5D=two')
        );
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        (new StaticFormDataListener())->onKernelRequest($event);

        self::assertSame(['message' => 'Grüße'], json_decode($request->request->getString('data'), true));
        self::assertSame(['one', 'two'], $request->request->all('tags'));
        self::assertFalse($request->headers->has('X-Storybook-Symfony-Form-Data'));
    }

    public function testRejectsMalformedFormData(): void
    {
        $request = Request::create('/_components/Counter/increment', 'POST');
        $request->headers->set('X-Storybook-Symfony-Form-Data', '%%%');
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        $this->expectException(BadRequestHttpException::class);

        (new StaticFormDataListener())->onKernelRequest($event);
    }

    public function testRestoresSingleAndMultipleUploadedFiles(): void
    {
        $directory = '/tmp/storybook-uploads/'.random_int(1, PHP_INT_MAX);
        mkdir($directory, 0700, true);
        file_put_contents($directory.'/0', 'single upload');
        file_put_contents($directory.'/1', 'first multiple upload');
        file_put_contents($directory.'/2', 'second multiple upload');

        $request = Request::create('/_components/FileUpload/upload', 'POST');
        $request->headers->set(
            'X-Storybook-Symfony-Uploaded-Files',
            base64_encode(json_encode([
                ['field' => 'my_file', 'name' => 'single.txt', 'path' => $directory.'/0', 'type' => 'text/plain'],
                ['field' => 'multiple[]', 'name' => 'first.txt', 'path' => $directory.'/1', 'type' => 'text/plain'],
                ['field' => 'multiple[]', 'name' => 'second.txt', 'path' => $directory.'/2', 'type' => 'text/plain'],
            ], JSON_THROW_ON_ERROR))
        );
        $event = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST
        );

        try {
            (new StaticFormDataListener())->onKernelRequest($event);

            self::assertSame('single.txt', $request->files->get('my_file')?->getClientOriginalName());
            self::assertSame('single upload', $request->files->get('my_file')?->getContent());
            self::assertSame('first.txt', $request->files->all('multiple')[0]->getClientOriginalName());
            self::assertSame('second multiple upload', $request->files->all('multiple')[1]->getContent());
        } finally {
            unlink($directory.'/0');
            unlink($directory.'/1');
            unlink($directory.'/2');
            rmdir($directory);
        }
    }
}
