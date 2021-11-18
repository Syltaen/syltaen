<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class ModelItemComment extends ModelItem
{
    const FIELD_PREFIX = "comment_";

    /**
     * Get a specific meta data
     *
     * @param string
     * @return mixed
     */
    public function getMeta($meta_key, $multiple = false)
    {
        return get_comment_meta($this->getID(), $meta_key, !$multiple);
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
        return update_comment_meta($this->getID(), $key, $value);
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
        $attrs["comment_ID"] = $this->getID();
        return wp_update_comment($attrs);
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
     * Delete a single user
     *
     * @param bool|int $force
     * @return void
     */
    public function delete($force = null)
    {
        wp_delete_comment($this->getID(), $force);
    }

    /**
     * Expose each default value of the wp_object
     *
     * @param object $comment
     * @param Model $model
     */
    public function __construct($comment, $model = false)
    {
        if (is_int($comment)) {
            return parent::__construct($comment, $model);
        }

        $item = (object) [];

        // Remove "comment_" prefix from each field key
        foreach ($comment as $key=>$value) {
            $item->{str_replace("comment_", "", $key)} = $value;
        }

        // Change key for author, as it will be used in the model
        $item->author_name = $item->author;
        unset($item->author);

        parent::__construct($item, $model);
    }
}