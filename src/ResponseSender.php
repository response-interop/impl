<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseBodyHandler;
use ResponseInterop\Interface\ResponseSenderService;
use ResponseInterop\Interface\ResponseStruct;
use StreamInterop\Interface\ResourceStream;

class ResponseSender implements ResponseSenderService
{
    public function sendResponse(
        ResponseStruct $response,
        ResourceStream $output = new ResponseStream(),
    ) : void
    {
        if ($response->body instanceof ResponseBodyHandler) {
            $response->body->prepareResponse($response);
        }

        $this->sendResponseHeader(
            "HTTP/{$response->httpVersion} {$response->statusCode}",
            response_code: $response->statusCode,
        );

        if ($response->httpVersion === '2') {
            $this->sendResponseHeader(":status: {$response->statusCode}");
        }

        foreach ($response->headers->getHeaders() as $field => $values) {
            foreach ((array) $values as $value) {
                $this->sendResponseHeader(
                    "{$field}: {$value}",
                    replace: false
                );
            }
        }

        if ($response->body instanceof ResponseBodyHandler) {
            $response->body->sendResponseBody($output);
        } else {
            fwrite($output->resource, (string) $response->body);
        }
    }

    protected function sendResponseHeader(
        string $header,
        bool $replace = true,
        int $response_code = 0
    ) : void
    {
        header($header, $replace, $response_code);
    }
}
