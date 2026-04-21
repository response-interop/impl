<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

class ResponseHeadersTest extends \PHPUnit\Framework\TestCase
{
    protected ResponseHeaders $headers;

    protected function setUp() : void
    {
        $this->headers = new ResponseHeaders();
    }

    public function testSetHeaderEtc() : void
    {
        $this->assertFalse($this->headers->hasHeader('foo'));
        $this->assertNull($this->headers->getHeader('foo'));
        $this->headers->setHeader('foo', 'bar');
        $this->assertTrue($this->headers->hasHeader('foo'));
        $this->assertSame('bar', $this->headers->getHeader('foo'));
        $this->assertSame(['foo' => 'bar'], $this->headers->getHeaders());
        $this->headers->unsetHeader('foo');
        $this->assertFalse($this->headers->hasHeader('foo'));
    }

    public function testAddHeaderEtc() : void
    {
        $this->assertFalse($this->headers->hasHeaders());
        $this->headers->addHeader('foo', 'bar');
        $this->headers->addHeader('foo', 'baz');
        $this->assertTrue($this->headers->hasHeaders());
        $this->assertSame(['bar', 'baz'], $this->headers->getHeader('foo'));
        $this->assertSame(['foo' => ['bar', 'baz']], $this->headers->getHeaders());
        $this->headers->unsetHeaders();
        $this->assertFalse($this->headers->hasHeaders());
    }

    // split these up:
    // - set/has/getCookie, replace cookie, unset cookie;
    // - hasCookies, unsetCookies
    public function testSetCookiesViaCookies() : void
    {
        $this->assertFalse($this->headers->hasCookies());
        $this->assertFalse($this->headers->hasCookie('foo'));

        $this->headers
            ->setCookie(
                name: 'foo',
                value: 'bar',
                attributes: ['secure' => true, 'samesite' => 'lax'],
            );

        $this->assertTrue($this->headers->hasCookies());
        $this->assertTrue($this->headers->hasCookie('foo'));

        $expectArray = [
            'name' => 'foo',
            'value' => 'bar',
            'attributes' => ['secure' => true, 'samesite' => 'lax'],
        ];

        $this->assertSame($expectArray, $this->headers->getCookieAsArray('foo'));

        $this->assertSame(
            ['foo' => $expectArray],
            $this->headers->getCookiesAsArrays(),
        );

        $expectString = 'foo=bar; secure; samesite=lax';

        $this->assertSame($expectString, $this->headers->getCookieAsString('foo'));

        $this->assertSame(
            ['foo' => $expectString],
            $this->headers->getCookiesAsStrings(),
        );

        $this->assertSame($expectString, $this->headers->getHeader('set-cookie'));

        $this->assertSame(
            ['set-cookie' => $expectString],
            $this->headers->getHeaders(),
        );

        $this->headers->unsetCookie('foo');
        $this->assertFalse($this->headers->hasCookie('foo'));
    }

    public function testSetCookiesViaHeaders() : void
    {
        $this->assertFalse($this->headers->hasHeaders());
        $this->assertFalse($this->headers->hasHeader('set-cookie'));
        $this->assertFalse($this->headers->hasCookies());
        $this->assertFalse($this->headers->hasCookie('foo'));

        $this->headers->setHeader('set-cookie', 'foo=bar; secure; samesite=lax');

        $this->assertTrue($this->headers->hasCookies());
        $this->assertTrue($this->headers->hasCookie('foo'));

        $expectArray = [
            'name' => 'foo',
            'value' => 'bar',
            'attributes' => ['secure' => true, 'samesite' => 'lax'],
        ];

        $this->assertSame($expectArray, $this->headers->getCookieAsArray('foo'));

        $this->assertSame(
            ['foo' => $expectArray],
            $this->headers->getCookiesAsArrays(),
        );

        $expectString = 'foo=bar; secure; samesite=lax';

        $this->assertSame($expectString, $this->headers->getCookieAsString('foo'));

        $this->assertSame(
            ['foo' => $expectString],
            $this->headers->getCookiesAsStrings(),
        );

        $this->assertSame($expectString, $this->headers->getHeader('set-cookie'));

        $this->assertSame(
            ['set-cookie' => $expectString],
            $this->headers->getHeaders(),
        );

        $this->headers->addHeader('set-cookie', 'bar=baz');

        $this->assertSame(
            ['foo' => $expectString, 'bar' => 'bar=baz'],
            $this->headers->getCookiesAsStrings(),
        );

        $this->assertTrue($this->headers->hasCookie('bar'));
    }

    public function testUnsetHeadersClearsCookies() : void
    {
        $this->headers->setCookie('foo', 'bar');
        $this->headers->setHeader('content-type', 'text/plain');
        $this->assertTrue($this->headers->hasCookies());
        $this->assertTrue($this->headers->hasHeader('content-type'));

        $this->headers->unsetHeaders();

        $this->assertFalse($this->headers->hasCookies());
        $this->assertFalse($this->headers->hasHeader('content-type'));
        $this->assertFalse($this->headers->hasHeader('set-cookie'));
    }

    public function testGetHeadersIncludesCookiesAsStrings() : void
    {
        $this->headers->setCookie('foo', 'bar');
        $this->headers->setCookie('baz', 'qux');

        $headers = $this->headers->getHeaders();
        $this->assertArrayHasKey('set-cookie', $headers);

        $this->assertSame(
            $this->headers->getCookiesAsStrings(),
            $headers['set-cookie'],
        );
    }

    public function testHasHeaderSetCookieReturnsTrueWhenCookiesSet() : void
    {
        $this->assertFalse($this->headers->hasHeader('set-cookie'));
        $this->headers->setCookie('foo', 'bar');
        $this->assertTrue($this->headers->hasHeader('set-cookie'));
    }

    public function testInvalidField() : void
    {
        $this->expectException(ResponseException::class);
        $this->headers->setHeader('has spaces', 'invalid field');
    }

    public function testBlankNotAllowed() : void
    {
        $this->expectException(ResponseException::class);
        $this->headers->setHeader('foo', '    ');
    }

    public function testInvalidCookieString() : void
    {
        $this->expectException(ResponseException::class);
        $this->headers->setHeader('set-cookie', 'invalid-cookie');
    }

    public function testInvalidCookieName() : void
    {
        $this->expectException(ResponseException::class);
        $this->headers->setHeader('set-cookie', '=bar');
    }
}
