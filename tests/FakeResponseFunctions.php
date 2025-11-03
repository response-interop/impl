<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

function header(
    string $header,
    bool $replace = true,
    int $response_code = 0,
) : void
{
    FakeResponseFunctions::$headers[] = [
        $header,
        $replace,
        $response_code,
    ];
}

class FakeResponseFunctions
{
    /**
     * @var mixed[]
     */
    public static array $headers = [];

    public static function reset() : void
    {
        static::$headers = [];
    }
}
