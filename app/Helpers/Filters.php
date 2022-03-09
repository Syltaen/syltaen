<?php

namespace Syltaen;

class Filters
{
    /**
     * The model to filter
     *
     * @var \Syltaen\Model
     */
    public $model;

    /**
     * Generate a new filter form and update the model based on the selected values
     *
     * @param Posts            $model   The model to filter
     * @param ContentProcessor $archive A reference to the content layout processor
     */
    public function __construct($model, $archive)
    {
        $this->model   = $model;
        $this->archive = $archive;
        $this->data    = [];

        // Register the form action page
        $this->data["action"] = Pagination::getBaseURL() . $this->archive->getAnchor();
    }


    // =============================================================================
    // > FIELDS
    // =============================================================================
    /**
     * Add a field to the form
     *
     * @param  $options
     * @param  $filter_callback
     * @return self
     */
    public function addField($options, $filter_callback, $default_value = false)
    {
        // Init the list if not defined
        $this->data["fields"] = $this->data["fields"] ?? [];

        // Get the field value from the parameters
        $options["value"] = $_GET[$options["name"]] ?? $default_value;

        // Register the field
        $this->data["fields"][] = $options;

        // Apply the callback if filter has value
        $this->applyFilter($filter_callback, $options["name"], $options["value"]);
        return $this;
    }

    /**
     * Shortcut to add a search field
     *
     * @param $name
     */
    public function addSearch($name = "s")
    {
        return $this->addField([
            "type"  => "text",
            "name"  => $name,
            "label" => __("Rechercher", "syltaen"),
        ], "search");
    }

    /**
     * Shortcut to add a select field
     *
     * @param  string $name            The field name
     * @param  string $label           The field label
     * @param  array  $options         The field options
     * @param  mixed  $filter_callback The callback to use to filter the model
     * @param  mixed  $default_value   The default value
     * @return self
     */
    public function addSelect($name, $label, $options, $filter_callback = "meta", $default_value = false)
    {
        if (empty($options)) {
            return;
        }

        return $this->addField([
            "type"    => "select",
            "name"    => $name,
            "label"   => $label,
            "options" => $options,
        ], $filter_callback, $default_value);
    }

    /**
     * Shortcut to add a select field for a taxonomy
     *
     * @return self
     */
    public function addSelectTaxonomy($taxonomy)
    {
        return $this->addSelect(
            $taxonomy::SLUG,
            $taxonomy::getName(true),
            $taxonomy->getAsOptions(true),
            "tax",
            "*"
        );
    }

    /**
     * @param $filter_callback
     * @param $value
     */
    public function applyFilter($filter_callback, $name, $value)
    {
        // Custom filter with callback function
        if (!is_string($filter_callback) && is_callable($filter_callback)) {
            return $filter_callback($this->model, $value);
        }

        // Generic filter
        switch ($filter_callback) {
            case "tax":
                return $value == "*" ? $this->model : $this->model->tax($name, $value);
            case "meta":
                return $this->model->meta($value);
            case "search":
                return $this->model->search($value);
            case "order":
                return $this->model->order($value, "DESC");
        }
    }
}