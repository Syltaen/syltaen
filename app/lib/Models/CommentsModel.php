<?php

namespace Syltaen;

abstract class CommentsModel extends Model
{

    // =============================================================================
    // > FILTERS
    // =============================================================================
    /**
     * Filter comments from a specific post
     *
     * @return void
     */
    public function post($post)
    {
        $this->filters["post__in"] = (array) $post;
        return $this;
    }

    /**
     * Filter by comment author
     *
     * @param [type] $user
     * @return void
     */
    public function author($user)
    {
        if (is_int($user)) {
            $filter = "user_id";
        }
        elseif (is_array($user)) {
            $filter = "author__in";
        }
        elseif (is_string($user)) {
            $filter = "author__email";
        } else return $this;

        $this->filters[$filter] = $user;
        return $this;
    }

    /**
     * Filter by parent(s)
     *
     * @param array|int $ids List of parent ids
     * @return self
     */
    public function parent($ids)
    {
        $this->filters["parent__in"] = (array) $ids;
        return $this;
    }

    public function limit($limit = false, $filter_key = "number")
    {
        return parent::limit($limit, $filter_key);
    }


    public function is($list, $add = false, $filter_key = "comment__in")
    {
        return parent::is($list, $add, $filter_key);
    }

    public function isnt($list, $filter_key = "comment__not_in")
    {
        return parent::isnt($list, $filter_key);
    }

    public function search($terms, $columns = [], $strict = false)
    {
        $this->filters["search"] = $terms;

        return $this;
    }


    public function status($status = false)
    {
        if ($status) {
            $this->filters["status"] = $status;
        }
        return $this;
    }

    // =============================================================================
    // > GETTERS
    // =============================================================================
    /**
     * Update parent method to have a cleaner data structure for each result
     *
     * @param WP_User_Query $query
     * @return void
     */
    protected static function getResultsFromQuery($query)
    {
        if (!$query->comments || empty($query->comments)) return [];

        foreach ($query->comments as &$comment) {
            $comment->ID = $comment->comment_ID;
        } unset ($comment);

        return $query->comments;
    }


    /* Update parent method */
    public function run($force = false)
    {
        if ($this->cachedQuery && $this->filters == $this->cachedFilters && !$force) return $this;
        $this->clearCache();


        $this->cachedQuery = new \WP_Comment_Query($this->filters);
        $this->cachedFilters = $this->filters;
        return $this;
    }


    /* Update parent method */
    public function count($paginated = true)
    {
        return count($this->getQuery()->comments);
    }


    // =============================================================================
    // > DATA HANDLING FOR EACH POST
    // =============================================================================
    /* Update parent method */
    protected function populateFields(&$comment, $fields_prefix = "comment_")
    {
        parent::populateFields($comment, $fields_prefix);
    }

    /**
     * Add all date formats specified in the model to a post object
     *
     * @param WP_Post $post
     * @return void
     */
    protected function populateDateFormats(&$comment)
    {
        if (!$this->hasAttr("date")) return false;

        $comment->date = [];
        foreach ($this->dateFormats as $name=>$format) {
            if ($format) {
                $comment->date[$name] = get_comment_date($format, $comment->ID);
            }
        }
    }

    /**
     * Set a default filter because runing WP_User_Query without any argument return no result
     *
     * @param boolean $filter_keys
     * @param array $default_filters
     * @return self
     */
    public function clearFilters($filter_keys = false, $default_filters = null)
    {
        return parent::clearFilters($filter_keys, [
            "prevent_empty"   => true
        ]);
    }

    // =============================================================================
    // > ACTION
    // =============================================================================
    /**
     * Create a new comment
     * see https://codex.wordpress.org/Function_Reference/wp_insert_comment
     * @param array $attrs
     * @param array $fileds
     * @return void
     */
    public static function add($attrs = [], $fields = false)
    {
        global $post;
        $user = Data::globals("user");

        // Default attributes
        $attrs = array_merge([
            "comment_post_ID" => $post ? $post->ID : false,
            "user_id"         => $user ? $user->ID : false,
            "comment_content" => "",
            // "comment_approved" => 0
        ], $attrs);

        // Create the comment
        $comment_id = wp_insert_comment($attrs);

        // Update the fields
        if ($fields) {
            static::updateFields($comment_id, $fields);
        }

        return $comment_id;
    }

    public function update($attrs = [], $fields = false, $merge = false)
    {
        foreach ($this->get() as $result) {
            // Default attributes
            if ($attrs && !empty($attrs)) {
                static::updateAttrs($result, $attrs, $merge);
            }

            // Custom fields
            if ($fields && !empty($fields)) {
                static::updateFields($result, $fields, $merge);
            }
        }

        // Force get refresh
        $this->clearCache();

        return $this;
    }

    /**
     * Update a result base attributes
     *
     * @param array $attrs
     * @param bool $merge Only update empty attrs
     * @return void
     */
    public static function updateAttrs($result, $attrs, $merge = false)
    {
        if ($merge) {
            foreach ($attrs as $attr=>$value) {
                if (isset($result->$attr) && !empty($result->$attr)) {
                    unset($attrs[$attr]);
                }
            }
        }

        foreach ($attrs as &$attr) {
            if (is_callable($attr) && !is_string($attr)) $attr = $attr($result);
        }

        $attrs["comment_ID"] = $result->ID;
        wp_update_comment($attrs);
    }

    /* Update parent method */
    public static function updateFields($comment, $fields, $merge = false, $fields_prefix = "comment_")
    {
        parent::updateFields($comment, $fields, $merge, $fields_prefix);
    }

    /* Update parent method */
    public function delete($force = false)
    {
        foreach ($this->get() as $comment) {
            wp_delete_comment($comment->ID, $force);
        }
        $this->clearCache();
    }
}