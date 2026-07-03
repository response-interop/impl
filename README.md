# Response-Interop Implementation Package

[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat-square)](https://github.com/php-pds/skeleton)
[![PDS Composer Script Names](https://img.shields.io/badge/pds-composer--script--names-blue?style=flat-square)](https://github.com/php-pds/composer-script-names)

Reference implementations of the [Response-Interop][] interfaces for PHP 8.4+.

## Installation

Install this package via [Composer][]:

```
composer require response-interop/impl
```

## Usage

Build a [_ResponseStruct_][], populate it, and hand it to a [_ResponseSenderService_][]:

```php
use ResponseInterop\Impl\Response;
use ResponseInterop\Impl\ResponseSender;

$response = new Response();
$response->headers->setHeader('content-type', 'text/plain');
$response->body = 'Hello, world!';

(new ResponseSender())->sendResponse($response);
```

Every `Response` property is also a promoted constructor argument; its defaults
are:

```php
use ResponseInterop\Impl\Response;
use ResponseInterop\Impl\ResponseHeaders;

$response = new Response(
    httpVersion: '1.1',
    statusCode: 200,
    headers: new ResponseHeaders(),
    body: '',
);
```

`ResponseSender` writes to `php://output` by default. Pass any writable
resource to send elsewhere, such as a buffer to capture output in tests:

```php
use ResponseInterop\Impl\Response;
use ResponseInterop\Impl\ResponseSender;

$buffer = fopen('php://memory', 'wb+');

/** @var Response $response */
(new ResponseSender($buffer))->sendResponse($response);

rewind($buffer);
$sent = stream_get_contents($buffer);
```

Set a cookie:

```php
use ResponseInterop\Impl\Response;

/** @var Response $response */
$response->headers->setCookie('session', 'abc123', [
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
```

The `body` accepts a `string`, any `Stringable`, or a
[_ResponseBodyHandler_][] that prepares its own headers and streams its own
content. This package ships two handlers; implement the interface for your own.

Send a JSON body:

```php
use ResponseInterop\Impl\Response;
use ResponseInterop\Impl\JsonResponseBody;

/** @var Response $response */
$response->body = new JsonResponseBody(['hello' => 'world']);
```

The optional arguments customize the output:

```php
use ResponseInterop\Impl\Response;
use ResponseInterop\Impl\JsonResponseBody;

/** @var Response $response */
$response->body = new JsonResponseBody(
    data: ['hello' => 'world'],
    type: 'application/hal+json',
    flags: JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
    depth: 512,
);
```

`$type` sets the `content-type` (default `application/json`); `$flags` and
`$depth` pass through to `json_encode()`, defaulting to an HTML-safe flag set
with `JSON_THROW_ON_ERROR` and a depth of 512. Passing `$flags` replaces the
default set entirely; re-include `JSON_THROW_ON_ERROR` and the HTML-safe flags
to keep them.

Send a file:

```php
use ResponseInterop\Impl\Response;
use ResponseInterop\Impl\FileResponseBody;

/** @var Response $response */
$response->body = new FileResponseBody(new SplFileObject('/path/to/file.pdf'));
```

The optional arguments set the response headers, defaulting from the file
itself:

```php
use ResponseInterop\Impl\Response;
use ResponseInterop\Impl\FileResponseBody;

/** @var Response $response */
$response->body = new FileResponseBody(
    file: new SplFileObject('/path/to/report.pdf'),
    type: 'application/pdf',
    encoding: 'binary',
    disposition: 'attachment',
    filename: 'report.pdf',
);
```

`$type` sets the `content-type` (default `application/octet-stream`),
`$encoding` the `content-transfer-encoding` (default `binary`), and
`$disposition`/`$filename` the `content-disposition` (default `attachment`
using the file's own name). A `content-length` header is added from the file
size.

## Classes

| Interface                       | Implementation          |
| ------------------------------- | ----------------------- |
| _ResponseStruct_                | `Response`              |
| _ResponseHeadersCollection_     | `ResponseHeaders`       |
| _ResponseBodyHandler_           | `FileResponseBody`, `JsonResponseBody` |
| _ResponseCookieHelperService_   | `ResponseCookieHelper`  |
| _ResponseSenderService_         | `ResponseSender`        |
| _ResponseBodySenderService_     | `ResponseSender`        |
| _ResponseThrowable_             | `ResponseException`     |

All classes are in the `ResponseInterop\Impl` namespace.

## Errors

Every exception thrown by this package implements the _ResponseThrowable_ marker
interface. Catch that instead of the concrete `ResponseException` for
portability across implementations:

```php
use ResponseInterop\Impl\Response;
use ResponseInterop\Interface\ResponseThrowable;

try {
    /** @var Response $response */
    $response->headers->setHeader('content-type', '');
} catch (ResponseThrowable $e) {
    // handle the blank header value
}
```

`ResponseHeaders` throws on an invalid or empty header field name, a blank
header value, a blank cookie name, or an unparseable `set-cookie` string.
`ResponseSender` throws on a negative length or offset, or on a read or seek
failure, when sending a body from a stream resource.

See the [Response-Interop][] interface package for the full specification.

[Response-Interop]: https://github.com/response-interop/interface
[_ResponseStruct_]: https://github.com/response-interop/interface#responsestruct
[_ResponseSenderService_]: https://github.com/response-interop/interface#responsesenderservice
[_ResponseBodyHandler_]: https://github.com/response-interop/interface#responsebodyhandler
[Composer]: https://getcomposer.org
