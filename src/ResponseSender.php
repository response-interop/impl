<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseBodyHandler;
use ResponseInterop\Interface\ResponseBodySenderService;
use ResponseInterop\Interface\ResponseSenderService;
use ResponseInterop\Interface\ResponseStruct;
use Stringable;

class ResponseSender implements ResponseSenderService, ResponseBodySenderService
{
    /**
     * @var resource
     */
    protected mixed $output;

    /**
     * @param null|false|resource $output
     */
    public function __construct(mixed $output = null)
    {
        $output ??= fopen('php://output', 'wb');
        assert(is_resource($output));
        $this->output = $output;
    }

    /**
     * @inheritdoc
     */
    public function sendResponse(ResponseStruct $response) : void
    {
        if ($response->body instanceof ResponseBodyHandler) {
            $response->body->prepareResponse($response);
        }

        $this->sendResponseHeader(
            "HTTP/{$response->httpVersion} {$response->statusCode}",
            statusCode: $response->statusCode,
        );

        if ($response->httpVersion === '2') {
            $this->sendResponseHeader(":status: {$response->statusCode}");
        }

        foreach ($response->headers->getHeaders() as $field => $values) {
            foreach ((array) $values as $value) {
                $this->sendResponseHeader("{$field}: {$value}", replace: false);
            }
        }

        if ($response->body instanceof ResponseBodyHandler) {
            $response->body->sendResponseBody($this);
        } else {
            $this->sendResponseBodyString($response->body);
        }
    }

    /**
     * @inheritdoc
     */
    protected function sendResponseHeader(
        string $header,
        bool $replace = true,
        int $statusCode = 0,
    ) : void
    {
        header($header, $replace, $statusCode);
    }

    /**
     * @inheritdoc
     */
    public function sendResponseBodyString(string|Stringable $content) : void
    {
        fwrite($this->output, (string) $content);
    }

    /**
     * @inheritdoc
     */
    public function sendResponseBodyResource(
        mixed $content,
        ?int $length = null,
        ?int $offset = null,
    ) : int
    {
        if ($length !== null && $length < 0) {
            throw new ResponseException(
                "Length must not be negative, actually {$length}.",
            );
        }

        if ($offset !== null) {
            if ($offset < 0) {
                throw new ResponseException(
                    "Offset must not be negative, actually {$offset}.",
                );
            }

            $this->seek($content, $offset);
        }

        $bytes = stream_copy_to_stream($content, $this->output, $length);

        if ($bytes === false) {
            throw new ResponseException(
                "Could not write content resource to response resource.",
            );
        }

        return $bytes;
    }

    /**
     * @param resource $content
     */
    protected function seek(mixed $content, int $offset) : void
    {
        $result = fseek($content, $offset);

        if ($result === -1) {
            throw new ResponseException(
                "Could not seek to {$offset} on content resource.",
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function flushResponse() : void
    {
        flush();
    }
}
