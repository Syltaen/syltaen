<?php

namespace Syltaen\Models\Posts;

use Syltaen\App\Services\Fields;

abstract class Posts
{

    /**
     * List of supports for the post type's registration
     */
    const TYPE     = "posts";
    const LABEL    = "Articles";
    const ICON     = false; // https://developer.wordpress.org/resource/dashicons
    const SUPPORTS = ["title", "editor", "author", "thumbnail", "excerpt", "trackbacks", "custom-fields", "comments", "revisions", "page-attributes", "post-formats"];
    const PUBLIK   = true;  // If not, the post type will be completely hidden
    const HAS_PAGE = true;  // Should each post have his own page
    const REWRITE  = true;  // Ex: ["slug" => "agenda"];
    const TAX      = [];    // List of taxonomy slugs

    /**
     * List of fields used by the Fields::store method
     *
     * @var array
     */
    protected $fields = [];

    /**
     * List of thumbnails formats to store in each post.
     * Specify formats in one or both of the arrays depending on what you want to be retrieved
     * see https://developer.wordpress.org/reference/functions/get_the_post_thumbnail/
     * @var array
     */
    protected $thumbnailsFormats = [
        "url" => [],
        "tag" => []
    ];

    /**
     * List of date formats to be stored in each post.
     * "formatname" => "format"
     * see https://codex.wordpress.org/Formatting_Date_and_Time
     * @var array
     */
    protected $dateFormats = [];


    /**
     * Store the query and its arguments to be modified by the model
     *
     * @var boolean
     */
    protected $query   = false;
    protected $filters = array();

    /**
     * Create the base query and pre-sort all needed fields
     */
    public function __construct()
    {
        $this->clearFilters();
    }


    // ==================================================
    // > QUERY MODIFIERS
    // ==================================================
    /**
     * Limit the number of posts returned.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Pagination_Parameters
     * @param int $limit
     * @return self
     */
    public function limit($limit = false)
    {
        if ($limit) {
            unset($this->filters["nopaging"]);
            $this->filters["posts_per_page"] = $limit;
        }
        return $this;
    }

    /**
     * Offset the results to a certain page.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Pagination_Parameters
     * @param int $page
     * @return self
     */
    public function page($page = false)
    {
        if ($page) {
            $this->filters["paged"] = $page;
        }
        return $this;
    }

    /**
     * Add search filter to the query.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Search_Parameter
     * @param string $terms
     * @param boolean $exclusive : Specify if the search is incluive (||) or exclusive (&&)
     * @return self
     */
    public function search($terms, $exclusive = false)
    {
        // $local_query
        // "s" => "keyword",
        // "post_in"

        return $this;
    }

    /**
     * Change the post order.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters
     * @param string $orderby
     * @param int $order
     * @return void
     */
    public function order($orderby = false, $order = "ASC")
    {
        $this->filters["orderby"] = $orderby;
        $this->filters["order"]   = $order;
        return $this;
    }

    /**
     * Update the status filter.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Status_Parameters
     * @param array|string $status : ["publish", "pending", "draft", "future", "private", "trash", "any"]
     * @return self
     */
    public function status($status = false)
    {
        if ($status) {
            $this->filters["post_status"] = $status;
        }
        return $this;
    }

    /**
     * Update the taxonomy filter
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Taxonomy_Parameters
     * @param array $taxonomy slug of the taxonomy to look for
     * @param array|string|int $terms Term or list of terms to match
     * @param string $relation Erase the current relation between each tax_query.
     *        Either "OR", "AND"(defl.) or false to keep the current one.
     * @param boolean $replace Specify if the filter should replace any existing one on the same taxonomy
     * @param string $operator 'IN', 'NOT IN', 'AND', 'EXISTS' and 'NOT EXISTS'
     * @return self
     */
    public function tax($taxonomy, $terms, $relation = false, $replace = false, $operator = "IN")
    {
        // Create the tax_query if it doesn't exist
        $this->filters["tax_query"] = isset($this->filters["tax_query"]) ? $this->filters["tax_query"] : [
            "relation" => "AND"
        ];

        // Update the relation if specified
        $this->filters["tax_query"]["relation"] = $relation ?: $this->filters["tax_query"]["relation"];

        // Guess if $terms are slugs or ids for the field parameter
        $field = is_int($terms) || (is_array($terms) && is_int($terms[0])) ? "term_id" : "slug";

        // If $replace, remove all filters made on that specific taxonomy
        if ($replace) {
            foreach ($this->filters["tax_query"] as $key=>$filter) {
                if (isset($filter["taxonomy"]) && $filter["taxonomy"] == $taxonomy) {
                    unset($this->filters["tax_query"][$key]);
                }
            }
        }

        // Add the filter
        $this->filters["tax_query"][] = [
            "taxonomy" => $taxonomy,
            "terms"    => $terms,
            "field"    => $field,
            "operator" => $operator
        ];
        return $this;
    }

    /**
     * Update the meta filter
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Custom_Field_Parameters
     * @param array $tax_query
     * @param string $relation
     * @return self
     */
    public function meta($meta_query, $relation = false)
    {
        // carefull about duplicates
        return $this;
    }

    /**
     * Clear one, several or all filters
     *
     * @param array|string $filter_keys
     * @return self
     */
    public function clearFilters($filter_keys = false)
    {
        if (!$filter_keys) {
            $this->filters = [
                "post_type"   => static::TYPE,
                "nopaging"    => true,
            ];
            return $this;
        }

        $filter_keys = is_array($filter_keys) ? $filter_keys : [$filter_keys];

        foreach ($filter_keys as $filter_key) {
            unset($this->filters[$filter_key]);
        }

        return $this;
    }

    // ==================================================
    // > GETTERS
    // ==================================================
    /**
     * Execute the query and retrive all the found posts
     *
     * @param int $limit Number of posts to return
     * @param int $page Page offset to use
     * @return void
     */
    public function get($limit = false, $page = false)
    {
        return $this
            ->limit($limit)
            ->page($page)
            ->run()
            ->populateData()
            ->query
            ->posts;
    }


    /**
     * Execute the query with the filters and store the result
     *
     * @return self
     */
    public function run()
    {
        $this->query = new \WP_Query($this->filters);
        return $this;
    }

    /**
     * Return the stored query
     *
     * @return WP_Query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Return the stored filters
     *
     * @return array of filters
     */
    public function getFilters()
    {
        return $this->filters;
    }


    // ==================================================
    // > DATA HANDLING FOR EACH POST
    // ==================================================
    /**
     * Add data to each passed post based on what the model supports
     *
     * @return self
     */
    public function populateData()
    {
        if (!isset($this->query) || empty($this->query)) die("The WP_Query need to be created before populating its posts.");

        foreach ($this->query->posts as $post) {
            /* ADD FIELDS IF ANY */
            if (!empty($this->fields)) {
                $this->populateFields($post);
            }

            /* ADD THUMBNAIL FORMATS IF ANY */
            if (!empty($this->thumbnailsFormats["url"]) || !empty($this->thumbnailsFormats["tag"])) {
                $this->populateThumbnailFormats($post);
            }

            /* ADD DATE FORMATS IF ANY */
            if (!empty($this->dateFormats)) {
                $this->populateDateFormats($post);
            }

            /* ADD POST URL IF PUBLIC */
            if (static::HAS_PAGE) {
                $this->populatePublicUrl($post);
            }
        }

        return $this;
    }


    /**
     * Add all Custom Fields's values specified in the model's constructor to a post
     *
     * @param WP_Post $post
     * @return null
     */
    protected function populateFields(&$post)
    {
        Fields::store($post, $this->fields, $post->ID);
    }

    /**
     * Add all thumbnail formats specified in the model to a post object
     *
     * @param WP_Post $post
     * @return null
     */
    protected function populateThumbnailFormats(&$post)
    {
        $post->thumb = [
            "url" => [],
            "tag" => []
        ];

        if (!empty($this->thumbnailsFormats["url"])) {
            foreach ($this->thumbnailsFormats["url"] as $name=>$format) {
                $post->thumb["url"][$name] = get_the_post_thumbnail_url($post->ID, $format);
            }
        }

        if (!empty($this->thumbnailsFormats["tag"])) {
            foreach ($this->thumbnailsFormats["tag"] as $name=>$format) {
                $post->thumb["tag"][$name] = get_the_post_thumbnail($post->ID, $format);
            }
        }
    }

    /**
     * Add all date formats specified in the model to a post object
     *
     * @param WP_Post $post
     * @return null
     */
    protected function populateDateFormats(&$post)
    {
        $post->date = [];
        foreach ($this->dateFormats as $name=>$format) {
            $post->date[$name] = get_the_date($format, $post->ID);
        }
    }

    /**
     * Add the post public url to a post object
     *
     * @param WP_Post $post
     * @return null
     */
    protected function populatePublicUrl(&$post)
    {
        $post->url = get_the_permalink($post->ID);
    }

    /**
     * Add or update a thumbnail format dynamicallly
     *
     * @param string $type
     * @param string $name
     * @param string|array $value
     * @return self
     */
    public function addThumbnailFormat($type, $name, $value)
    {
        $this->thumbnailsFormats[$type][$name] = $value;
        return $this;
    }

    /**
     * Add or update a date format dynamically
     *
     * @param string $name
     * @param string $format
     * @return self
     */
    public function addDateFormat($name, $format)
    {
        $this->dateFormats[$name] = $format;
        return $this;
    }


    // ==================================================
    // > POST TYPE REGISTRATION
    // ==================================================
    /**
     * Register a post type using the class constants
     *
     * @return void
     */
    public static function register()
    {
        register_post_type(static::TYPE, array(
            "label"              => static::LABEL,
            "public"             => static::PUBLIK,
            "publicly_queryable" => static::HAS_PAGE,
            "menu_icon"          => static::ICON,
            "supports"           => static::SUPPORTS,
            "rewrite"            => static::REWRITE,
            "taxonomies"         => static::TAX,
            "has_archive"        => false
        ));

        // addStatusTypes
    }

    public static function addStatusTypes()
    {

    }

    // ==================================================
    // > POST CONTROLS
    // ==================================================
    /**
     * Create a new post
     *
     * @param string $title
     * @param string $content
     * @param boolean $fields
     * @return WP_Post added post(s)
     */
    public static function add($title = "", $content = "", $fields = false)
    {

    }

    /**
     * Update all posts matching the query
     *
     * @param array $post_attrs
     * @param array $filds
     * @return WP_Post updated post(s)
     */
    public function update($post_attrs, $fields)
    {
        foreach ($this->posts as $p) {
            //
        }
    }

    /**
     * Delete all posts matching the query
     *
     * @param boolean $force : Completely remove the posts instead of placing them in the trash
     * @return boolean Result of the deletion
     */
    public function delete($force = false)
    {
        foreach ($this->posts as $p) {
            wp_delete_post($p->id, $force);
        }
    }

}