<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use SplFileObject;

class ResponseTest extends \PHPUnit\Framework\TestCase
{
    use ResponseAssertions;

    public function testStringResponse() : void
    {
        $response = new Response();
        $response->statusLine->httpVersion = '2';
        $response->statusLine->statusCode = 200;
        $response->headers->setHeader('content-type', 'text/plain');
        $response->body = "Hello world!";

        FakeResponseFunctions::reset();
        ob_start();
        $response->sendResponse();
        $output = ob_get_clean();

        $this->assertHeaders([
            ['HTTP/2 200', true, 200],
            ['content-type: text/plain', false, 0],
        ]);

        $this->assertSame('Hello world!', $output);
    }

    public function testJsonResponse() : void
    {
        $response = new Response();
        $response->body = new JsonResponseBody(['hello' => 'world']);

        FakeResponseFunctions::reset();
        ob_start();
        $response->sendResponse();
        $output = ob_get_clean();

        $this->assertHeaders([
            ['HTTP/1.1 200', true, 200],
            ['content-type: application/json', false, 0],
        ]);

        $this->assertSame('{"hello":"world"}', $output);
    }

    public function testFileResponse() : void
    {
        $response = new Response();
        $response->body = new FileResponseBody(
            new SplFileObject(__DIR__ . '/hello.txt'),
        );

        FakeResponseFunctions::reset();
        ob_start();
        $response->sendResponse();
        $output = (string) ob_get_clean();

        $this->assertHeaders([
            ['HTTP/1.1 200', true, 200],
            ['content-type: application/octet-stream', false, 0],
            ['content-transfer-encoding: binary', false, 0],
            ['content-disposition: attachment; filename="hello.txt"', false, 0],
            ['content-length: 14', false, 0],
        ]);

        $this->assertSame('Hello, world!', trim($output));
    }
}
