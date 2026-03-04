<?php

namespace Anibalealvarezs\OAuthV1\Helpers;

class Helper
{
    /**
     * @param mixed $input
     * @return array|string
     */
    public static function urlencode_rfc3986(mixed $input): array|string
    {
        if (is_array($input)) {
            return array_map([self::class, 'urlencode_rfc3986'], $input);
        } elseif (is_scalar($input)) {
            return str_replace(
                '+',
                ' ',
                str_replace('%7E', '~', rawurlencode($input))
            );
        } else {
            return '';
        }
    }

    /**
     * @param array $params
     * @return string
     */
    public static function build_http_query(array $params): string
    {
        if (!$params) {
            return '';
        }

        // Urlencode both keys and values
        $keys = Helper::urlencode_rfc3986(array_keys($params));
        $values = Helper::urlencode_rfc3986(array_values($params));
        $params = array_combine($keys, $values);

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs = array();
        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                // June 12th, 2010 - changed to sort because of issue 164 by hidetaka
                sort($value, SORT_STRING);
                foreach ($value as $duplicate_value) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }
        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        return implode('&', $pairs);
    }

    /**
     * @param string $url
     * @return string
     */
    public static function getNormalizedUrl(string $url): string
    {
        $url_parts = parse_url($url);
        $scheme = strtolower($url_parts['scheme']);
        $host = strtolower($url_parts['host']);
        $port = isset($url_parts['port']) ? intval($url_parts['port']) : ($scheme === 'https' ? 443 : 80);
        $path = $url_parts['path'] ?? '';
        return $scheme . '://' . $host . ($port !== 80 && $port !== 443 ? ':' . $port : '') . $path;
    }

    /**
     * @return string
     */
    public static function generateNonce(): string
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 20);
    }

}
