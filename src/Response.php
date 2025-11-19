<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseBodyHandler;
use ResponseInterop\Interface\ResponseHeadersCollection;
use ResponseInterop\Interface\ResponseStruct;
use ResponseInterop\Interface\ResponseTypeAliases;
use StreamInterop\Interface\ResourceStream;
use Stringable;

/**
 * @phpstan-import-type response_status_code_int from ResponseTypeAliases
 */
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
