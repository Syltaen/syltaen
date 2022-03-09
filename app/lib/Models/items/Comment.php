<?php

namespace Syltaen;

/**
 * Wrap each result of a model in a class that is used to retrieve dynamic fields defined by the model
 */

class Comment extends ModelItem
{
    const FIELD_PREFIX = "comment_";

    /**
     * Get a specific meta data
     *
     * @param  string
     * @return mixed
     */
    public function getMeta($meta_key = "", $multiple = false)
    {
        return get_comment_meta($this->getID(), $meta_key, !$multiple);
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
        return update_comment_meta($this->getID(), $key, $value);
    }

    /**
     * Remove a post meta
     *
     * @param  string $key    The meta key to remove
     * @param  mixed  $value  Allow to filter by type
     * @return bool   Success or failure
     */
    public function removeMeta($key, $value = null)
    {
        return delete_comment_meta($this->getID(), $key, $value);
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

        $keys               = $this->parseProperties($keys, $merge);
        $keys["comment_ID"] = $this->getID();
        return wp_update_comment($keys);
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
     * Delete a single user
     *
     * @param  bool|int $force
     * @return void
     */
    public function delete($force = null)
    {
        wp_delete_comment($this->getID(), $force);
    }

    /**
     * Update or filter the object keys before there are saved
     *
     * @param  object   $object
     * @return object
     */
    public static function filterObjectKeys($comment)
    {
        $item = (object) [];

        // Remove "comment_" prefix from each field key
        foreach ($comment as $key => $value) {
            $item->{str_replace("comment_", "", $key)} = $value;
        }

        // Change key for author, as it will be used in the model
        $item->author_name = $item->author;
        unset($item->author);

        return $item;
    }
}