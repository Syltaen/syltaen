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


    // =============================================================================
    // > LANGUAGES
    // =============================================================================
    /**
     * Get the list of langs
     *
     * @return array
     */
    public static function langs()
    {
        if (function_exists("pll_languages_list")) return pll_languages_list();
        return [static::defaultLang()];
    }

    /**
     * Get the current lang
     *
     * @return string
     */
    public static function lang()
    {
        if (function_exists("pll_current_language")) return pll_current_language();
        return static::defaultLang();
    }


   /**
     * Get the default lang
     *
     * @return string
     */
    public static function defaultLang()
    {
        if (function_exists("pll_default_language")) return pll_default_language();
        return "fr";
    }

    // =============================================================================
    // > TOOLS
    // =============================================================================
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



    // =============================================================================
    // > CRON
    // =============================================================================

    /**
     * Plan an event to occure once
     *
     * @return void
     */
    public static function planEvent($hook, $time = "+ 5 minutes")
    {
        if (!wp_next_scheduled($hook)) {
            wp_schedule_single_event(strtotime($time), $hook);
        }
    }

    /**
     * Add a cron task if it does not already exists
     */
    public static function addCron($hook, $recurrence, $start = false)
    {
        $start = $start ?: Time::current();
        $start = is_int($start) ? $start : Time::fromString($start);

        if (!wp_next_scheduled($hook)) {
            wp_schedule_event($start, $recurrence, $hook);
        }
    }
}