<?php

namespace Syltaen;

class Filters extends FormProcessor
{
    const METHOD = "GET";

    /**
     * Common rendering context
     *
     * @return void
     */
    public function get()
    {
        // Register the form action page
        $this->data["action"] = Pagination::getBaseURL() . (isset($this->controller->content) && $this->controller->content->getAnchor() ? $this->controller->content->getAnchor() : "#filters");
    }

    /**
     * Empty the payload to skip all form validation/processing
     *
     * @return void
     */
    public function setup()
    {
        $this->addPrefill((array) $this->payload);
        $this->payload = set();
    }

    /**
     * Keep parameters in the form as hidden fields
     *
     * @param  array  $keys
     * @return self
     */
    public function keepParameters($keys)
    {
        foreach ($keys as $key) {
            $this->addHiddenField($key);
        }

        return $this;
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
    private function addField($type, $name, $label = "", $attrs = [], $filter_callback = false, $default_value = false)
    {
        // Init the list if not defined
        $this->data["fields"] = $this->data["fields"] ?? [];

        // Register the field
        $this->data["fields"][$name] = [
            "type"  => $type,
            "label" => $label,
            "attrs" => $attrs,
        ];

        // Set default value is the is none
        if (empty($this->payload[$name])) {
            $this->payload[$name] = $default_value;
        }

        // Apply the callback if filter has value
        $this->applyFilter($filter_callback, $name);
        return $this;
    }

    /**
     * Shortcut to add a choice field : select, radio, checkbox, ...
     *
     * @param  string $name            The field name
     * @param  string $label           The field label
     * @param  array  $options         The field options
     * @param  mixed  $filter_callback The callback to use to filter the model
     * @param  mixed  $default_value   The default value
     * @return self
     */
    private function addChoiceField($type, $name, $label, $options, $attrs = [], $filter_callback = "meta", $default_value = false)
    {
        if (empty($options)) {
            return $this;
        }

        // Register options
        $this->addOptions([$name => $options]);

        // Register field
        return $this->addField($type, $name, $label, array_merge($attrs, ["autosubmit" => true]), $filter_callback, $default_value);
    }

    /**
     * Shortcut to add a search field
     *
     * @param $name
     */
    public function addSearch($label = null, $placeholder = "", $name = "search")
    {
        return $this->addField("search", $name, $label !== null ? $label : __("Search", "syltaen"), [
            "placeholder" => $placeholder,
        ], "search");
    }

    /**
     * Shortcut to add a search field
     *
     * @param $name
     */
    public function addHiddenField($name)
    {
        return $this->addField("hidden", $name, false);
    }

    /**
     * Shortcut to add a select field
     * @see static::addChoiceField
     *
     * @return self
     */
    public function addSelect($name, $label, $options, $filter_callback = "meta", $default_value = false)
    {
        return $this->addChoiceField("select", $name, $label, $options, ["append" => true], $filter_callback, $default_value);
    }

    /**
     * Shortcut to add a select field
     * @see static::addChoiceField
     *
     * @return self
     */
    public function addRadio($name, $label, $options, $filter_callback = "meta", $default_value = false)
    {
        return $this->addChoiceField("radio", $name, $label, $options, [], $filter_callback, $default_value);
    }

    /**
     * Shortcut to add a select field for a taxonomy
     *
     * @return self
     */
    public function addSelectTaxonomy($taxonomy, $all_option = true, $default_value = "*", $show_label = true)
    {
        return $this->addSelect(
            $taxonomy::SLUG,
            $show_label ? $taxonomy::getName(true) : false,
            $taxonomy->getAsOptions($all_option),
            "tax",
            $default_value
        );
    }

    /**
     * Shortcut to add a radio field for a taxonomy
     *
     * @return self
     */
    public function addRadioTaxonomy($taxonomy, $all_option = true, $default_value = "*", $show_label = true)
    {
        return $this->addRadio(
            $taxonomy::SLUG,
            $show_label ? $taxonomy::getName(true) : false,
            $taxonomy->getAsOptions($all_option),
            "tax",
            $default_value
        );
    }

    // =============================================================================
    // > FILTERS
    // =============================================================================
    /**
     * @param $filter_callback
     * @param $value
     */
    public function applyFilter($filter_callback, $name)
    {
        $value = $this->payload[$name] ?? false;

        if (!$filter_callback) {
            return false;
        }

        // Custom filter with callback function
        if (!is_string($filter_callback) && is_callable($filter_callback)) {
            return $filter_callback($this->controller->model, $value);
        }

        // Generic filter
        switch ($filter_callback) {
            case "tax":
                return $value == "*" ? $this->controller->model : $this->controller->model->tax($name, $value);
            case "meta":
                return $this->controller->model->meta($name, $value);
            case "search":
                return $this->controller->model->search($value);
            case "status":
                return $this->controller->model->status($value);
            case "order":
                return $this->controller->model->order($value, "DESC");
        }
    }
}
