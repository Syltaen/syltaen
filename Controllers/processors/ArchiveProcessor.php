<?php

namespace Syltaen;

class ArchiveProcessor extends DataProcessor
{
    /**
     * Archives for News
     *
     * @param  array  $c Local context
     * @return void
     */
    public function news(&$c)
    {
        $model = new News;

        // Filter example : ?type=example-category-123
        $this->addFilter($c, "type", "Type de news", (new NewsTaxonomy)->getAsOptions(), function ($value) use ($model) {
            $model->tax(NewsTaxonomy::SLUG, $value);
        });

        $this->paginate($c, $model, 4);
    }

    // =============================================================================
    // > TOOLS
    // =============================================================================
    /**
     * Create a pagination from a model
     *
     * @param  array          $c          Local context
     * @param  \Syltaen\Model $model
     * @param  int            $perpage
     * @return void
     */
    private function paginate(&$c, $model, $perpage = 6)
    {
        $pagination  = (new Pagination($model, $perpage));
        $c["walker"] = $pagination->walker(null, "pagination--simple")->data;
        $c["posts"]  = $pagination->posts();
    }

    /**
     * Register a new list filter
     *
     * @param  array    $c             The local render context
     * @param  string   @name          The input/data name
     * @param  string   @label         THe label of the filter
     * @param  array    $options       The different available options in an associative array of $value=>$label
     * @param  callable $callback      The callback used to filter items of the model
     * @param  mixed    $default_value The default value
     * @return void
     */
    public function addFilter(&$c, $name, $label, $options, $filter_callback, $default_value = false)
    {
        if (empty($options)) {
            return false;
        }

        // Act on this page without pagination
        $c["filters_action"] = $c["filters_action"] ?? Pagination::getBaseURL();

        $value = $_GET[$name] ?? $default_value;

        // Register filter in the list
        $c["filters"]        = $c["filters"] ?? [];
        $c["filters"][$name] = [
            "name"    => $label,
            "value"   => $value,
            "options" => $options,
        ];

        // Apply the callback if filter has value
        if ($value && is_callable($filter_callback)) {
            return $filter_callback($value);
        }
    }

    // =============================================================================
    // > METHOD ROUTING
    // =============================================================================
    /**
     * Process a single archive
     *
     * @param  array  $content
     * @return void
     */
    public function process($archive)
    {
        // Run the correct mehtod by looking at the archive content type
        switch ($archive["type"]) {
            // Add custom layout-method routes here
            // Ex:
            // case "type-of-archive":
            //     static::nameOfTheMethod($archive);
            //     break;

            // By default : use the type as a method name
            default:
                $method = $archive["type"];
                if (method_exists(static::class, $method)) {
                    $this->{$method}($archive);
                } else {
                    throw new \Exception(__CLASS__ . " does not implement {$method}(). Please add it to process this archive type.", 1);
                }
        }

        return $archive;
    }
}