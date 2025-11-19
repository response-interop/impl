<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseBodyHandler;
use ResponseInterop\Interface\ResponseStruct;
use ResponseInterop\Interface\ResponseTypeAliases;
use SplFileObject;
use StreamInterop\Interface\ResourceStream;

/**
 * @phpstan-import-type response_header_value_string from ResponseTypeAliases
 */
class FileResponseBody implements ResponseBodyHandler
{
    /**
     * @param ?response_header_value_string $type
     * @param ?response_header_value_string $encoding
     * @param ?response_header_value_string $disposition
     * @param ?response_header_value_string $filename
     */
    public function __construct(
        public SplFileObject $file,
        public ?string $type = null,
        public ?string $encoding = null,
        public ?string $disposition = null,
        public ?string $filename = null,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function prepareResponse(ResponseStruct $response) : void
    {
        $response->headers->setHeader(
            'content-type',
            $this->type ?? 'application/octet-stream',
        );

        $response->headers->setHeader(
            'content-transfer-encoding',
            $this->encoding ?? 'binary',
        );

        $disposition = $this->disposition ?? 'attachment';
        $filename = rawurlencode($this->filename ?? $this->file->getFilename());

        $response->headers->setHeader(
            'content-disposition',
            "{$disposition}; filename=\"{$filename}\"",
        );

        $size = (string) $this->file->getSize();

        if ($size !== '') {
            $response->headers->setHeader('content-length', $size);
        }
    }

    /**
     * @inheritdoc
     */
    public function sendResponseBody(ResourceStream $output) : void
    {
        $content = fopen($this->file->getPathName(), 'rb');
        assert(is_resource($content));
        stream_copy_to_stream($content, $output->resource);
        fclose($content);
    }
}
