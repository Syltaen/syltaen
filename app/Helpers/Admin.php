<?php

namespace Syltaen;

class Admin
{

    /**
     * Add a list of filters above a list of posts
     *
     * @param  string $name
     * @param  string $slug
     * @param  array  $filters
     * @return void
     */
    public static function addFiltersList($screens, $name, $slug, $filters, $filter_callback = false)
    {
        if (!is_admin()) {
            return;
        }

        $is_multiple = preg_match("/\[\]/", $slug);
        $slug        = $is_multiple ? str_replace("[]", "", $slug) : $slug;

        // ========== LISTING ========== //
        add_action("admin_head", function () use ($screens, $name, $slug, $filters, $is_multiple) {
            global $current_screen;
            if (empty($current_screen) || !in_array($current_screen->id, (array) $screens)) {
                return;
            }

            $filters = is_callable($filters) ? $filters() : $filters;
            $filters = array_merge(["" => "Tous"], $filters);

            $list = implode(" | ", array_map(function ($value, $label) use ($slug, $filters, $is_multiple) {
                $params          = $_GET;
                $params["paged"] = 1;

                // Single
                if (!$is_multiple) {
                    $current       = !empty($_GET[$slug]) && $_GET[$slug] == $value ? 'class=\"current\"' : '';
                    $current       = !$value && empty($_GET[$slug]) ? 'class=\"current\"' : $current;
                    $params[$slug] = $value;
                    return '<li><a ' . $current . ' href=\"' . Route::getFullUrl($params) . '\" >' . $label . '</a></li>';
                }

                // Multiple - Clear all
                if (!$value) {
                    $current       = empty($_GET[$slug]) ? 'class=\"current\"' : '';
                    $params[$slug] = [];
                    return '<li><a ' . $current . ' href=\"' . Route::getFullUrl($params) . '\" >' . $label . '</a></li>';
                }

                // Multiple - Add
                $current       = !empty($_GET[$slug]) && in_array($value, $_GET[$slug]) ? 'class=\"current\"' : '';
                $params[$slug] = array_merge($_GET[$slug] ?? [], [$value]);
                $add           = '<a ' . $current . ' href=\"' . Route::getFullUrl($params) . '\" >' . $label . '</a>';

                // Multiple - Remove
                $removed_params        = $params;
                $removed_params[$slug] = array_values(array_diff($removed_params[$slug], [$value]));
                $remove                = !$current ? '' : '<a href=\"' . Route::getFullUrl($removed_params) . '\">[x]</a>';

                return "<li>$add$remove</li>";

            }, array_keys($filters), array_values($filters)));

            echo "<script>jQuery(function ($) {";
            echo "$(\".subsubsub\").last().after(\"<ul class='subsubsub' style='clear: both; margin-top: 0;'><li>$name :&nbsp;</li>$list</ul>\")";
            echo "});</script>";
        });

        // ========== FILTER ========== //
        if (empty($filter_callback)) {
            return;
        }

        if (empty($_GET[$slug])) {
            return;
        }

        add_filter("posts_clauses", function ($query) use ($screens, $name, $slug, $filters, $filter_callback, $is_multiple) {
            global $wpdb, $current_screen;
            if (empty($current_screen) || !in_array($current_screen->id, (array) $screens)) {
                return;
            }

            $query["distinct"] = "DISTINCT";
            $query["join"] .= " LEFT JOIN {$wpdb->postmeta} postmeta ON (postmeta.post_id = {$wpdb->posts}.ID)";

            return $filter_callback($wpdb, $query, $_GET[$slug], $slug, $filters);
        });
    }

    /**
     * Add a list of filters above a list of posts for a specific taxonomy
     *
     * @param  string        $screen
     * @param  TaxonomyModel $taxonomy
     * @return void
     */
    public static function addFiltersListTaxonomy($screen, $taxonomy)
    {
        add_action("init", function () use ($screen, $taxonomy) {
            $terms = [];

            foreach (get_terms(["taxonomy" => $taxonomy::SLUG]) as $term) {
                $terms[$term->slug] = $term->name;
            }

            static::addFiltersList($screen, $taxonomy::NAME, $taxonomy::SLUG, $terms);
        });
    }

    /**
     * Add a custom menu category/item
     *
     * @return void
     */
    public static function addCustomMenuItem($name, $render_callback = false)
    {
        /**
         * Register the custom nav menu item
         */
        add_filter("syltaen_custom_nav_menu_items", function ($items) use ($name, $render_callback) {
            $items[$name] = $render_callback;
            return $items;
        });

        /**
         * Render the custom nav menu item, once
         */
        if (!apply_filters("syltaen_custom_nav_menu_items_rendered", false)) {
            add_action("admin_init", function () use ($name) {
                add_meta_box("syltaen_custom_nav_link", __("Custom items"), function () use ($name) {
                    global $_nav_menu_placeholder, $nav_menu_selected_id;?>
                    <div id="posttype-syltaen_custom" class="posttypediv">
                        <div id="tabs-panel-syltaen_custom" class="tabs-panel tabs-panel-active">
                            <ul id="syltaen_custom-checklist" class="categorychecklist form-no-clear">
                                <?php foreach (apply_filters("syltaen_custom_nav_menu_items", []) as $name => $callback): ?>
                                    <?php $_nav_menu_placeholder = $_nav_menu_placeholder < 0 ? $_nav_menu_placeholder - 1 : -1;?>
                                    <li>
                                        <label class="menu-item-title">
                                            <input type="checkbox" class="menu-item-checkbox" name="menu-item[<?=(int) $_nav_menu_placeholder;?>][menu-item-object-id]" value="-1"> <?=$name?>
                                        </label>
                                        <input type="hidden" class="menu-item-type" name="menu-item[<?=(int) $_nav_menu_placeholder;?>][menu-item-type]" value="custom">
                                        <input type="hidden" class="menu-item-title" name="menu-item[<?=(int) $_nav_menu_placeholder;?>][menu-item-title]" value="<?=$name?>">
                                        <!-- <input type="hidden" class="menu-item-url" name="menu-item[<?=(int) $_nav_menu_placeholder;?>][menu-item-url]" value="#"> -->
                                    </li>
                                <?php endforeach;?>
                            </ul>
                        </div>
                        <p class="button-controls">
                            <span class="add-to-menu">
                                <input type="submit" <?php disabled($nav_menu_selected_id, 0);?> class="button-secondary submit-add-to-menu right" value="<?=__("Add to Menu");?>" name="add-post-type-menu-item" id="submit-posttype-syltaen_custom">
                                <span class="spinner"></span>
                            </span>
                        </p>
                    </div>
                <?php }, "nav-menus", "side", "low");
            });
        }
        add_filter("syltaen_custom_nav_menu_items_rendered", "__return_true");

        /**
         * Render the submenu
         */
        if ($render_callback) {
            add_filter("wp_nav_menu_items", function ($items, $args) use ($name, $render_callback) {
                return str_replace("<a>$name</a>", $render_callback($items), $items);
            }, 10, 2);
        }
    }
}