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
        static::setAttrs($this->getID(), $this->model::SLUG, $attrs);
    }

    /**
     * Set the attributes of an item
     *
     * @param int $id
     * @param array $attributes
     * @return int|WP_Error The updated user's ID or a WP_Error object if the user could not be updated.
     */
    public static function setAttrs($id, $taxonomy, $attrs)
    {
        return wp_update_term($id, $taxonomy, $attrs);
    }

    /**
     * Set the term_order of a term
     *
     * @return void
     */
    public static function setOrder($term_id)
    {
        global $wpdb;
        $terms = $wpdb->get_results("UPDATE $wpdb->terms SET term_order = $order WHERE term_id = $term_id");
    }


    /**
     * Set the language of a term
     *
     * @param int $term_id
     * @param string $lang
     * @return bool
     */
    public static function setLang($term_id, $lang)
    {
        return pll_set_term_language($term_id, $lang);
    }
}