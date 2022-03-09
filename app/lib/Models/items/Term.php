<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class Term extends ModelItem
{
    const FIELD_PREFIX = "term_";

    /**
     * ID normalizer for all model resuts
     *
     * @return int
     */
    public function getID()
    {
        return $this->term_id;
    }

    /**
     * ID setter for all model resuts
     *
     * @return int
     */
    public function setID($id)
    {
        $this->term_id = $id;
    }

    /**
     * Get the title of the term
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->name;
    }

    /**
     * Get the type of the item
     *
     * @return string
     */
    public function getType()
    {
        return $this->taxonomy;
    }

    /**
     * Get the slug of the term
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Get a specific meta data
     *
     * @param  string
     * @return mixed
     */
    public function getMeta($meta_key = "", $multiple = false)
    {
        return get_term_meta($this->getID(), $meta_key, !$multiple);
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
        return update_term_meta($this->getID(), $key, $value);
    }

    /**
     * Set the attributes of an item
     *
     * @param  int          $id
     * @param  array        $attributes
     * @return int|WP_Error The updated user's ID or a WP_Error object if the user could not be updated.
     */
    public function setProperties($keys, $merge = false)
    {
        if (empty($keys)) {
            return false;
        }

        $keys = $this->parseProperties($keys, $merge);
        return wp_update_term($this->getID(), $this->taxonomy, $keys);
    }

    /**
     * Update a result taxonomies
     *
     * @param  array  $attrs
     * @param  bool   $merge   Only update empty attrs
     * @return void
     */
    public function setTaxonomies($tax, $merge = false)
    {
        // Do nothing
    }

    /**
     * Get the language of a term
     *
     * @return string
     */
    public function getLang()
    {
        return pll_get_term_language($this->getID());
    }

    /**
     * Set the language of a term
     *
     * @param  string $lang
     * @return bool
     */
    public function setLang($lang)
    {
        // wp_send_json($this);
        return pll_set_term_language($this->getID(), $lang);
    }

    /**
     * Get a specific term translation's ID
     *
     * @param  string $lang
     * @return int    ID of the translated post
     */
    public function getTranslationID($lang = false)
    {
        return pll_get_term($this->getID(), $lang);
    }

    /**
     * Get all the term translations' IDs
     *
     * @return array
     */
    public function getTranslationsIDs()
    {
        return pll_get_term_translations($this->getID());
    }

    /**
     * Set the term_order of a term
     *
     * @param  int    $order
     * @return void
     */
    public function setOrder($order)
    {
        $term_id = $this->getID();
        return Database::query("UPDATE terms SET term_order = $order WHERE term_id = $term_id");
    }

    /**
     * Get this term and its ancestors in a new TaxonomyModel
     *
     * @return TaxonomyModel
     */
    public function getAncestors($include_self = true)
    {
        $parents = array_reverse(get_ancestors($this->term_id, $this->taxonomy));

        if ($include_self) {
            $parents[] = $this->getID();
        }

        if (empty($parents)) {
            $parents = [-1];
        }

        $model = clone $this->model;
        return $model->clearFilters()->isMaybe($parents);
    }

    /**
     * Delete a single user
     *
     * @param  bool|int $reassign Reassign posts to a new term id.
     * @return void
     */
    public function delete($reassign = null)
    {
        if (empty($this->model)) {
            throw new \Exception("You must provide a model to delete a term");
        }

        wp_delete_term($this->getID(), $this->taxonomy, $reassign ? [
            "force_default" => $reassign,
        ] : null);
    }

    /**
     * Duplicate a term base data and meta data.
     *
     * @return Term
     */
    public function duplicate($slug_suffix = "2")
    {
        // Create new term with given suffix
        $term = $this->model::add($this->name, [
            "description" => $this->description,
            "slug"        => $this->slug . "-" . $slug_suffix,
        ]);

        // Duplicate all metadata
        $meta = $this->getMeta();
        foreach ($meta as $key => $values) {
            foreach ($values as $value) {
                add_term_meta($term->getID(), $key, maybe_unserialize($value), false);
            }
        }

        return $term;
    }

    /**
     * Create a new Term intance
     *
     * @param object|int|string $term
     * @param mixed             $model
     */
    public function __construct($term, $model = false)
    {
        if (empty($model)) {
            throw new \Exception("A model or a taxonomy must be provided when instanciating a Term.");
        }

        if (is_string($term) && is_string($model)) {
            throw new \Exception("A TaxonomyModel must be provided to retrieve a term by its slug.");
        }

        // If taxonomy slug provided, store only that for light mode
        if (is_string($model)) {
            $this->taxonomy = $model;
            $model          = false;
        } else {
            $this->taxonomy = $model::getSlug();
        }

        // Allow for term slugs, but a model must be provided
        if (is_string($term)) {
            $model->bySlug($term)->lang(false);
            $term = $model->getOne();
        }

        parent::__construct($term, $model);
    }
}