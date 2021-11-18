<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class ModelItemPost extends ModelItem
{
    const FIELD_PREFIX = "";

    /**
     * Get a specific meta data
     *
     * @param string
     * @return mixed
     */
    public function getMeta($meta_key, $multiple = false)
    {
        return get_post_meta($this->getID(), $meta_key, !$multiple);
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
        return update_post_meta($this->getID(), $key, $value);
    }

    /**
     * Set the attributes of an item
     *
     * @param int $id
     * @param array $attributes
     * @return int|WP_Error The post ID on success. The value 0 or WP_Error on failure.
     */
    public function updateAttrs($attrs, $merge = false)
    {
        if (empty($attrs)) return false;
        $attrs = $this->parseAttrs($attrs, $merge);
        $attrs["ID"] = $this->getID();
        return wp_update_post($attrs);
    }

    /**
     * Set the taxonomies of a post
     *
     * @param array $tax
     * @param bool $merge
     * @return void
     */
    public function updateTaxonomies($tax, $merge = false)
    {
        foreach ((array) $tax as $taxonomy=>$terms) {
            wp_set_object_terms($this->getID(), $terms, $taxonomy, $merge);
        }
    }

    /**
     * Set the language of a post
     *
     * @param string $lang
     * @return bool
     */
    public function updateLang($lang)
    {
        return pll_set_post_language($this->getID(), $lang);
    }

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
}