<?php
declare(strict_types=1);

namespace ResponseInterop\Impl;

use ResponseInterop\Interface\ResponseCookieHelperService;
use ResponseInterop\Interface\ResponseTypeAliases;

/**
 * @phpstan-import-type response_cookie_array from ResponseTypeAliases
 * @phpstan-import-type response_cookie_attributes_array from ResponseTypeAliases
 * @phpstan-import-type response_header_value_string from ResponseTypeAliases
 */
class ResponseCookieHelper implements ResponseCookieHelperService
{
    /**
     * @inheritdoc
     */
    public function parseResponseCookieString(string $setCookieString) : ?array
    {
        $cookie = [];

        // 1.  If the set-cookie-string contains a %x3B (";") character ...
        $semicolonPos = strpos($setCookieString, ';');

        if ($semicolonPos !== false) {
            // The name-value-pair string consists of the characters up to,
            // but not including, the first %x3B (";"), and the unparsed-
            // attributes consist of the remainder of the set-cookie-string
            // (including the %x3B (";") in question).
            $nameValuePair = substr($setCookieString, 0, $semicolonPos);
            $unparsedAttributes = substr($setCookieString, $semicolonPos);
        } else {
            // Otherwise:
            //
            // The name-value-pair string consists of all the characters
            // contained in the set-cookie-string, and the unparsed-
            // attributes is the empty string.
            $nameValuePair = $setCookieString;
            $unparsedAttributes = '';
        }

        // 2.  If the name-value-pair string lacks a %x3D ("=") character,
        // ignore the set-cookie-string entirely.
        $equalsPos = strpos($nameValuePair, '=');

        if ($equalsPos === false) {
            return null;
        }

        // 3.  The (possibly empty) name string consists of the characters up
        // to, but not including, the first %x3D ("=") character, and the
        // (possibly empty) value string consists of the characters after
        // the first %x3D ("=") character.
        //
        // 4.  Remove any leading or trailing WSP characters from the name
        // string and the value string.
        $name = trim(substr($nameValuePair, 0, $equalsPos));
        $value = trim(substr($nameValuePair, $equalsPos + 1));

        // 5.  If the name string is empty, ignore the set-cookie-string
        // entirely.
        if ($name === '') {
            return null;
        }

        // 6.  The cookie-name is the name string, and the cookie-value is the
        // value string.
        $cookie['name'] = $this->decode($name);
        $cookie['value'] = $this->decode($value);

        // The user agent MUST use an algorithm equivalent to the following
        // algorithm to parse the unparsed-attributes:
        $cookie['attributes'] = $this->parseResponseCookieStringAttributes(
            $unparsedAttributes,
        );

        /** @var response_cookie_array */
        return $cookie;
    }

    /**
     * @param response_header_value_string $unparsedAttributes
     * @return response_cookie_attributes_array
     */
    protected function parseResponseCookieStringAttributes(
        string $unparsedAttributes,
    ) : array
    {
        $attributes = [];

        // 1.  If the unparsed-attributes string is empty, skip the rest of
        // these steps.
        if ($unparsedAttributes === '') {
            return $attributes;
        }

        while ($unparsedAttributes) {
            // 2.  Discard the first character of the unparsed-attributes (which
            // will be a %x3B (";") character).
            $unparsedAttributes = substr($unparsedAttributes, 1);

            // 3.  If the remaining unparsed-attributes contains a %x3B (";")
            // character:
            $semicolonPos = strpos($unparsedAttributes, ';');

            if ($semicolonPos !== false) {
                // Consume the characters of the unparsed-attributes up to, but
                // not including, the first %x3B (";") character.
                $consumed = substr($unparsedAttributes, 0, $semicolonPos);
                $unparsedAttributes = substr($unparsedAttributes, $semicolonPos);
            } else {
                // Otherwise:
                //
                // Consume the remainder of the unparsed-attributes.
                $consumed = $unparsedAttributes;
                $unparsedAttributes = '';
            }

            // Let the cookie-av string be the characters consumed in this step.
            $av = $consumed;

            // 4.  If the cookie-av string contains a %x3D ("=") character:
            $equalsPos = strpos($av, '=');

            if ($equalsPos !== false) {
                // The (possibly empty) attribute-name string consists of the
                // characters up to, but not including, the first %x3D ("=")
                // character, and the (possibly empty) attribute-value string
                // consists of the characters after the first %x3D ("=")
                // character.
                $name = trim(substr($av, 0, $equalsPos));
                $value = trim(substr($av, $equalsPos + 1));
            } else {
                // Otherwise:
                //
                // The attribute-name string consists of the entire cookie-av
                // string, and the attribute-value string is empty.
                $name = trim($av);

                // (Response-Interop specifies that if the cookie has no
                // `=<attribute-value>` portion, retain it as boolean `true`.)
                $value = true;
            }

            // Skip empty attribute names (e.g. from adjacent semicolons).
            if ($name === '') {
                continue;
            }

            $attributes[strtolower($name)] = $value;

            // 7.  Return to Step 1 of this algorithm.
        }

        /** @var response_cookie_attributes_array $attributes */
        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function composeResponseCookieString(array $cookie) : string
    {
        $setCookieString = $this->encode($cookie['name'])
            . '='
            . $this->encode($cookie['value']);

        foreach ($cookie['attributes'] as $attributeName => $attributeValue) {
            $setCookieString .= "; {$attributeName}";

            if (is_string($attributeValue)) {
                $setCookieString .= "={$attributeValue}";
            }
        }

        return $setCookieString;
    }

    protected function decode(string $string) : string
    {
        return urldecode($string);
    }

    protected function encode(string $string) : string
    {
        return urlencode($string);
    }
}
