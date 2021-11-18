<?php

namespace Syltaen;

abstract class PostsModel extends Model
{
    /**
     * The slug used for the post type
     */
    const TYPE = "post";

    /**
     * The readable label for the post type
     */
    const LABEL = "Articles";

    /**
     * The icon used for the administration
     * @see https://developer.wordpress.org/resource/dashicons
     */
    const ICON = false;

    /**
     * List of supports for the post type's registration
     */
    const HAS_TITLE          = true;
    const HAS_AUTHOR         = false;
    const HAS_EDITOR         = false;
    const HAS_THUMBNAIL      = false;
    const HAS_EXCERPT        = false;
    const HAS_TRACKBACKS     = false;
    const HAS_CUSTOMFIELDS   = false;
    const HAS_COMMENTS       = false;
    const HAS_REVISIONS      = false;
    const HAS_PAGEATTRIBUTES = false;
    const HAS_POSTFORMATS    = false;

    /**
     * Define if the page should be public or completely hidden
     */
    const PUBLIK = true;

    /**
     * Definie if each post should have its own page
     */
    const HAS_PAGE = true;

    /**
     * Define if a pagination route should be registered for the custom archive
     */
    const HAS_PAGINATION = true;

    /**
     * If defined, use a custom path instead of the slug
     * @see https://codex.wordpress.org/Function_Reference/register_post_type#Flushing_Rewrite_on_Activation
     */
    const CUSTOMPATH  = false;

    /**
     * List of taxonomies' slugs to use for this post type
     */
    const TAXONOMIES = false;

    /**
     * List of custom status to use
     * Exemple : "old_news"  => ["News dépassée", "News dépassées"]
     */
    const CUSTOM_STATUS = false;


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
     * List of terms format to be stored in each post.
     * See exemple bellow for the different return formats.
     * see https://codex.wordpress.org/Function_Reference/wp_get_post_terms
     * @var array
     */
    public $termsFormats = [
        // "(names) ProductsCategories@categories_names",
        // "(ids) ProductsCategories@categories_ids",
        // "ProductsCategories"
    ];


    /**
     * List of date formats to be stored in each post.
     * see https://codex.wordpress.org/Formatting_Date_and_Time
     * @var array
     */
    protected $dateFormats = [
        // "formatname" => "format"
    ];


    /**
     * A list of other post models joined to this one with $this->join().
     * Used for applying $this->populateData() differently for each post type.
     * @var array
     */
    protected $joinedModels = [];


    /**
     * Add fields shared by all post types
     */
    public function __construct() {
        parent::__construct();

        // By default, only fetch published posts
        $this->status("publish");

        $this->addFields([
            /**
             * The URL of the post
             */
            "@url" => function ($post) {
                return static::HAS_PAGE ? get_the_permalink($post->ID) : false;
            },

            /**
             * All the thumb formats for this post, defined by $thumbnailsFormats
             */
            "@thumb" => function ($post) {
                if (!static::HAS_THUMBNAIL) return false;

                $thumb = [
                    "url" => [],
                    "tag" => []
                ];

                if (!empty($this->thumbnailsFormats["url"])) {
                    foreach ($this->thumbnailsFormats["url"] as $name=>$format) {
                        $thumb["url"][$name] = get_the_post_thumbnail_url($post->ID, $format);
                    }
                }

                if (!empty($this->thumbnailsFormats["tag"])) {
                    foreach ($this->thumbnailsFormats["tag"] as $name=>$format) {
                        $thumb["tag"][$name] = get_the_post_thumbnail($post->ID, $format);
                    }
                }

                return $thumb;
            },


            /**
             * The post date in various format, defined by $dateFormats
             */
            "@date" => function ($post) {
                $date = [];
                foreach ($this->dateFormats as $name=>$format) {
                    if ($format) $date[$name] = get_the_date($format, $post->ID);
                }
                return $date;
            },

            /**
             * All the terms for this post, defined by $termsFormats
             */
            "@terms" => function ($post) {
                $list = [];

                // No term format defined, return empty list
                if (empty($this->termsFormats)) return $list;

                // For each format, get the terms and join them
                foreach (Data::normalizeFieldsKeys($this->termsFormats) as $key=>$join) {
                    // Parse key with default parts
                    $key = Data::parseDataKey($key);

                    $class = "Syltaen\\" . $key["meta"];
                    $store = $key["store"] == $key["meta"] ? $class::SLUG : $key["store"];

                    // Retrieve terms
                    $terms = (new $class)->for($post->ID)->fields($key["filter"] ?: "all")->get();

                    // No join : add to the list as is
                    if (empty($join)) {
                        $list[$store] = $terms;
                        continue;
                    }
                    // Has join
                    if (is_callable($join)) {
                        $list[$store] = $join($terms);
                    } else {
                        $list[$store] = join($join, $terms);
                    }
                }

                return $list;
            }
        ]);
    }

    // ==================================================
    // > QUERY MODIFIERS
    // ==================================================
    /* Update parent method */
    public function clearFilters($filter_keys = false, $default_filters = null)
    {
        return parent::clearFilters($filter_keys, [
            "post_type"   => static::TYPE,
            "nopaging"    => true
        ]);
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
     * @param boolean $children Specify if the terms children-terms should be included too
     * @return self
     */
    public function tax($taxonomy, $terms, $relation = false, $replace = false, $operator = "IN", $children = true)
    {
        // Create the tax_query if it doesn't exist
        $this->filters["tax_query"] = isset($this->filters["tax_query"]) ? $this->filters["tax_query"] : [
            "relation" => "AND"
        ];

        // Update the relation if specified
        $this->filters["tax_query"]["relation"] = $relation ?: $this->filters["tax_query"]["relation"];

        // Guess if $terms are slugs or ids for the field parameter
        $field = is_int($terms) || (is_array($terms) && isset($terms[0]) && is_int($terms[0])) ? "term_id" : "slug";

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
            "taxonomy"         => $taxonomy,
            "terms"            => $terms,
            "field"            => $field,
            "operator"         => $operator,
            "include_children" => $children
        ];

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
        $this->filters["post_parent__in"] = (array) $ids;
        return $this;
    }

    /**
     * Filter by author(s)
     *
     * @param int $ids
     * @return self
     */
    public function author($authors)
    {
        $this->filters["author__in"] = (array) $authors;
        return $this;
    }


    /**
     * Add a post type to the query
     *
     * @param Syltaen\ $post_model
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
     * Query update for the serach : add taxonomies
     *
     * @return void
     */
    private static function addTaxonomiesToSearchQuery($query, $search)
    {
        global $wpdb;
        $found_terms = false;

        // For each word in the search term, update the where clause
        $query["where"] = array_reduce(explode(" ", $search), function ($where, $word) use ($wpdb, &$found_terms) {
            $all_terms = [];

            // Get all terms of all taxonomies matching this word
            foreach (static::TAXONOMIES as $tax) {
                $terms = get_terms([
                    "taxonomy"   => $tax,
                    "hide_empty" => true,
                    "name__like" => $word,
                    "fields"     => "ids"
                ]);
                $all_terms = array_merge($all_terms, $terms);
                foreach ($terms as $term) {
                    $all_terms = array_merge($all_terms, get_term_children($term, $tax));
                }
            }

            // If no match, don't alter query
            if (empty($all_terms)) return $where;
            $found_terms = true;

            // Else, update where statement to include terms
            return preg_replace(
                "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'\{[a-z0-9]+\}".$word."\{[a-z0-9]+\}\')\s*\)/",
                "(".$wpdb->posts.".post_title LIKE $1) OR (searched_tax.term_taxonomy_id IN (".implode(",", $all_terms)."))",
                $where
            );

            return $where;
        }, $query["where"]);

        // Add term_relationships to the searchable data
        if ($found_terms) {
            $query["join"] .= " LEFT JOIN {$wpdb->term_relationships} searched_tax ON ({$wpdb->posts}.ID = searched_tax.object_id)";
        }

        return $query;
    }

    /**
     * Query update for the serach : add meta
     *
     * @return void
     */
    private static function addMetaToSearchQuery($query, $search, $meta_keys = true, $identifiers = [])
    {
        global $wpdb;

        $identifiers = array_merge([
            "meta_column"   => $wpdb->postmeta,
            "meta_alias"    => "searchmeta",
            "object_column" => $wpdb->posts,
        ], $identifiers);

        // Add postmeta to the searchable data if not already in it
        $query["join"] .= " LEFT JOIN {$identifiers['meta_column']} {$identifiers['meta_alias']} ON {$identifiers['object_column']}.ID = {$identifiers['meta_alias']}.post_id";

        // Restirct metadata to specific keys to speed up the search
        if (is_array($meta_keys)) foreach ($meta_keys as $key) {
            $query["join"] .= " AND {$identifiers['meta_alias']}.meta_key IN (".implode(",", array_map(function ($key) { return "'$key'"; }, $meta_keys)).")";
        }

        // Extend search to metadata
        $query["where"] = preg_replace(
            "/\(\s*{$wpdb->posts}.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "({$wpdb->posts}.post_title LIKE $1) OR ({$identifiers['meta_alias']}.meta_value LIKE $1)",
            $query["where"]
        );

        return $query;
    }

    /**
     * Query update for the serach : add children meta (ex: product_variation)
     *
     * @return void
     */
    private static function addChildrenMetaToSearchQuery($query, $search, $meta_keys = true)
    {
        global $wpdb;

        // Join all children
        $query["join"] .= " LEFT JOIN {$wpdb->posts} child ON child.post_parent = {$wpdb->posts}.ID";

        // Add meta of the children
        if ($meta_keys) {
            $query = static::addMetaToSearchQuery($query, $search, $meta_keys, [
                "meta_alias"    => "searchmeta_child",
                "object_column" => "child",
            ]);
        }

        return $query;
    }


    /**
     * Add search filter to the query.
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Search_Parameter
     * @param string $search
     * @param array|bool $include_meta Specify if the search should apply to the metadata,
     *                   restrict to specific key by passing an array
     * @param bool $strict
     * @return self
     */
    public function search($search, $include_meta = true, $include_children = false)
    {
        $this->filters["s"] = $search;

        // Clear previous query modifiers tagged with search
        $this->clearQueryModifiers("search");

        // Update the SQL query to include metadata and taxonomies
        return $this->updateQuery(function ($query) use ($search, $include_meta, $include_children) {
            global $wpdb;

            $query["distinct"] = "DISTINCT";

            // Add meta data if requested
            if ($include_meta) {
                $query = static::addMetaToSearchQuery($query, $search, $include_meta);

                // Also add the children's meta
                if ($include_children) {
                    $query = static::addChildrenMetaToSearchQuery($query, $search, $include_meta);
                }
            }

            // Add taxonomies, if there are any linked to this model
            if (!empty(static::TAXONOMIES)) {
                $query = static::addTaxonomiesToSearchQuery($query, $search);
            }

            return $query;
        }, "search");
    }


    // ==================================================
    // > DATA HANDLING FOR EACH POST
    // ==================================================
    /**
     * Add new thumbnail formats to the list
     *
     * @param string $type
     * @param string $name
     * @param string|array $value
     * @return self
     */
    public function addThumbnailFormats($type, $formats)
    {
        $this->thumbnailsFormats[$type] = array_merge($this->thumbnailsFormats[$type], $formats);
        return $this;
    }

    /**
     * Add new date formats to the list
     *
     * @param string $name
     * @param string $format
     * @return self
     */
    public function addDateFormats($formats)
    {
        $this->dateFormats = array_merge($this->dateFormats, $formats);
        return $this;
    }

    /**
     * Add new term formats to the list
     *
     * @param string $name
     * @param string $format
     * @return self
     */
    public function addTermsFormats($formats)
    {
        $this->termsFormats = array_merge($this->termsFormats, $formats);
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
        $supports = [];
        if (static::HAS_TITLE)          $supports[] = "title";
        if (static::HAS_EDITOR)         $supports[] = "editor";
        if (static::HAS_AUTHOR)         $supports[] = "author";
        if (static::HAS_THUMBNAIL)      $supports[] = "thumbnail";
        if (static::HAS_EXCERPT)        $supports[] = "excerpt";
        if (static::HAS_TRACKBACKS)     $supports[] = "trackbacks";
        if (static::HAS_CUSTOMFIELDS)   $supports[] = "custom-fields";
        if (static::HAS_COMMENTS)       $supports[] = "comments";
        if (static::HAS_REVISIONS)      $supports[] = "revisions";
        if (static::HAS_PAGEATTRIBUTES) $supports[] = "page-attributes";
        if (static::HAS_POSTFORMATS)    $supports[] = "post-formats";

        $rewrite = static::CUSTOMPATH ? ["slug" => static::CUSTOMPATH] : true;

        register_post_type(static::TYPE, [
            "label"              => static::LABEL,
            "public"             => static::PUBLIK,
            "publicly_queryable" => static::HAS_PAGE,
            "menu_icon"          => static::ICON,
            "supports"           => $supports,
            "rewrite"            => $rewrite,
            "has_archive"        => false
        ]);

        foreach ((array) static::TAXONOMIES as $slug) {
            register_taxonomy_for_object_type(
                $slug,
                static::TYPE
            );
        }

        static::addStatusTypes(static::CUSTOM_STATUS);
    }

    /**
     * Register custom status types for the model
     *
     * @param array $status_list List of custom posts status
     * @return void
     */
    public static function addStatusTypes($status_list, $options = [])
    {
        if (empty($status_list)) return false;

        $post_type = static::TYPE;

        // ========== register each status ========== //
        foreach ($status_list as $status=>$labels) {
            register_post_status($status, array_merge([
                "label" => $labels[0],
                "public" => true,
                "exclude_from_search" => true,
                "show_in_admin_all_list" => true,
                "show_in_admin_status_list" => true,
                "label_count" => _n_noop(
                    "$labels[0] <span class='count'>(%s)</span>",
                    "$labels[1] <span class='count'>(%s)</span>",
                    "syltaen"
                )
            ], $options));
        }
        // ========== Add in quick edit ========== //
        add_action("admin_footer-edit.php", function () use ($status_list, $post_type) {
            global $post;
            if (!$post || $post->post_type !== $post_type) return false;
            foreach ($status_list as $status=>$labels) {
                printf(
                    "<script>jQuery(function(\$){\$('select[name=\"_status\"]').append('<option value=\"%s\">%s</option>');});</script>",
                    $status,
                    $labels[0]
                );
            }
        });

        // ========== Add in post edit ========== //
        add_action("admin_footer-post.php", function () use($status_list, $post_type) {
            global $post;
            if (!$post || $post->post_type !== $post_type) return false;
            foreach ($status_list as $status=>$labels) {
                printf(
                        '<script>'.
                        '   jQuery(document).ready(function($){'.
                        '      $("select#post_status").append("<option value=\"%s\" %s>%s</option>");'.
                        '      $("a.save-post-status").on("click",function(e){'.
                        '         e.preventDefault();'.
                        '         var value = $("select#post_status").val();'.
                        '         $("select#post_status").value = value;'.
                        '         $("select#post_status option").removeAttr("selected", true);'.
                        '         $("select#post_status option[value=\'"+value+"\']").attr("selected", true)'.
                        '       });'.
                        '   });'.
                        '</script>',
                        $status,
                        $post->post_status !== $status ? "" : "selected='selected'",
                        $labels[0]
                );
                if ($post->post_status === $status) {
                    printf(
                        "<script>jQuery(function(\$){\$(\".misc-pub-section #post-status-display\").text(\"%s\");});</script>",
                        $labels[0]
                    );
                }
            }
        });
    }


    /**
     * Return the total number of published post stored in the database
     *
     * @return int
     */
    public static function getTotalCount($perm = "")
    {
        return wp_count_posts(static::TYPE, $perm);
    }

    /**
     * Get final slug used by the model
     *
     * @return string
     */
    public static function getCustomSlug()
    {
        return static::CUSTOMPATH ?: static::TYPE;
    }

    /**
     * Get the URL of this post archive
     *
     * @return string
     */
    public static function getArchiveURL($path = "")
    {
        return site_url(static::getCustomSlug() . "/" . $path);
    }


    /**
     * Get the meta values of all the posts
     *
     * @return void
     */
    public static function getAllMeta($key, $ids = false, $groupby_value = true)
    {
        $rows = Database::get_results(
           "SELECT p.ID ID, m.meta_value value FROM posts p
            JOIN postmeta m ON m.post_id = p.ID AND m.meta_key = '$key'
            WHERE p.post_type = '".static::TYPE."'" . ($ids ? ("AND p.ID IN " . Database::inArray($ids)) : "")
        );

        if (!$groupby_value) return Data::mapKey($rows, "ID", "value");

        return Data::groupByKey($rows, "value", "ID");
    }


    /**
     * Get the full list
     *
     * @return void
     */
    public static function addTranslationsIDs($ids)
    {
        if (empty($ids)) return [];

        $ids = Database::get_col(
           "SELECT object_id FROM term_relationships WHERE term_taxonomy_id IN (
                SELECT tr.term_taxonomy_id FROM term_relationships tr
                JOIN term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                WHERE tt.taxonomy = 'post_translations' AND tr.object_id IN (".implode(",", $ids).")
           )
        ");

        return array_map("intval", $ids);
    }

    // ==================================================
    // > ACTIONS
    // ==================================================
    /**
     * Create a new post
     * see https://developer.wordpress.org/reference/functions/wp_insert_post/
     * @param array $attrs The post attributes
     * @param array $fields Custom ACF fields with their values
     * @param string $status Status for the post
     * @return self A new model instance containing the new item
     */
    public static function add($attrs = [], $fields = [], $tax = [])
    {
        // Create the post
        $post_id = wp_insert_post(array_merge([
            "post_type"      => static::TYPE,
            "post_title"     => "",
            "post_content"   => "",
            "post_status"    => "publish"
        ], $attrs));

        if ($post_id instanceof \WP_Error) return $post_id;

        return (new static)->is($post_id)->update(false, $fields, $tax);
    }

    /**
     * Add a comment to all matchin posts
     *
     * @param string $content
     * @param string $author
     * @param string $email
     * @param string $url
     * @param integer $parent
     * @return void
     */
    public function addComment($comment, $author = "", $email = "", $url = "", $parent = 0)
    {
        foreach ($this->get() as $post) {

            // Register the new comment
            Comments::add([
                "comment_post_ID"      => $post->ID,
                "comment_author"       => $author,
                "comment_author_email" => $email,
                "comment_author_url"   => $url,
                "comment_type"         => "",
                "comment_parent"       => $parent,
                "comment_content"      => wpautop($comment)
            ]);
        }
    }
}