<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

class FakeResponseSender extends ResponseSender
{
    /**
     * @var mixed[]
     */
    public array $headersSent = [];

    public mixed $output;

    /**
     * @inheritdoc
     */
    public function sendResponseHeader(
        string $header,
        bool $replace = true,
        int $statusCode = 0,
    ) : void
    {
        $this->headersSent[] = [$header, $replace, $statusCode];
    }
}
