<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

abstract class ModelItem
{
    /**
     * Hold a reference to the model so that we can know how to retrieve fields
     *
     * @var Model
     */
    protected $model = false;


    /**
     * Expose each default value of the wp_object
     *
     * @param object $wp_object_or_id A full WP Object or just an ID.
     * If an ID is passed, functions will be very limited.
     * @param Model $model
     */
    public function __construct($wp_object_or_id, $model = false)
    {
        if (is_int($wp_object_or_id)) {
            $this->setID($wp_object_or_id);
        }

        if (is_object($wp_object_or_id)) foreach ($wp_object_or_id as $key=>$value) {
            $this->{$key} = $value;
        }

        $this->model = $model;

        // The model specify that all fields should be pre-fetched by default
        if ($this->model && $this->model->forceFetchFields) {
            $this->fetchAllFields();
        }
    }


    /**
     * When trying to access a property that is not already stored, use the model fields definition to proccess its value
     *
     * @param string $property
     * @return mixed
     */
    public function __get($field)
    {
        // Throw an error because the field was not defined in the index
        if (!$this->hasFieldInIndex($field)) {
            trigger_error(
                "\"$field\" was not found in \"".get_class($this->model)."\""
            );
        }

        // If the field is present in the field index, retrieve and cache its value
        $data = $this->getField($field);
        $this->{$data["key"]} = $data["value"];
        return $this->{$data["key"]};
    }

    /**
     * Check if a field is difined in the model fields index
     *
     * @return boolean
     */
    public function hasFieldInIndex($field)
    {
        return isset($this->model->fieldsIndex[$field]);
    }

    /**
     * Get a field value and property
     *
     * @param string $field
     * @return array
     */
    public function getField($field)
    {
        return Data::getAdvanced(
            $this->model->fieldsIndex[$field],
            $this->model->fields[$this->model->fieldsIndex[$field]],
            static::FIELD_PREFIX . $this->getID(),
            $this,
        );
    }

    /**
     * ID normalizer for all model resuts
     *
     * @return int
     */
    public function getID()
    {
        return $this->ID;
    }

    /**
     * ID setter for all model resuts
     *
     * @return int
     */
    public function setID($id)
    {
        $this->ID = $id;
    }

    /**
     * Pre-fetch all the fields specified in the model
     *
     * @return void
     */
    public  function fetchAllFields()
    {
        if (!$this->model) return false;

        foreach ($this->model->fieldsIndex as $field=>$key) {
            // Force the value fetching, erasing potential existing fields
            $data = $this->getField($field);
            $this->{$data["key"]} = $data["value"];
        }
    }

    /**
     * Update a single post
     *
     *
     * @param array $attrs Base attributes
     * @param array $fields ACF data
     * @param array $tax Taxonomies
     * @param bool $merge Wether to merge or set the values
     * @return self
     */
    public function update($attrs = [], $fields = [], $tax = [], $merge = false)
    {
        // Default attributes
        if (!empty($attrs)) {
            $this->updateAttrs($attrs, $merge);
        }

        // Custom fields
        if (!empty($fields)) {
            $this->updateFields($fields, $merge);
        }

        // Taxonomy
        if (!empty($tax)) {
            $this->updateTaxonomies($tax, $merge);
        }

        return $this;
    }

    /**
     * Parse attributes to be saved
     *
     * @param array $attrs
     * @param bool $merge
     * @return array
     */
    public function parseAttributes($attrs, $merge)
    {
        if (empty($attrs)) return false;

        if ($merge) foreach ($attrs as $attr=>$value) {
            if (isset($this->$attr) && !empty($this->$attr)) {
                unset($attrs[$attr]);
            }
        }

        foreach ($attrs as &$attr) {
            if (is_callable($attr) && !is_string($attr)) $attr = $attr($this);
        }

        return $attrs;
    }


    /**
     * Update a result custom fields
     *
     * @param array $fields
     * @param bool $merge Only update empty fields
     * @return void
     */
    public  function updateFields($fields, $merge = false)
    {
        if (empty($fields)) return false;

        foreach ($fields as $key=>$value) {
            if (is_callable($value) && !is_string($value)) $value = $value($this);
            Data::update($key, $value, static::FIELD_PREFIX.$this->getID(), $merge);
        }
    }

    /**
     * Update the itemmeta directrly (does not create a duplicate metakey for ACF)
     *
     * @param array $meta
     * @return void
     */
    public function updateMeta($meta)
    {
        if (empty($meta)) return;

        foreach ($meta as $key=>$value) {
            if (is_callable($value) && !is_string($value)) $value = $value($this);
            $this->setMeta($key, $value);
        }
    }


    // ==================================================
    // > DEBUG
    // ==================================================
    /**
     * Dump the result of a model with all its fields loaded
     *
     * @return void
     */
    public function json()
    {
        $this->fetchAllFields();
        wp_send_json($this);
    }

    /**
     * Dump the result of a model with all its fields loaded
     *
     * @return void
     */
    public function log()
    {
        $this->fetchAllFields();
        Controller::log($this);
    }
}