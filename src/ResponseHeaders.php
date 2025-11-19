<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseHeadersCollection;
use ResponseInterop\Interface\ResponseTypeAliases;

/**
 * This implementation keeps 2 copies of each cookie: a string representation in
 * `$fields['set-cookie']`, and an array representation in `$cookies`.
 *
 * @phpstan-import-type response_cookie_array from ResponseTypeAliases
 * @phpstan-import-type response_cookie_attributes_array from ResponseTypeAliases
 * @phpstan-import-type response_cookie_name_string from ResponseTypeAliases
 * @phpstan-import-type response_cookie_value_string from ResponseTypeAliases
 * @phpstan-import-type response_header_field_string from ResponseTypeAliases
 * @phpstan-import-type response_header_value_string from ResponseTypeAliases
 */
class ResponseHeaders implements ResponseHeadersCollection
{
    /**
     * @var array<
     *     response_header_field_string,
     *     array<response_header_value_string>
     * >
     */
    protected array $fields = [];

    /**
     * @var array<response_cookie_name_string,response_cookie_array>
     */
    protected array $cookies = [];

    public function __construct(
        protected ResponseCookieHelper $cookieHelper = new ResponseCookieHelper(),
    ) {
    }

    /**
     * @inheritdoc
     */
    public function setHeader(string $field, string $value) : void
    {
        $this->normalizeField($field);
        $this->assertNotBlank($value);
        $this->unsetHeader($field);

        if ($field === 'set-cookie') {
            $this->fields['set-cookie'] = [];
            $this->retainCookie($value);
            return;
        }

        $this->fields[$field] = [$value];
    }

    /**
     * @inheritdoc
     */
    public function addHeader(string $field, string $value) : void
    {
        if (! $this->hasHeader($field)) {
            $this->setHeader($field, $value);
            return;
        }

        $this->normalizeField($field);
        $this->assertNotBlank($value);

        if ($field === 'set-cookie') {
            $this->retainCookie($value);
            return;
        }

        $this->fields[$field][] = $value;
    }

    /**
     * @inheritdoc
     */
    public function hasHeader(string $field) : bool
    {
        $this->normalizeField($field);
        return isset($this->fields[$field]);
    }

    /**
     * @inheritdoc
     */
    public function getHeader(string $field) : null|string|array
    {
        $this->normalizeField($field);
        $values = $this->fields[$field] ?? [];

        /**
         * @var null
         *     |response_header_value_string
         *     |array<response_header_value_string>
         */
        $value = match(count($values)) {
            0 => null,
            1 => $values[key($values)],
            default => $values,
        };

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function unsetHeader(string $field) : void
    {
        $this->normalizeField($field);
        unset($this->fields[$field]);

        if ($field === 'set-cookie') {
            $this->cookies = [];
        }
    }

    /**
     * @inheritdoc
     */
    public function hasHeaders() : bool
    {
        return (bool) $this->fields;
    }

    /**
     * @inheritdoc
     */
    public function getHeaders() : array
    {
        $headers = $this->fields;

        foreach ($headers as $field => $values) {
            if (count($values) === 1) {
                $headers[$field] = $values[key($values)];
            }
        }

        return $headers;
    }

    /**
     * @inheritdoc
     */
    public function unsetHeaders() : void
    {
        $this->fields = [];
        $this->unsetCookies();
    }

    /**
     * @inheritdoc
     */
    public function setCookie(
        string $name,
        string $value,
        array $attributes = [],
    ) : void
    {
        $this->assertNotBlank($name);

        $cookie = [
            'name' => $name,
            'value' => $value,
            'attributes' => $attributes,
        ];

        $this->cookies[$cookie['name']] = $cookie;
        $this->fields['set-cookie'] ??= [];

        $this->fields['set-cookie'][$name] = $this->cookieHelper
            ->composeResponseCookieString($cookie);
    }

    /**
     * @inheritdoc
     */
    public function hasCookie(string $name) : bool
    {
        return isset($this->cookies[$name]);
    }

    /**
     * @inheritdoc
     */
    public function getCookieAsArray(string $name) : ?array
    {
        return $this->cookies[$name] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getCookieAsString(string $name) : ?string
    {
        return $this->fields['set-cookie'][$name] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function unsetCookie(string $name) : void
    {
        unset($this->fields['set-cookie'][$name]);
        unset($this->cookies[$name]);
    }

    /**
     * @inheritdoc
     */
    public function hasCookies() : bool
    {
        return (bool) $this->cookies;
    }

    /**
     * @inheritdoc
     */
    public function getCookiesAsArrays() : array
    {
        return $this->cookies;
    }

    /**
     * @inheritdoc
     */
    public function getCookiesAsStrings() : array
    {
        /** @var array<response_cookie_name_string,response_header_value_string> */
        $strings = $this->fields['set-cookie'] ?? [];
        return $strings;
    }

    /**
     * @inheritdoc
     */
    public function unsetCookies() : void
    {
        $this->cookies = [];
        $this->unsetHeader('set-cookie');
    }

    protected function normalizeField(string &$field) : void
    {
        $field = strtolower($field);

        if (! preg_match('/^:?[a-z][a-z0-9-]+$/', $field)) {
            throw new ResponseException(
                "Header field name '{$field}' contains invalid characters, or is empty."
            );
        }
    }

    protected function assertNotBlank(string $string) : void
    {
        if (trim($string) === '') {
            throw new ResponseException("Expected non-blank string, actually blank.");
        }
    }

    /**
     * @param response_header_value_string $setCookieString
     */
    protected function retainCookie(string $setCookieString) : void
    {
        $cookie = $this->cookieHelper->parseResponseCookieString(
            $setCookieString,
        );

        if ($cookie === null) {
            throw new ResponseException("Could not parse set-cookie string: '{$setCookieString}'");
        }

        $this->fields['set-cookie'][$cookie['name']] = $setCookieString;
        $this->cookies[$cookie['name']] = $cookie;
    }
}
