<?php

namespace Syltaen;

abstract class Text
{
    public static function wrapFirstWord($string, $start_tag = "<strong>", $end_tag = "</strong>")
    {
        return preg_replace('/(?<=\>)\b\w*\b|^\w*\b/', $start_tag.'$0'.$end_tag, $string);
    }
}