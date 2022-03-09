<?php

namespace Syltaen;

abstract class CommentsModel extends Model
{
    /**
     * The slug that define what this model is used for
     */
    const TYPE = "users";

    /**
     * Query arguments used by the model's methods
     */
    const QUERY_CLASS  = "WP_Comment_Query";
    const OBJECT_CLASS = "WP_Comment";
    const ITEM_CLASS   = "\Syltaen\Comment";
    const OBJECT_KEY   = "comments";
    const QUERY_IS     = "comment__in";
    const QUERY_ISNT   = "comment__not_in";
    const QUERY_LIMIT  = "number";
    const QUERY_STATUS = "status";
    const QUERY_HOOK   = "pre_get_comments";
    const META_TABLE   = "commentmeta";
    const META_OBJECT  = "comment_id";

    /**
     * Add fields shared by all comments types
     */
    public function __construct()
    {
        parent::__construct();

        $this->status("approve");

        $this->addFields([
            /*
             * The comment author
             */
            "@author" => function ($comment) {
                return Users::getItem($comment->user_id);
            },

            /**
             * The permalink
             */
            "@url"    => function ($comment) {
                return get_comment_link($comment->getID());
            },
        ]);
    }

    // =============================================================================
    // > FILTERS
    // =============================================================================
    /**
     * Filter comments from a specific post
     *
     * @return void
     */
    public function of($post)
    {
        $this->filters["post__in"] = (array) $post;
        return $this;
    }

    /**
     * Filter by comment author
     *
     * @param  User|int $user
     * @return serlf
     */
    public function author($user)
    {
        if (is_int($user)) {
            $filter = "user_id";
        } elseif (is_array($user)) {
            $filter = "author__in";
        } elseif (is_string($user)) {
            $filter = "author__email";
        } else {
            return $this;
        }

        $this->filters[$filter] = $user;

        return $this;
    }

    /**
     * Filter by parent(s)
     *
     * @param  array|int $ids List of parent ids
     * @return self
     */
    public function parent($ids)
    {
        $this->filters["parent__in"] = (array) $ids;
        return $this;
    }

    /**
     * Filter comments that are on hold
     *
     * @return self
     */
    public function onHold()
    {
        return $this->status("hold");
    }

    // =============================================================================
    // > GETTERS
    // =============================================================================

    /* Update parent method */
    /**
     * @param $paginated
     */
    public function count($paginated = true)
    {
        return count($this->getQuery()->comments);
    }

    /**
     * Add all date formats specified in the model to a post object
     *
     * @param  WP_Post $post
     * @return void
     */
    protected function populateDateFormats(&$comment)
    {
        if (!$this->hasAttr("date")) {
            return false;
        }

        $comment->date = [];
        foreach ($this->dateFormats as $name => $format) {
            if ($format) {
                $comment->date[$name] = get_comment_date($format, $comment->ID);
            }
        }
    }

    /**
     * Set a default filter because runing WP_User_Query without any argument return no result
     *
     * @param  boolean $filter_keys
     * @param  array   $default_filters
     * @return self
     */
    public function clearFilters($filter_keys = false, $default_filters = null)
    {
        return parent::clearFilters($filter_keys, [
            "prevent_empty" => true,
        ]);
    }

    /**
     * Get all the IDs of this model's objects
     *
     * @return array
     */
    public static function getAllIDs()
    {
        return (array) Database::get_col("SELECT comment_ID FROM comments");
    }

    /**
     * Get a dummy object instance
     *
     * @return object
     */
    public static function getDummyObject()
    {
        return (object) [
            "comment_ID"           => "0",
            "comment_post_ID"      => "0",
            "comment_author"       => "",
            "comment_author_email" => "",
            "comment_author_url"   => "",
            "comment_author_IP"    => "",
            "comment_date"         => "",
            "comment_date_gmt"     => "",
            "comment_content"      => "",
            "comment_karma"        => "0",
            "comment_approved"     => "0",
            "comment_agent"        => "",
            "comment_type"         => "comment",
            "comment_parent"       => "0",
            "user_id"              => "0",
        ];
    }

    // =============================================================================
    // > ACTION
    // =============================================================================
    /**
     * Create a new comment
     * see https://codex.wordpress.org/Function_Reference/wp_insert_comment
     * @param  array $attrs
     * @param  array $fileds
     * @return self  A new model instance containing the new item
     */
    public static function add($attrs = [], $fields = false)
    {
        global $post;

        // Create the comment
        $comment_id = wp_insert_comment(array_merge([
            "comment_post_ID" => $post ? $post->ID : false,
            "user_id"         => get_current_user_id(),
            "comment_content" => "",
            // "comment_approved" => 0
        ], $attrs));

        return static::getItem($comment_id)->update(false, $fields);
    }
}