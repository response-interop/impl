<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseThrowable;
use RuntimeException;

class ResponseException extends RuntimeException implements ResponseThrowable
{
}
