<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

class ResponseCookieHelperTest extends \PHPUnit\Framework\TestCase
{
    protected ResponseCookieHelper $helper;

    protected function setUp() : void
    {
        $this->helper = new ResponseCookieHelper();
    }

    public function testParseComposeRoundTrip() : void
    {
        $cookie = [
            'name' => 'session',
            'value' => 'abc123',
            'attributes' => [
                'expires' => 'Wed, 21 Oct 2026 07:28:00 GMT',
                'max-age' => '3600',
                'path' => '/',
                'domain' => 'example.com',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax',
                'partitioned' => true,
            ],
        ];

        $string = $this->helper->composeResponseCookieString($cookie);
        $parsed = $this->helper->parseResponseCookieString($string);
        $this->assertSame($cookie, $parsed);
    }

    public function testParseBooleanFlagAttributes() : void
    {
        $parsed = $this->helper
            ->parseResponseCookieString('foo=bar; secure; httponly; partitioned');

        $this->assertNotNull($parsed);

        $this->assertSame(
            ['secure' => true, 'httponly' => true, 'partitioned' => true],
            $parsed['attributes'],
        );
    }

    public function testParseLowercasesAttributeNames() : void
    {
        $parsed = $this->helper
            ->parseResponseCookieString(
                'foo=bar; Max-Age=3600; SameSite=Strict; Secure',
            );

        $this->assertNotNull($parsed);

        $this->assertSame(
            ['max-age' => '3600', 'samesite' => 'Strict', 'secure' => true],
            $parsed['attributes'],
        );
    }

    public function testParseReturnsNullForMissingEquals() : void
    {
        $this->assertNull(
            $this->helper->parseResponseCookieString('no-equals-here'),
        );
    }

    public function testParseReturnsNullForEmptyName() : void
    {
        $this->assertNull($this->helper->parseResponseCookieString('=value'));
    }

    public function testComposeOmitsEqualsForBooleanTrue() : void
    {
        $cookie = [
            'name' => 'foo',
            'value' => 'bar',
            'attributes' => ['secure' => true],
        ];

        $this->assertSame(
            'foo=bar; secure',
            $this->helper->composeResponseCookieString($cookie),
        );
    }

    public function testEncodingRoundTrip() : void
    {
        $cookie = [
            'name' => 'has space',
            'value' => 'has;semi=eq and%percent',
            'attributes' => [],
        ];

        $string = $this->helper->composeResponseCookieString($cookie);
        $parsed = $this->helper->parseResponseCookieString($string);
        $this->assertSame($cookie, $parsed);
    }
}
