<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

trait ResponseAssertions
{
    /**
     * @param mixed[] $expect
     */
    public function assertHeaders(array $expect) : void
    {
        $this->assertSame($expect, FakeResponseFunctions::$headers);
    }
}
