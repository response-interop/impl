<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseBodyHandler;
use ResponseInterop\Interface\ResponseSenderService;
use ResponseInterop\Interface\ResponseStruct;
use StreamInterop\Interface\ResourceStream;

class FakeResponseSender extends ResponseSender
{
    /**
     * @var mixed[]
     */
    public array $headersSent = [];

    public ResourceStream $output;

    public function sendResponse(
        ResponseStruct $response,
        ResourceStream $output = new ResponseStream(),
    ) : void
    {
        $this->output = $output;
        parent::sendResponse($response, $output);
    }

    protected function sendResponseHeader(
        string $header,
        bool $replace = true,
        int $response_code = 0
    ) : void
    {
        $this->headersSent[] = [$header, $replace, $response_code];
    }
}
