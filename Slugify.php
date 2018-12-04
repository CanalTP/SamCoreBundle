<?php

namespace CanalTP\SamCoreBundle;

class Slugify
{
    public static function format($text, $separator = '-')
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', $separator, $text);

        // trim
        $text = trim($text, $separator);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}
