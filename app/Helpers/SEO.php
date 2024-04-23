<?php

namespace Syltaen;

class SEO
{
    /**
     * Set the meta description if it was not provided
     *
     * @return void
     */
    public static function setDefaultDescription($default_description, $forced = false)
    {
        add_filter("wpseo_metadesc", function ($used_description) use ($default_description, $forced) {
            if ($used_description && !$forced) {
                return $used_description;
            }

            if ($default_description) {
                return wp_trim_words(html_entity_decode($default_description), 55);
            }

            return $used_description;
        }, 100, 1);
    }

    /**
     * Generate a share URL for the given social network
     *
     * @param  string         $network
     * @param  string         $url       The URL to share
     * @param  boolean|string $content   The text to share
     * @return string
     */
    public static function share($networks, $url, $content = "", $link_array_target = "popup")
    {
        return set([
            "Facebook"  => [
                "url"  => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url),
                "icon" => ["fab", "&#xf39e;"], // facebook-f
            ],
            "Twitter"   => [
                "url"  => "https://twitter.com/intent/tweet?text={$content}%0A" . urlencode($url),
                "icon" => ["fab", "&#xf099;"], // twitter
            ],
            "Pinterest" => [
                "url"  => "https://www.pinterest.com/pin/create/button/?url=" . urlencode($url) . "&media=&description={$content}",
                "icon" => ["fab", "&#xf231;"], // pinterest-p
            ],
            "LinkedIn"  => [
                "url"  => "http://www.linkedin.com/shareArticle?mini=true&url=" . urlencode($url),
                "icon" => ["fab", "&#xf0e1;"], // linkedin-in
            ],
            "Mail"      => [
                "url"  => "mailto:?subject={$content}&body=" . urlencode($url),
                "icon" => ["far", "&#xf0e0;"], // envelope
            ],
        ])->keepKeys((array) $networks)->mapAssoc(function ($name, $network) use ($link_array_target) {
            return [$name => [
                "link" => [
                    "title"  => $name,
                    "url"    => $network["url"],
                    "target" => $link_array_target,
                ],
                "icon" => (object) [
                    "prefix"  => $network["icon"][0],
                    "unicode" => $network["icon"][1],
                ],
            ]];
        });
    }
}