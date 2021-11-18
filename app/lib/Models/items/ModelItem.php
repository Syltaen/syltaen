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
     * @param object $wp_object
     * @param Model $model
     */
    public function __construct($wp_object, $model) {
        foreach ($wp_object as $key=>$value) {
            $this->{$key} = $value;
        }

        $this->model = $model;

        // The model specify that all fields should be pre-fetched by default
        if ($this->model->forceFetchFields) {
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
     * Pre-fetch all the fields specified in the model
     *
     * @return void
     */
    public  function fetchAllFields()
    {
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
     * Update a result base attributes
     *
     * @param array $attrs
     * @param bool $merge Only update empty attrs
     * @return void
     */
    public function updateAttrs($attrs, $merge = false)
    {
        $attrs = static::parseAttrs($attrs, $merge);
        if (empty($attrs)) return false;
        static::setAttrs($this->getID(), $attrs);
    }


    /**
     * Parse a list of attributes
     *
     * @param array $attrs
     * @param bool $merge
     * @return array
     */
    public function parseAttrs($attrs, $merge)
    {
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
            static::setField($this->getID(), $key, $value, $merge);
        }
    }

    /**
     * Set an ACF field value for an item
     *
     * @param int $id
     * @param string $key
     * @param mixed $value
     * @param bool $merge
     * @return void
     */
    public static function setField($id, $key, $value, $merge)
    {
        Data::update($key, $value, static::FIELD_PREFIX.$id, $merge);
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
            static::setMeta($this->getID(), $key, $value);
        }
    }


    /**
     * Update the terms of the given object
     *
     * @param object $result
     * @param array $terms key : taxonomy, value : term(s)
     * @param boolean $merge Add terms and do not remove any
     * @return void
     */
    public function updateTaxonomies($tax, $merge = false)
    {
        static::setTaxonomies($this->getID(), $tax, $merge);
    }


    /**
     * Set the language of a term
     *
     * @param int $term_id
     * @param string $lang
     * @return bool
     */
    public function updateLang($lang)
    {
        static::setLang($this->getID(), $lang);
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