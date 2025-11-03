<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseBodyContent;
use ResponseInterop\Interface\ResponseHeadersCollection;
use ResponseInterop\Interface\ResponseStatusLineStruct;
use ResponseInterop\Interface\ResponseStruct;
use ResponseInterop\Interface\ResponseTypeAliases;
use Stringable;

class Response implements ResponseStruct
{
    public function __construct(
        public ResponseStatusLineStruct $statusLine = new ResponseStatusLine(),
        public ResponseHeadersCollection $headers = new ResponseHeaders(),
        public string|Stringable|ResponseBodyContent $body = '',
    ) {
    }

    /**
     * @inheritdoc
     */
    public function sendResponse() : void
    {
        if ($this->body instanceof ResponseBodyContent) {
            $this->body->prepareResponse($this);
        }

        $this->statusLine->sendResponseStatusLine();
        $this->headers->sendResponseHeaders();

        if ($this->body instanceof ResponseBodyContent) {
            $this->body->sendResponseBody();
        } else {
            echo $this->body;
        }
    }
}
