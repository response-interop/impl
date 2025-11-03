<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseBodyContent;
use ResponseInterop\Interface\ResponseStruct;

class JsonResponseBody implements ResponseBodyContent
{
    public const int DEFAULT_FLAGS = JSON_HEX_TAG
        | JSON_HEX_APOS
        | JSON_HEX_AMP
        | JSON_HEX_QUOT
        | JSON_THROW_ON_ERROR;

    /**
     * @param ?non-empty-string $type
     * @param mixed[]|object $data
     * @param int<1,max> $depth
     */
    public function __construct(
        public array|object $data,
        public ?string $type = null,
        public ?int $flags = null,
        public ?int $depth = null,
    ) {
    }

    public function prepareResponse(ResponseStruct $response) : void
    {
        $response->headers->setHeader(
            'content-type',
            $this->type ?? 'application/json'
        );
    }

    public function sendResponseBody() : void
    {
        echo json_encode(
            $this->data,
            $this->flags ?? static::DEFAULT_FLAGS,
            $this->depth ?? 512,
        );
    }
}
