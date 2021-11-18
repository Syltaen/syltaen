<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class ModelItemTaxonomy extends ModelItem
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
     * Get a specific meta data
     *
     * @param string
     * @return mixed
     */
    public function getMeta($meta_key, $multiple = false)
    {
        return get_term_meta($this->getID(), $meta_key, !$multiple);
    }

    /**
     * Update a meta value in the database
     *
     * @param string $key
     * @param mixed $value
     * @return mixed Meta ID if the key didn't exist, true on successful update, false on failure
     */
    public function setMeta($key, $value)
    {
        return update_term_meta($this->getID(), $key, $value);
    }

    /**
     * Set the attributes of an item
     *
     * @param int $id
     * @param array $attributes
     * @return int|WP_Error The updated user's ID or a WP_Error object if the user could not be updated.
     */
    public function updateAttrs($attrs, $merge = false)
    {
        if (empty($attrs)) return false;
        $attrs = $this->parseAttrs($attrs, $merge);
        return wp_update_term($this->getID(), $this->model::SLUG, $attrs);
    }

    /**
     * Update a result taxonomies
     *
     * @param array $attrs
     * @param bool $merge Only update empty attrs
     * @return void
     */
    public function updateTaxonomies($tax, $merge = false)
    {
        // Do nothing
    }

    /**
     * Set the language of a term
     *
     * @param string $lang
     * @return bool
     */
    public function updateLang($lang)
    {
        return pll_set_term_language($this->getID(), $lang);
    }

    /**
     * Set the term_order of a term
     *
     * @param int $order
     * @return void
     */
    public function updateOrder($order)
    {
        global $wpdb;
        $term_id = $this->getID();
        $terms = $wpdb->get_results("UPDATE $wpdb->terms SET term_order = $order WHERE term_id = $term_id");
    }

    /**
     * Delete a single user
     *
     * @param bool|int $reassign Reassign posts to a new term id.
     * @return void
     */
    public function delete($reassign = null)
    {
        wp_delete_term($this->getID(), $this->model::SLUG, $reassign ? [
            "force_default" => $reassign
        ] : null);
    }
}