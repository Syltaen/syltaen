<?php

namespace Syltaen;

abstract class Text
{
    /**
     * Wrap the first word of a stirng intro tags
     *
     * @param [type] $string
     * @param string $start_tag
     * @param string $end_tag
     * @return void
     */
    public static function wrapFirstWord($string, $start_tag = "<strong>", $end_tag = "</strong>")
    {
        return preg_replace('/(?<=\>)\b\w*\b|^\w*\b/', $start_tag.'$0'.$end_tag, $string);
    }


    /**
     * Get a random string
     *
     * @param integer $length
     * @param string $characters
     * @return string
     */
    public static function getRandomString($length = 8, $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ")
    {
        $string = "";
        for ($i = 0; $i < $length; $i++) $string .= $characters[rand(0, strlen($characters) - 1)];
        return $string;
    }
}