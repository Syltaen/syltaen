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
     * @var boolean|string Allow to use an external link (stored in a field) for the permalink generation.
     */
    const CUSTOM_PAGE_FIELD = false;

    /**
     * List of fields used by the Fields::store method
     *
     * @var array
     */
    protected $fields = [];

    /**
     * List of thumbnails formats to store in each post.
     * Specify a key and a format (string or array of sizes).
     * Specify those in one or both of the arrays (url or tag) depending on what you want to be retrieved
     * see https://developer.wordpress.org/reference/functions/the_post_thumbnail/
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
     * List of terms format to be stored in each post.
     * "taxonomy" => ["all"|"ids"|"names"|"slugs"(@alias) => "join"]
     * If join is false, retrieve an array of terms
     * see https://codex.wordpress.org/Function_Reference/wp_get_post_terms
     * @var array
     */
    protected $termsFormats = [];


    /**
     * Store the query and its arguments to be modified by the model
     *
     * @var boolean
     */
    protected $query   = false;
    protected $filters = [];

    /**
     * A list of other post models joined to this one with $this->join().
     * Used for applying $this->populateData() differently for each post type.
     * @var array
     */
    protected $joinedModels = [];

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
     * @param string $orderby the field to order the posts by
     * @param int $order ASC or DESC
     * @param string $meta_key When $orderby is "meta_value", specify the meta_key.
     * Must include a meta query beforehand specifying criteras for that key.
     * @return void
     */
    public function order($orderby = false, $order = "ASC", $meta_key = false)
    {
        $this->filters["orderby"] = $orderby;
        $this->filters["order"]   = $order;
        if ($orderby == "meta_value") {
            $this->filters["meta_key"] = $meta_key;
        }
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
     * Update the filter to only retrive specific ID's
     *
     * @param array $ids
     * @return void
     */
    public function ids($ids)
    {
        $this->filters["post__in"] = $ids;
        return $this;
    }

    /**
     * Update the filter to only retrive one specific ID
     *
     * @param int $id
     * @return self
     */
    public function id($id)
    {
        $this->filters["post__in"] = [$id];
        return $this;
    }

    /**
     * Update the taxonomy filter
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Taxonomy_Parameters
     * @param array $taxonomy slug of the taxonomy to look for
     * @param array|string|int $terms Term or list of terms to match
     * @param string $relation Erase the current relation between each tax_query.
     *        Either "OR", "AND" (deflault) or false to keep the current one.
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
            foreach ($this->filters["tax_query"] as $filter_key=>$filter) {
                if (isset($filter["taxonomy"]) && $filter["taxonomy"] == $taxonomy) {
                    unset($this->filters["tax_query"][$filter_key]);
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
     * @param string $key Custom field key.
     * @param string|array $value Custom field value.
     *        It can be an array only when compare is 'IN', 'NOT IN', 'BETWEEN', or 'NOT BETWEEN'.
     *        You don't have to specify a value when using the 'EXISTS' or 'NOT EXISTS'
     * @param string $compare  Operator to test. Possible values are :
     *        '=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE', 'IN',
     *        'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS' and 'NOT EXISTS'.
     * @param string $type Custom field type. Possible values are :
     *        'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME', 'DECIMAL', 'SIGNED',
     *        'TIME', 'UNSIGNED'. Default value is 'CHAR'.
     *        You can also specify precision and scale for the 'DECIMAL' and 'NUMERIC' types
     *        (for example, 'DECIMAL(10,5)' or 'NUMERIC(10)' are valid).
     * @param string $relation Erase the current relation between each meta_query.
     *        Either "OR", "AND" (deflault) or false to keep the current one.
     * @param boolean $replace Specify if the filter should replace any existing one on the same meta_key
     * @return void
     */
    public function meta($key, $value = null, $compare = "=", $type = null, $relation = false, $replace = false)
    {
        // Create the meta_query if it doesn't exist
        $this->filters["meta_query"] = isset($this->filters["meta_query"]) ? $this->filters["meta_query"] : [
            "relation" => "AND"
        ];

        // Update the relation if specified
        $this->filters["meta_query"]["relation"] = $relation ?: $this->filters["meta_query"]["relation"];

        // If $replace, remove all filters made on that specific meta_key
        if ($replace) {
            foreach ($this->filters["meta_query"] as $filter_key=>$filter) {
                if (isset($filter["key"]) && $filter["key"] == $key) {
                    unset($this->filters["meta_query"][$filter_key]);
                }
            }
        }

        // Add the filter
        $filter = [
            "key"     => $key,
            "value"   => $value,
            "compare" => $compare,
            "type"    => $type
        ];

        if (is_null($value)) {
            unset($filter["value"]);
        }

        if (is_null($type)) {
            unset($filter["type"]);
        }

        $this->filters["meta_query"][] = $filter;

        return $this;
    }


    /**
     * Add a post type to the query
     *
     * @param Syltaen\Models\Posts\ $post_model
     * @return void
     */
    public function join($post_model) {
        if (!is_array($this->filters["post_type"])) {
            $this->filters["post_type"] = [static::TYPE];
        }
        $this->filters["post_type"][] = $post_model::TYPE;
        $this->joinedModels[$post_model::TYPE] = $post_model;
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
     * @return array of WP_Posts
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
        if (empty($this->filters)) die ("Filters not set for " . static::TYPE);
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

    /**
     * Return the number of posts matching the query
     *
     * @return void
     */
    public function count()
    {
        return intval($this->query->found_posts);
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

        if (!isset($this->query) || empty($this->query)) die("The WP_Query need to be run before populating its posts.");

        foreach ($this->query->posts as $post) {

            // If the post is not from this model (because of a join), use that post's model
            if ($post->post_type !== static::TYPE) {

                $this->joinedModels[$post->post_type]->populatePostData($post);
            } else {
                $this->populatePostData($post);
            }
        }

        return $this;
    }

    /**
     * Launch each populate method on a post
     *
     * @param WP_Post $post
     * @return void
     */
    public function populatePostData(&$post)
    {

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

        /* ADD TAXONOMIY TERMS IF ANY */
        if (!empty($this->termsFormats)) {
            $this->populateTerms($post);
        }

        /* ADD POST URL IF PUBLIC */
        if (static::HAS_PAGE || static::CUSTOM_PAGE_FIELD) {
            $this->populatePublicUrl($post);
        }

    }


    /**
     * Add all Custom Fields's values specified in the model's constructor to a post
     *
     * @param WP_Post $post
     * @return void
     */
    protected function populateFields(&$post)
    {
        Fields::store($post, $this->fields, $post->ID);
    }

    /**
     * Add all thumbnail formats specified in the model to a post object
     *
     * @param WP_Post $post
     * @return void
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
     * @return void
     */
    protected function populateDateFormats(&$post)
    {
        $post->date = [];
        foreach ($this->dateFormats as $name=>$format) {
            if ($format) {
                $post->date[$name] = get_the_date($format, $post->ID);
            }
        }
    }

    /**
     * Add taxonomy terms data to the post
     *
     * @param WP_Post $post
     * @return void
     */
    protected function populateTerms(&$post)
    {
        $post->terms = [];
        foreach ($this->termsFormats as $taxonomy=>$formats) {
            foreach ($formats as $fields=>$join) {
                if (preg_match('/(.*)@(.*)/', $fields, $keys)) {
                    $fields = $keys[1];
                    $store  = $keys[2];
                } else {
                    $store = $fields;
                }

                $post->terms[$taxonomy][$store] = wp_get_post_terms($post->ID, $taxonomy, [
                    "fields" => $fields
                ]);

                if ($join) {
                    $post->terms[$taxonomy][$store] = join($join, $post->terms[$taxonomy][$store]);
                }
            }
        }
    }

    /**
     * Add the post public url to a post object
     *
     * @param WP_Post $post
     * @return void
     */
    protected function populatePublicUrl(&$post)
    {
        $post->url = static::CUSTOM_PAGE_FIELD ? Fields::get(static::CUSTOM_PAGE_FIELD, $post->ID) : get_the_permalink($post->ID);
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
        register_post_type(static::TYPE, [
            "label"              => static::LABEL,
            "public"             => static::PUBLIK,
            "publicly_queryable" => static::HAS_PAGE,
            "menu_icon"          => static::ICON,
            "supports"           => static::SUPPORTS,
            "rewrite"            => static::REWRITE,
            "taxonomies"         => static::TAX,
            "has_archive"        => false
        ]);

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
     * see https://developer.wordpress.org/reference/functions/wp_insert_post/
     * @param string $title The post title
     * @param string $content The post content
     * @param array $fields Custom ACF fields with their values
     * @param string $status Status for the post
     * @return WP_Post added post(s)
     */
    public static function add($title, $content = "", $fields = false, $status = "published")
    {
        $post_id = wp_insert_post([
            "post_type"    => static::TYPE,
            "post_title"   => $title,
            "post_content" => $content,
            "post_status"  => $status
        ]);

        /* #LOG# */ \Syltaen\Controllers\Controller::log($post_id, __CLASS__.":".__LINE__);


        foreach ($fields as $key=>$value) {
            update_field($key, $value, $post_id);
        }
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