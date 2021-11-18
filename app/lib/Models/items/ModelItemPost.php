<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class ModelItemPost extends ModelItem
{
    const FIELD_PREFIX = "";


    /**
     * Delete a post
     *
     * @param bool $force Whether to bypass Trash and force deletion.
     * @return void
     */
    public function delete($force = false)
    {
        wp_delete_post($this->ID, $force);
    }


    /**
     * Set the attributes of an item
     *
     * @param int $id
     * @param array $attributes
     * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
     */
    public static function setAttrs($id, $attrs)
    {
        $attrs["ID"] = $id;
        return wp_update_post($attrs);
    }

    /**
     * Update a meta value in the database
     *
     * @param int $id
     * @param string $key
     * @param mixed $value
     * @return mixed Meta ID if the key didn't exist, true on successful update, false on failure
     */
    public function setMeta($id, $key, $value)
    {
        return update_post_meta($id, $key, $value);
    }

    /**
     * Set the taxonomies of a post
     *
     * @param int $id
     * @param array $tax
     * @param bool $merge
     * @return void
     */
    public static function setTaxonomies($id, $tax, $merge)
    {
        foreach ((array) $tax as $taxonomy=>$terms) {
            wp_set_object_terms($id, $terms, $taxonomy, $merge);
        }
    }


    /**
     * Set the language of a post
     *
     * @param int $id
     * @param array $tax
     * @param bool $merge
     * @return void
     */
    public static function setLang($id, $lang)
    {
        return pll_set_post_language($term_id, $lang);
    }
}