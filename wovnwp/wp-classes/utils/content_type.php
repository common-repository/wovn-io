<?php
namespace Wovnio\Wovnwp\WpClasses\Utils;

use finfo;

class ContentType
{
    public static function isHtml($buffer)
    {
        $finfo = new finfo(FILEINFO_MIME);
        $contentType = $finfo->buffer($buffer);

        if ($contentType) {
            if (preg_match('/html|php/', strtolower($contentType))) {
                return true;
            } elseif (preg_match('/xml/', strtolower($contentType))) {
                return false;
            } elseif (preg_match('/text/', strtolower($contentType))) {
                return $buffer != strip_tags($buffer);
            }
            // finfo can still return octet-stream even if it is really HTML but contains certain characters
            // So we should still fallback to a buffer check
        }
        return $buffer != strip_tags($buffer);
    }
}
