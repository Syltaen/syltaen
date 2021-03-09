<?php

namespace Syltaen;

abstract class App
{
    private static $config = false;

    /**
     * Get a config item
     *
     * @return void
     */
    public static function config($key)
    {
        // Load config file once
        if (!static::$config) {
            static::$config = include Files::path("app/config/_config.php");
        }

        return static::$config[$key];
    }


    /**
     * Generate a share URL for the given social network
     *
     * @param string $network
     * @param string $url  The URL to share
     * @param boolean|string $content The text to share
     * @return string
     */
    public static function share($network, $url, $content = "", $link_array_target = "popup")
    {
        if (is_array($network)) return array_filter(array_map(function ($network) use ($url, $content, $link_array_target) {
            return App::share($network, $url, $content, $link_array_target);
        }, $network));

        $networks = [
            "Facebook"  => [
                "url"  => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url),
                "icon" => "<i class='fab fa-facebook-f'></i>"
            ],
            "Twitter"   => [
                "url"  => "https://twitter.com/intent/tweet?text={$content}%0A" . urlencode($url),
                "icon" => "<i class='fab fa-twitter'></i>"
            ],
            "Pinterest" => [
                "url"  => "https://www.pinterest.com/pin/create/button/?url=".urlencode($url)."&media=&description={$content}",
                "icon" => "<i class='fab fa-pinterest-p'></i>"
            ],
            "Mail" => [
                "url" =>  "mailto:?subject=$content&body=$url",
                "icon" => "<i class='far fa-envelope'></i>"
            ]
        ];

        // Network not available
        if (empty($networks[$network])) return false;

        // Return only URL
        if (!$link_array_target) return $networks[$network]["url"] ?? false;

        // Return a complete array of title, url, target and icon
        return [
            "link" => [
                "title"  => $network,
                "url"    => $networks[$network]["url"],
                "target" => $link_array_target
            ],
            "icon" => $networks[$network]["icon"]
        ];
    }


    /**
     * Plan an event to occure once
     *
     * @return void
     */
    public static function planEvent($hook, $time = "+ 5 minutes")
    {
        if (!wp_next_scheduled("product_discounts_cache")) {
            wp_schedule_single_event(strtotime($time), "product_discounts_cache");
        }
    }
}