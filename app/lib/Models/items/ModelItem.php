<?php

namespace Syltaen;

use AllowDynamicProperties;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */
#[AllowDynamicProperties]
abstract class ModelItem
{
    /**
     * Hold a reference to the model so that we can know how to retrieve fields
     *
     * @var PostModel|TaxonomyModel|UserModel
     */
    protected $model = false;

    /**
     * Prefix to user for all ACF related field manipulation
     */
    const FIELD_PREFIX = "";

    /**
     * Expose each default value of the wp_object
     *
     * @param object $wp_object_or_id A full WP Object or just an ID.
     * @param Model  $model
     */
    public function __construct($wp_object_or_id, $model = false)
    {
        $this->setModel($model);

        // Only an ID was provided
        if (!is_object($wp_object_or_id) && (is_int($wp_object_or_id) || !empty(intval($wp_object_or_id)))) {
            if ($this->model) {
                // Get the object from the model
                $wp_object_or_id = $wp_object_or_id === 0 ? false : $this->model::getObject($wp_object_or_id);
                if (!$wp_object_or_id || (isset($wp_object_or_id->ID) && $wp_object_or_id->ID == 0)) {
                    $wp_object_or_id = $this->model::getDummyObject();
                }
            } else {
                // Only keep the ID, only allows some simple methods
                $this->setID($wp_object_or_id);
            }
        }

        // Extract all properties of the object
        if (is_object($wp_object_or_id)) {
            foreach (static::filterObjectKeys($wp_object_or_id) as $key => $value) {
                $this->{$key} = $value;
            }
        }

        // Fetch all fields if the model requests it
        if ($this->model) {
            $this->fetchFields($this->model->forceFetchFields);
        }
    }

    /**
     * Check that the object was found and exists
     *
     * @return bool
     */
    public function exists()
    {
        return !empty($this->ID);
    }

    /**
     * Set the model for this item.
     * Allow to change the model after it's been initialize, or to prevent useless SQL queries
     *
     * @param  Model  $model
     * @return self
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Get the model for this item
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    // ==================================================
    // > FIELDS MANAGEMENT
    // ==================================================
    /**
     * When trying to access a property that is not already stored, use the model fields definition to proccess its value
     *
     * @param  string  $property
     * @return mixed
     */
    public function __get($field)
    {
        // Throw an error because the field was not defined in the index
        if (!$this->hasFieldInIndex($field)) {
            if ($this->model) {
                trigger_error("\"$field\" was not found in \"" . get_class($this->model) . "\".");
            } else {
                trigger_error("Trying to access the field \"$field\", but no model was provided.");
            }
        }

        // If the field is present in the field index, retrieve and cache its value
        $data                 = $this->getFieldFromModel($field);
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
        if (empty($this->model->fieldsIndex)) {
            return false;
        }

        return $this->model->fieldsIndex->hasKey($field);
    }

    /**
     * Get a field value and property from the model
     *
     * @param  string  $field
     * @return array
     */
    public function getFieldFromModel($field)
    {
        return Data::getAdvanced(
            $this->model->fieldsIndex[$field],
            $this->model->fields[$this->model->fieldsIndex[$field]],
            static::FIELD_PREFIX . $this->getID(),
            $this,
        );
    }

    /**
     * Get a field value and property
     *
     * @param  string  $field
     * @return array
     */
    public function getField($field, $default = "", $filter = false)
    {
        return Data::get($field, static::FIELD_PREFIX . $this->getID(), $default, $filter);
    }

    /**
     * Pre-fetch all the fields specified in the model
     *
     * @return self
     */
    public function fetchFields($fields = true)
    {
        if (!$this->model) {
            return $this;
        }

        if (empty($fields)) {
            return $this;
        }

        // Fetch all fields if "true" given as argument
        $fields = $fields === true ? $this->model->fieldsIndex->keys() : $fields;

        foreach ($fields as $field) {
            $key = $this->model->fieldsIndex[$field] ?? false;
            // Field does not exist in the index
            if (empty($key)) {
                continue;
            }

            // Force the value fetching, erasing potential existing fields
            $data                 = $this->getFieldFromModel($field);
            $this->{$data["key"]} = $data["value"];
        }

        return $this;
    }

    // ==================================================
    // > GETTERS / SETTERS
    // ==================================================
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
     * Get the title of the item
     *
     * @return string
     */
    public function getTitle()
    {
        return ""; // Should be updated by children classes
    }

    /**
     * Get a simple link, with the name
     *
     * @return string
     */
    public function getLink($class = "", $text = false, $blank = false)
    {
        $text = $text ?: $this->getTitle();

        if (!$this->url) {
            return $text;
        }

        return "<a href='{$this->url}' class='{$class}' " . ($blank ? "target='_blank'" : "") . ">{$text}</a>";
    }

    /**
     * Update a single post
     *
     *
     * @param  array  $properties Base properties of the object
     * @param  array  $fields     ACF data
     * @param  array  $tax        Taxonomies
     * @param  bool   $merge      Wether to merge with existing value or erase them
     * @return self
     */
    public function update($properties = [], $fields = [], $tax = [], $merge = false)
    {
        // Default attributes
        if (!empty($properties)) {
            $this->setProperties($properties, $merge);
        }

        // Custom fields
        if (!empty($fields)) {
            $this->setFields($fields, $merge);
        }

        // Taxonomy
        if (!empty($tax)) {
            $this->setTaxonomies($tax, $merge);
        }

        return $this;
    }

    /**
     * Parse object keys to be saved.
     * Only keep keys
     *
     * @param  array   $attrs
     * @param  bool    $merge
     * @return array
     */
    public function parseProperties($keys, $merge)
    {
        if (empty($keys)) {
            return false;
        }

        // If merging : do not keep keys that already have a value
        if ($merge) {
            foreach ($keys as $key => $value) {
                if (!empty($this->$key)) {
                    unset($keys[$key]);
                }

            }
        }

        // Il callable : execute it
        foreach ($keys as $key => $value) {
            if (is_callable($value) && !is_string($value)) {
                $keys[$key] = $value($this);
            }
        }

        return $keys;
    }

    /**
     * Update a result custom fields
     *
     * @param  array  $fields
     * @param  bool   $merge    Only update empty fields
     * @return void
     */
    public function setFields($fields, $merge = false)
    {
        if (empty($fields)) {
            return false;
        }

        foreach ($fields as $key => $value) {
            if (is_callable($value) && !is_string($value)) {
                $value = $value($this);
            }

            Data::update($key, $value, static::FIELD_PREFIX . $this->getID(), $merge);
        }
    }

    /**
     * Update the item's meta directrly (does not create a duplicate metakey for ACF)
     *
     * @param  array  $meta
     * @return void
     */
    public function setMetas($meta)
    {
        if (empty($meta)) {
            return;
        }

        foreach ($meta as $key => $value) {
            if (is_callable($value) && !is_string($value)) {
                $value = $value($this);
            }

            $this->setMeta($key, $value);
        }
    }

    /**
     * Get a specific meta data
     *
     * @param  string
     * @return mixed
     */
    public function getMetas($meta_keys, $multiple = false)
    {
        $meta = $this->getMeta(false, false);

        // Keep only the requested keys
        $meta = (array) set($meta)->keepKeys($meta_keys);

        // Keep only one value if requested
        if (!$multiple) {
            $meta = array_map("current", $meta);
        }

        return $meta;
    }

    // ==================================================
    // > TRANSLATIONS (only available for children that support translation)
    // ==================================================
    /**
     * Get a specific term translation
     *
     * @param  string    $lang
     * @return ModelItem ID of the translated post
     */
    public function getTranslation($lang = false)
    {
        $model = clone $this->model;
        $model->clearFilters();

        $id = $this->getTranslationID($lang);
        return $id ? new static($id, $model) : false;
    }

    /**
     * Get all the term translations
     *
     * @return array
     */
    public function getTranslations()
    {
        return array_map(function ($id) {
            return $id ? new static($id, $this->model) : false;
        }, $this->getTranslationsIDs());
    }

    /**
     * Create a duplicate of the object in a specific language
     *
     * @return ModelItem
     */
    public function createTranslation($lang)
    {
        // Duplicate in the right language
        $translation = $this->duplicate($lang);
        $translation->setLang($lang);

        // Link the translations
        $translations        = $this->getTranslationsIDs();
        $translations[$lang] = $translation->getID();
        $this->model::linkTranslations($translations);

        return $translation;
    }

    // ==================================================
    // > INTERNAL
    // ==================================================
    /**
     * Allow children to update or filter the object keys before there are saved
     *
     * @param  object   $object
     * @return object
     */
    public static function filterObjectKeys($object)
    {
        return $object;
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
        $this->fetchFields();
        Log::json($this);
    }

    /**
     * Dump the result of a model with all its fields loaded
     *
     * @return void
     */
    public function log()
    {
        $this->fetchFields();
        Log::console($this);
    }

    // =============================================================================
    // > METHODS TO BE UPDATED BY CHILDREN
    // =============================================================================

    /**
     * Set the attributes of an item
     *
     * @param  array        $keys
     * @return int|WP_Error The item ID on success. The value or WP_Error on failure.
     */
    public function setProperties($keys, $merge = false)
    {
    }

    /**
     * Set the taxonomies of a post
     *
     * @param  array  $tax
     * @param  bool   $merge
     * @return void
     */
    public function setTaxonomies($tax, $merge = false)
    {
    }

    /**
     * Get a specific meta data
     *
     * @param  string
     * @return mixed
     */
    public function getMeta($meta_key = "", $multiple = false)
    {
    }

    /**
     * Update a meta value in the database
     *
     * @param  string $key
     * @param  mixed  $value
     * @return mixed  Meta ID if the key didn't exist, true on successful update, false on failure
     */
    public function setMeta($key, $value)
    {
    }

    /**
     * Get a specific post translation's ID
     *
     * @param  string $lang
     * @return int    ID of the translated post
     */
    public function getTranslationID($lang = false)
    {
    }

    /**
     * Get all the post translations'IDs
     *
     * @return array
     */
    public function getTranslationsIDs()
    {
        return [];
    }

    /**
     * Create a clone of the post
     *
     * @return Post
     */
    public function duplicate()
    {
    }
}
