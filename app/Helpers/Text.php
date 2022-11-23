<?php

namespace Syltaen;

abstract class Text
{
    /**
     * Caputre what is printed by the callback funcion and return it
     *
     * @return string
     */
    public static function output($callback)
    {
        ob_start();
        $callback();
        return ob_get_clean();
    }

    /**
     * @param $string
     * @param $start_tag
     * @param $end_tag
     */
    public static function wrapFirstWord($string, $start_tag = "<strong>", $end_tag = "</strong>")
    {
        return preg_replace('/(?<=\>)\b\w*\b|^\w*\b/', $start_tag . '$0' . $end_tag, $string);
    }

    /**
     * Convert camel case to snake case
     *
     * @return string
     */
    public static function camelToSnake($string)
    {
        return preg_replace_callback("/[A-Z]/", function ($letter) {
            return "_" . strtolower($letter[0]);
        }, $string);
    }

    /**
     * Get a random string
     *
     * @param  integer  $length
     * @param  string   $characters
     * @return string
     */
    public static function getRandomString($length = 8, $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ")
    {
        $string = "";
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    /**
     * Decode a string if it's a valid JSON
     *
     * @return object|array|string
     */
    public static function maybeJsonDecode($string, $assoc_array = false)
    {
        if (!is_string($string)) {
            return $string;
        }

        $json = json_decode(stripslashes($string), $assoc_array);
        if ($json !== null) {
            return $json;
        }

        return $string;
    }

    /**
     * Remove all line breaks from the text
     *
     * @return string
     */
    public static function removeLineBreaks($text)
    {
        $text = str_replace("\n", "", $text);
        $text = str_replace("\r", "", $text);
        return $text;
    }

    /**
     * Escape all line breaks from the text
     *
     * @return string
     */
    public static function escapeLineBreaks($text)
    {
        $text = str_replace("\n", '\n', $text);
        $text = str_replace("\r", '\r', $text);
        return $text;
    }

    /**
     * Check that a haystack starts with a needle
     *
     * @param string $needle
     * @param string $haystack
     */
    public static function startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }

    /**
     * Check that a haystack contains a needle
     *
     * @param string $needle
     * @param string $haystack
     */
    public static function contains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }

    /**
     * namespace a class name if it is not already
     *
     * @param  string   $class
     * @return string
     */
    public static function namespaced($class)
    {
        if (static::startsWith($class, "Syltaen")) {
            return $class;
        }

        return "\\Syltaen\\$class";
    }

    /**
     * Return the FA unicode of an icon
     *
     * @return string
     */
    public static function fa($icon)
    {
        $list = file_get_contents(Files::path("styles/assets/vendors/fontawesome/_fa-icons.scss"));
        preg_match("/{$icon}\: .(f[0-9a-z]+)/", $list, $matches);
        return !empty($matches[1]) ? "&#x" . $matches[1] . ";" : "";
    }

    /**
     * Return a formated address from its parts
     *
     * @param  array    $parts
     * @return string
     */
    public static function address($parts)
    {
        return implode("<br>", [
            ($parts["street"] ?? false),
            implode(" - ", [($parts["zip"] ?? false), ($parts["city"] ?? false)]),
            ($parts["country"] ?? false),
        ]);
    }
}