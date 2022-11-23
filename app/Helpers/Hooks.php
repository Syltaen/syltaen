<?php

namespace Syltaen;

abstract class Hooks
{
    /**
     * Register a hook
     *
     * @param  string|array $hooks
     * @param  callable     $callback
     * @return void
     */
    public static function add($hooks, $callback, $priority = 10, $accepted_args = 1)
    {
        foreach ((array) $hooks as $index => $hook) {
            add_filter(
                $hook,
                $callback,
                (int) (is_array($priority) ? $priority[$index] : $priority),
                (int) (is_array($accepted_args) ? $accepted_args[$index] : $accepted_args)
            );
        }
    }

    /**
     * Call an action hook by its name
     *
     * @param  string $name
     * @return void
     */
    public static function exec($name)
    {
        do_action($name);
    }

    /**
     * Find a hook callback by its function name and remove it
     *
     * @return void
     */
    public static function findAndRemove($hook, $function_name, $trigger = "init")
    {
        add_action($trigger, function () use ($hook, $function_name) {
            global $wp_filter;
            if (empty($wp_filter[$hook])) {
                return false;
            }

            foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
                foreach (array_keys($callbacks) as $callback) {
                    if (strpos($callback, $function_name)) {
                        remove_filter($hook, $callback, $priority);
                    }
                }
            }
        });
    }

    /**
     * List all callback for a specific hook
     *
     * @return void
     */
    function list($hook) {
        global $wp_filter;
        return $wp_filter[$hook];
    }

    // =============================================================================
    // > AJAX
    // =============================================================================
    /**
     * Register a new ajax hook
     *
     * @param  string   $name       The hook name
     * @param  callable $callback   The function to run
     * @param  boolean  $private
     * @return void
     */
    public static function ajax($name, $callback = false, $private = false)
    {
        // No callback : execute ajax
        if (!$callback) {
            return self::exec("wp_ajax_" . $name);
        }

        // Else : Register hook
        add_action("wp_ajax_" . $name, $callback);
        if (!$private) {
            add_action("wp_ajax_nopriv_" . $name, $callback);
        }
    }

    /**
     * Add the different hooks used to generate AJAX select options
     *
     * @param  string   $name
     * @param  callable $model
     * @param  callable $result_format
     * @return void
     */
    public static function addSelectOptions($name, $model, $result_format, $add_label = false)
    {
        add_filter("syltaen_select_{$name}_model", $model);
        add_filter("syltaen_select_{$name}_format", $result_format);

        add_filter("syltaen_select_{$name}_options", function ($value = false) use ($name, $add_label) {
            $model = apply_filters("syltaen_select_{$name}_model", false);

            // Restrict model to a specific value to limit memory usage
            if ($value) {
                $model->is($value);
            }

            $results = (array) $model->map(function ($item) use ($name) {
                return apply_filters("syltaen_select_{$name}_format", $item);
            });

            if ($add_label) {
                $results[] = [
                    "wrap" => "add",
                    "id"   => "new",
                    "text" => $add_label,
                ];
            }

            // Wrap text
            $results = array_map(function ($item) {
                return [
                    "id"    => $item["id"],
                    "title" => strip_tags(str_replace("<br>", "\n", $item["text"])),
                    "text"  => !empty($item["wrap"]) ? "<div class='select2-advancedoption--{$item['wrap']}'>" . $item["text"] . "</div>" : $item["text"],
                ];
            }, $results);

            return $results;
        });

        static::ajax("options_{$name}", function () use ($name) {
            wp_send_json([
                "results" => apply_filters("syltaen_select_{$name}_options", [])
            ]);
        });
    }

    /**
     * Get the AJAX options for specific field
     *
     * @param  string $name
     * @return array  of options
     */
    public static function getSelectOptions($name, $value = false)
    {
        return apply_filters("syltaen_select_{$name}_options", [$value]);
    }
}