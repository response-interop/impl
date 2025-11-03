<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseTypeAliases;
use ResponseInterop\Interface\ResponseStatusLineStruct;

/**
 * @phpstan-import-type response_http_version_string from ResponseTypealiases
 * @phpstan-import-type response_status_code_int from ResponseTypealiases
 */
class ResponseStatusLine implements ResponseStatusLineStruct
{
    /**
     * @param response_http_version_string $httpVersion
     * @param response_status_code_int $statusCode
     */
    public function __construct(
        public string $httpVersion = '1.1',
        public int $statusCode = 200,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function sendResponseStatusLine() : void
    {
        header(
            header: "HTTP/{$this->httpVersion} {$this->statusCode}",
            replace: true,
            response_code: $this->statusCode,
        );
    }
}
