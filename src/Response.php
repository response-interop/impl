<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseBodyHandler;
use ResponseInterop\Interface\ResponseHeadersCollection;
use ResponseInterop\Interface\ResponseStruct;
use Stringable;

class Response implements ResponseStruct
{
    public function __construct(
        public string $httpVersion = '1.1',
        public int $statusCode = 200,
        public ResponseHeadersCollection $headers = new ResponseHeaders(),
        public string|Stringable|ResponseBodyHandler $body = '',
    ) {
    }
}
