<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use StreamInterop\Interface\ResourceStream;

class ResponseStream implements ResourceStream
{
    /**
     * @inheritdoc
     */
    public array $metadata {
        get {
            return $this->isOpen() ? stream_get_meta_data($this->resource) : [];
        }
    }

    /**
     * @var resource
     */
    public protected(set) mixed $resource;

    public function __construct(
        string $filename = 'php://output',
        string $mode = 'wb'
    ) {
        $resource = fopen($filename, $mode);
        assert(is_resource($resource));
        $this->resource = $resource;
        assert($this->isOpen());
    }

    public function __destruct()
    {
        if (! $this->isClosed()) {
            fclose($this->resource);
        }
    }

    /**
     * @inheritdoc
     */
    public function isClosed() : bool
    {
        return strtolower(get_resource_type($this->resource)) === 'unknown';
    }

    /**
     * @inheritdoc
     */
    public function isOpen() : bool
    {
        return strtolower(get_resource_type($this->resource)) === 'stream';
    }
}
