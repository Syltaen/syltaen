<?php

namespace Syltaen;

abstract class Posts extends Model
{
    /**
     * The slug used for the post type
     */
    const TYPE = "posts";

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
     * List of date formats to be stored in each post.
     * "formatname" => "format"
     * see https://codex.wordpress.org/Formatting_Date_and_Time
     * @var array
     */
    protected $dateFormats = [];

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

        /* ADD DATE FORMATS IF ANY */
        if (!empty($this->dateFormats)  && $this->hasAttr("date")) {
            $this->populateDateFormats($post);
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
        add_filter("wp_insert_post_data", function ($data , $postarr) use ($post_type) {
            if ($data["post_type"] == $post_type) {
                $data["post_status"] = $data["post_status"] == "publish" ? $postarr["original_post_status"] : $data["post_status"];
            }
            return $data;
        }, "99", 2);
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
    public static function add($title, $content = "", $fields = false, $status = "publish")
    {
        $post_id = wp_insert_post([
            "post_type"      => static::TYPE,
            "post_title"     => $title,
            "post_content"   => $content,
            "post_status"    => $status
        ]);

        if ($fields) {
            static::updateFields($post_id, $fields);
        }

        return $post_id;
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
    public function addComment($content, $author = "", $email = "", $url = "", $parent = 0)
    {
        foreach ($this->get() as $post) {
            wp_new_comment([
                "comment_post_ID"		=> $post->ID,
                "comment_author"		=> $author,
                "comment_author_email" 	=> $email,
                "comment_author_url"	=> $url,
                "comment_type"			=> "",
                "comment_parent"		=> $parent,
                "comment_content"		=> wpautop($content)
            ]);
        }
    }
}