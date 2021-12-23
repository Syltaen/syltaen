<?php

namespace Syltaen;

class SEO
{
    /**
     * Generate structured data for a product
     */
    public static function generateProductStructuredData($variation, $product)
    {
        add_filter("woocommerce_structured_data_product", function ($data, $wc_product) use ($variation, $product) {
            $data["gtin"] = $variation->getMeta("_ean");

            if ($brand = $product->pa_brand->getOne()) {
                $data["brand"] = [
                    "@type" => "Brand",
                    "name"  => $brand->name,
                    "url"   => $brand->url,
                ];
            }

            $data["category"] = $product->product_cat->getAncestors()->callEach()->getNames()->callEach()->join(" > ")->values()[0] ?? "";
            $data["color"]    = $variation->getAttribute("pa_color") ?: "";

            return $data;
        }, 10, 2);

        $sd = (new \WC_Structured_Data);
        $sd->generate_product_data($variation->wc);
        return $sd->get_data();
    }

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
     * Generate data to send to the data layer
     *
     * @param  [type] $product_id
     * @return void
     */
    public static function getProductDataLayer($product_id, $extra = [])
    {
        $product = get_post_type($product_id) == Variations::TYPE ? Variations::getItem($product_id) : Products::getItem($product_id);
        $parent  = get_post_type($product_id) == Variations::TYPE ? $product->parent : $product;

        return array_merge([
            "name"       => $product->post_title,
            "id"         => $product->getMeta("_sku"),
            "price"      => (float) $product->getMeta("_price"),
            "category"   => $parent->product_cat->getNames()->join(", "),
            "stocklevel" => $product->getMeta("_stock") ?: 0,
            "brand"      => $parent->pa_brand->getNames()->join(", "),
        ], $extra);
    }

    /**
     * Generate a share URL for the given social network
     *
     * @param  string         $network
     * @param  string         $url       The URL to share
     * @param  boolean|string $content   The text to share
     * @return string
     */
    public static function share($network, $url, $content = "", $link_array_target = "popup")
    {
        if (is_array($network)) {
            return array_filter(array_map(function ($network) use ($url, $content, $link_array_target) {
                return static::share($network, $url, $content, $link_array_target);
            }, $network));
        }

        $networks = [
            "Facebook"  => [
                "url"  => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url),
                "icon" => "<i class='fab fa-facebook-f'></i>",
            ],
            "Twitter"   => [
                "url"  => "https://twitter.com/intent/tweet?text={$content}%0A" . urlencode($url),
                "icon" => "<i class='fab fa-twitter'></i>",
            ],
            "Pinterest" => [
                "url"  => "https://www.pinterest.com/pin/create/button/?url=" . urlencode($url) . "&media=&description={$content}",
                "icon" => "<i class='fab fa-pinterest-p'></i>",
            ],
            "Mail"      => [
                "url"  => "mailto:?subject={$content}&body=" . urlencode($url),
                "icon" => "<i class='far fa-envelope'></i>",
            ],
        ];

        // Network not available
        if (empty($networks[$network])) {
            return false;
        }

        // Return only URL
        if (!$link_array_target) {
            return $networks[$network]["url"] ?? false;
        }

        // Return a complete array of title, url, target and icon
        return [
            "link" => [
                "title"  => $network,
                "url"    => $networks[$network]["url"],
                "target" => $link_array_target,
            ],
            "icon" => $networks[$network]["icon"],
        ];
    }
}