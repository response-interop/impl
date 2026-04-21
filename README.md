# response-interop/impl

[![PDS Skeleton](https://img.shields.io/badge/pds-skeleton-blue.svg?style=flat-square)](https://github.com/php-pds/skeleton)
[![PDS Composer Script Names](https://img.shields.io/badge/pds-composer--script--names-blue?style=flat-square)](https://github.com/php-pds/composer-script-names)

Reference implementations of the [Response-Interop][] interfaces for PHP 8.4+.

## Installation

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

Set a cookie:

```php
$response->headers->setCookie('session', 'abc123', [
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
```

Send a JSON body:

```php
use ResponseInterop\Impl\JsonResponseBody;

$response->body = new JsonResponseBody(['hello' => 'world']);
```

Send a file:

```php
use ResponseInterop\Impl\FileResponseBody;

$response->body = new FileResponseBody(new SplFileObject('/path/to/file.pdf'));
```

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

See the [Response-Interop][] interface package for the full specification.

[Response-Interop]: https://github.com/response-interop/interface
[_ResponseStruct_]: https://github.com/response-interop/interface#responsestruct
[_ResponseSenderService_]: https://github.com/response-interop/interface#responsesenderservice
