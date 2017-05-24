<?php

namespace Syltaen\Models;

use Syltaen\App\Services\Fields;

abstract class Posts
{

    /**
     * List of supports for the post type's registration
     */
    const TYPE     = "news";
    const LABEL    = "Articles";
    const ICON     = false; // https://developer.wordpress.org/resource/dashicons
    const SUPPORTS = ["title", "editor", "author", "thumbnail", "excerpt", "trackbacks", "custom-fields", "comments", "revisions", "page-attributes", "post-formats"];
    const PUBLIK   = true;
    const HAS_PAGE = true;
    const REWRITE  = true; // Ex: ["slug" => "agenda"];
    const TAX      = [];

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
    protected $thumbnails_formats = [
        "url" => [],
        "tag" => []
    ];

    /**
     * List of date formats to be stored in each post.
     * "formatname" => "format"
     * see https://codex.wordpress.org/Formatting_Date_and_Time
     * @var array
     */
    protected $date_formats = [];


    /**
     * Store the query and its arguments to be modified by the model
     *
     * @var boolean
     */
    private $query   = false;
    private $filters = array();

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
     * @param int $order
     * @param string $order_by
     * @return void
     */
    public function order($order, $order_by)
    {

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
     * @param array $tax_query
     * @param string $relation
     * @return self
     */
    public function tax($tax_query, $relation = false)
    {
        // carefull about duplicates
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
        $this->query = new \WP_Query( $this->filters );
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
    // > TAXONOMY
    // ==================================================
    public function taxonomy($include_terms = false, $include_posts = false)
    {
        $taxonomies = get_object_taxonomies(static::TYPE, ($include_terms ? "objects" : "names"));
        if ($include_terms) {
            foreach ($taxonomies as $tax) {
                $tax->terms = get_terms([
                    "taxonomy"   => $tax->name,
                    "hide_empty" => false
                ]);

                if ($include_posts) {
                    foreach ($tax->terms as $term) {

                        $term->posts = $this->tax("test", "test")->get(3);

                        // /* #LOG# */ \Syltaen\Controllers\Controller::log($term);
                    }
                }
            }
        }

        return $taxonomies;
    }

    protected function populateTermData()
    {

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
            if (!empty($this->thumbnails_formats["url"]) || !empty($this->thumbnails_formats["tag"])) {
                $this->populateThumbnailFormats($post);
            }

            /* ADD DATE FORMATS IF ANY */
            if (!empty($this->date_formats)) {
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

        if (!empty($this->thumbnails_formats["url"])) {
            foreach ($this->thumbnails_formats["url"] as $name=>$format) {
                $post->thumb["url"][$name] = get_the_post_thumbnail_url($post->ID, $format);
            }
        }

        if (!empty($this->thumbnails_formats["tag"])) {
            foreach ($this->thumbnails_formats["tag"] as $name=>$format) {
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
        foreach ($this->date_formats as $name=>$format) {
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
        $this->thumbnails_formats[$type][$name] = $value;
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
        $this->date_formats[$name] = $format;
        return $this;
    }


    // ==================================================
    // > POST TYPE CONTROLS
    // ==================================================
    /**
     * Register a post type using the class constants
     *
     * @return void
     */
    public static function register()
    {
        return register_post_type(static::TYPE, array(
            "label"              => static::LABEL,
            "public"             => static::PUBLIK,
            "publicly_queryable" => static::HAS_PAGE,
            "menu_icon"          => static::ICON,
            "supports"           => static::SUPPORTS,
            "rewrite"            => static::REWRITE,
            "taxonomies"         => static::TAX,
            "has_archive"        => false
        ));
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