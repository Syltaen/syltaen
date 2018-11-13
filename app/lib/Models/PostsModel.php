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
    protected $termsFormats = [
        // "ProductsTaxonomy",
        // "ProductsTaxonomy@types" => [
        //     "names@list" => ", ",
        //     "ids"
        // ]
    ];


    /**
     * A list of other post models joined to this one with $this->join().
     * Used for applying $this->populateData() differently for each post type.
     * @var array
     */
    protected $joinedModels = [];



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
     * @return void
     */
    public function author($authors)
    {
        $this->filters["author__in"] = (array) $authors;
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


    // ==================================================
    // > DATA HANDLING FOR EACH POST
    // ==================================================
    /* Update parent method */
    public function populateResultData(&$post)
    {
        // if the post is not from this model, use the post's model
        if ($post->post_type !== static::TYPE) return $this->joinedModels[$post->post_type]->populateResultData($post);

        /* ADD THUMBNAIL FORMATS IF ANY */
        if ((!empty($this->thumbnailsFormats["url"]) || !empty($this->thumbnailsFormats["tag"])) && $this->hasAttr("thumb")) {
            $this->populateThumbnailFormats($post);
        }

        /* ADD TAXONOMIY TERMS IF ANY */
        if (!empty($this->termsFormats) && $this->hasAttr("terms")) {
            $this->populateTerms($post);
        }

        /* ADD POST URL IF PUBLIC */
        if ((static::HAS_PAGE) && $this->hasAttr("url")) {
            $this->populatePublicUrl($post);
        }

        /* COMMON */
        parent::populateResultData($post);
    }

    /**
     * Add all thumbnail formats specified in the model to a post object
     *
     * @param WP_Post $post
     * @return void
     */
    protected function populateThumbnailFormats(&$post)
    {
        if (!static::HAS_THUMBNAIL) return false;

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

    /**
     * Add taxonomy terms data to the post
     *
     * @param WP_Post $post
     * @return void
     */
    protected function populateTerms(&$post)
    {
        $post->terms = [];
        foreach ($this->termsFormats as $class=>$formats) {

            // Default format : all
            if (is_int($class)) {
                $class   = $formats;
                $formats = "all";
            }

            $class = "Syltaen\\" . $class;

            // Alias for the taxonomy
            if (preg_match('/(.*)@(.*)/', $class, $keys)) {
                $class = $keys[1];
                $alias = $keys[2];
            } else {
                $alias  = $class::SLUG;
            }

            // Only one format
            $direct = false;
            if (is_string($formats)) {
                $formats = (array) $formats;
                $direct  = true;
            }

            foreach ($formats as $format=>$join) {

                // No join
                if (is_int($format)) {
                    $format = $join;
                    $join   = false;
                }

                // Alias for the format
                if (preg_match('/(.*)@(.*)/', $format, $keys)) {
                    $format       = $keys[1];
                    $format_alias = $keys[2];
                } else {
                    $format_alias = $format;
                }

                $terms = (new $class)->getPostTerms($post->ID, $format);

                if ($direct) {
                    $post->terms[$alias] = $terms;
                } else {
                    $post->terms[$alias][$format_alias] = $terms;
                    if ($join) {
                        if (is_callable($join)) {
                            $post->terms[$alias][$format_alias] = $join($post->terms[$alias][$format_alias]);
                        } else {
                            $post->terms[$alias][$format_alias] = join($join, $post->terms[$alias][$format_alias]);
                        }
                    }
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
        $post->url = get_the_permalink($post->ID);
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

        if (static::HAS_PAGINATION) {
            $page = static::CUSTOMPATH ? static::CUSTOMPATH : static::TYPE;
            Route::add([[
                $page . "/([0-9]*)/?$",
                'index.php?pagename='.$page.'&page=$matches[1]'
            ]]);
        }
    }

    /**
     * Register custom status types for the model
     *
     * @param array $status_list List of custom posts status
     * @return void
     */
    public static function addStatusTypes($status_list)
    {
        if (empty($status_list)) return false;

        $post_type = static::TYPE;

        // ========== register each status ========== //
        foreach ($status_list as $status=>$labels) {
            register_post_status($status, [
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
            ]);
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

        // ========== Prevent status switch when saving ========== //
        // add_filter("wp_insert_post_data", function ($data , $postarr) use ($post_type) {
        //     if ($data["post_type"] == $post_type) {
        //         $data["post_status"] = $data["post_status"] == "publish" ? $postarr["original_post_status"] : $data["post_status"];
        //     }
        //     return $data;
        // }, "99", 2);
    }

    // ==================================================
    // > ACTIONS
    // ==================================================
    /**
     * Create a new post
     * see https://developer.wordpress.org/reference/functions/wp_insert_post/
     * @param string $title The post title
     * @param string $content The post content
     * @param array $fields Custom ACF fields with their values
     * @param string $status Status for the post
     * @return int The created post's ID
     */
    public static function add($attrs = [], $fields = false)
    {
        // Default attributes
        $attrs = array_merge([
            "post_type"      => static::TYPE,
            "post_title"     => "",
            "post_content"   => "",
            "post_status"    => "publish"
        ], $attrs);

        // Create the post
        $post_id = wp_insert_post($attrs);

        // Update the fields
        if ($fields) {
            static::updateFields($post_id, $fields);
        }

        return $post_id;
    }
}