<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseBodyHandler;
use ResponseInterop\Interface\ResponseBodySenderService;
use ResponseInterop\Interface\ResponseStruct;

class JsonResponseBody implements ResponseBodyHandler
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

    /**
     * @inheritdoc
     */
    public function prepareResponse(ResponseStruct $response) : void
    {
        $response->headers
            ->setHeader('content-type', $this->type ?? 'application/json');
    }

    /**
     * @inheritdoc
     */
    public function sendResponseBody(ResponseBodySenderService $bodySender) : void
    {
        $bodySender->sendResponseBodyString(
            (string) json_encode(
                $this->data,
                $this->flags ?? static::DEFAULT_FLAGS,
                $this->depth ?? 512,
            ),
        );
    }
}
