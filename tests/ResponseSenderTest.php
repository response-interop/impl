<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use SplFileObject;

class ResponseSenderTest extends \PHPUnit\Framework\TestCase
{
    private FakeResponseSender $responseSender;

    protected function setUp() : void
    {
        $this->responseSender = new FakeResponseSender(
            output: fopen('php://memory', 'wb+'),
        );
    }

    protected function tearDown() : void
    {
        unset($this->responseSender);
    }

    public function testHttp2Response() : void
    {
        $response = new Response();
        $response->httpVersion = '2';
        $response->statusCode = 404;
        $response->body = "Not found.";
        $this->responseSender->sendResponse($response);

        $this->assertHeaders([
            ['HTTP/2 404', true, 404],
            [':status: 404', true, 0],
        ]);

        $this->assertBody('Not found.');
    }

    public function testStringResponse() : void
    {
        $response = new Response();
        $response->headers->setHeader('content-type', 'text/plain');
        $response->body = "Hello world!";
        $this->responseSender->sendResponse($response);

        $this->assertHeaders([
            ['HTTP/1.1 200', true, 200],
            ['content-type: text/plain', false, 0],
        ]);

        $this->assertBody('Hello world!');
    }

    public function testJsonResponse() : void
    {
        $response = new Response();
        $response->body = new JsonResponseBody(['hello' => 'world']);
        $this->responseSender->sendResponse($response);

        $this->assertHeaders([
            ['HTTP/1.1 200', true, 200],
            ['content-type: application/json', false, 0],
        ]);

        $this->assertBody('{"hello":"world"}');
    }

    public function testFileResponse() : void
    {
        $file = new SplFileObject(__DIR__ . '/hello.txt');
        $response = new Response();
        $response->body = new FileResponseBody($file);
        $this->responseSender->sendResponse($response);

        $this->assertHeaders([
            ['HTTP/1.1 200', true, 200],
            ['content-type: application/octet-stream', false, 0],
            ['content-transfer-encoding: binary', false, 0],
            ['content-disposition: attachment; filename="hello.txt"', false, 0],
            ['content-length: 14', false, 0],
        ]);

        $expect = (string) file_get_contents($file->getPathName());
        $this->assertBody($expect);
    }

    public function testNegativeLengthThrows() : void
    {
        $resource = fopen('php://memory', 'rb+');

        if ($resource === false) {
            $this->fail('Could not open php://memory.');
        }

        $this->expectException(ResponseException::class);
        $this->responseSender->sendResponseBodyResource($resource, length: -1);
    }

    public function testNegativeOffsetThrows() : void
    {
        $resource = fopen('php://memory', 'rb+');

        if ($resource === false) {
            $this->fail('Could not open php://memory.');
        }

        $this->expectException(ResponseException::class);
        $this->responseSender->sendResponseBodyResource($resource, offset: -1);
    }

    /**
     * @param mixed[] $expect
     */
    protected function assertHeaders(array $expect) : void
    {
        $this->assertSame($this->responseSender->headersSent, $expect);
    }

    protected function assertBody(string $expect) : void
    {
        $output = $this->responseSender->output;
        rewind($output);
        $actual = (string) stream_get_contents($output);
        $this->assertSame($expect, $actual);
    }
}
