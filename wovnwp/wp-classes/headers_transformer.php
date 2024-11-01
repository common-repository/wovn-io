<?php
namespace Wovnio\Wovnwp\WpClasses;

class HeadersTransformer
{
    public function transform_and_apply_api_headers($headers)
    {
        $api_headers_transform_rules = array(
            '/^X-Cache:/' => 'X-Wovn-Cache:',
            '/^X-Cache-Hits:/' => 'X-Wovn-Cache-Hits:',
            '/^X-Wovn-Surrogate-Key:/' => 'X-Wovn-Surrogate-Key:'
        );

        foreach ($headers as $header) {
            foreach ($api_headers_transform_rules as $rule => $transform) {
                if (preg_match($rule, $header)) {
                    header(preg_replace($rule, $transform, $header));
                    break;
                }
            }
        }
    }
}
