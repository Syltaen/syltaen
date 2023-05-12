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
     * Default thumbnail ACF key in the "display" option page
     */
    const HAS_DEFAULT_THUMBNAIL = false;

    /**
     * If defined, use a custom path instead of the slug
     * @see https://codex.wordpress.org/Function_Reference/register_post_type#Flushing_Rewrite_on_Activation
     */
    const CUSTOMPATH = false;

    /**
     * List of taxonomies' slugs to use for this post type
     */
    const TAXONOMIES = [];

    /**
     * List of custom status to use
     * Exemple : "old_news"  => ["News dépassée", "News dépassées"]
     */
    const CUSTOM_STATUS = false;

    /**
     * Slug for an ACF options page
     */
    const OPTIONS_PAGE = false;

    /**
     * A list of other post models joined to this one with $this->join().
     * Used for applying $this->populateData() differently for each post type.
     * @var array
     */
    protected $joinedModels = [];

    /**
     * Add fields shared by all post types
     */
    public function __construct()
    {
        parent::__construct();

        // By default, only fetch published posts
        $this->status("publish");

        $this->addFields([
            /**
             * The URL of the post
             */
            "@url"   => function ($post) {
                if (!static::HAS_PAGE || empty($post->ID)) return false;
                return get_permalink($post->ID);
            },

            /**
             * Instance of an Attachment allowing to get the image url/tag easily
             */
            "@thumb" => function ($post) {
                if (!static::HAS_THUMBNAIL) {
                    return false;
                }

                $thumb_id = $post->getMeta("_thumbnail_id") ?: 0;

                if (!$thumb_id && static::HAS_DEFAULT_THUMBNAIL) {
                    $thumb_id = Data::option(static::HAS_DEFAULT_THUMBNAIL);

                    $post->use_default_thumbnail = true;
                }

                return Attachments::getLightItem((int) $thumb_id);
            },
        ]);

        // Add a new field for each linked taxonomy
        if (!empty(static::TAXONOMIES)) {
            foreach (static::TAXONOMIES as $tax) {
                $tax = Text::namespaced($tax);
                $this->addFields([
                    $tax::SLUG => function ($post) use ($tax) {
                        return (new $tax)->for($post->ID);
                    },
                ]);
            }
        }
    }

    /**
     * Get the labal of the post type, allow for translations
     *
     * @param  bool     $singular
     * @return string
     */
    public static function getLabel($singular = false)
    {
        return static::LABEL;
    }

    // ==================================================
    // > QUERY MODIFIERS
    // ==================================================
    /* Update parent method */
    /**
     * @param $filter_keys
     * @param false          $default_filters
     */
    public function clearFilters($filter_keys = false, $default_filters = null)
    {
        $types = [static::TYPE];

        foreach ($this->joinedModels as $model) {
            $types[] = $model::TYPE;
        }

        return parent::clearFilters($filter_keys, [
            "post_type" => $types,
            "nopaging"  => true,
        ]);
    }

    /**
     * Update the taxonomy filter
     * See https://codex.wordpress.org/Class_Reference/WP_Query#Taxonomy_Parameters
     *        Either "OR", "AND" (deflault) or false to keep the current one.
     * @param  array            $taxonomy slug of the taxonomy to look for
     * @param  array|string|int $terms    Term or list of terms to match
     * @param  string           $relation Erase the current relation between each tax_query.
     * @param  boolean          $replace  Specify if the filter should replace any existing one on the same taxonomy
     * @param  string           $operator 'IN', 'NOT IN', 'AND', 'EXISTS' and 'NOT EXISTS'
     * @param  boolean          $children Specify if the terms children-terms should be included too
     * @return self
     */
    public function tax($taxonomy, $terms, $relation = false, $replace = false, $operator = "IN", $children = true)
    {
        // Create the tax_query if it doesn't exist
        $this->filters["tax_query"] = isset($this->filters["tax_query"]) ? $this->filters["tax_query"] : [
            "relation" => "AND",
        ];

        // Update the relation if specified
        $this->filters["tax_query"]["relation"] = $relation ?: $this->filters["tax_query"]["relation"];

        // Guess if $terms are slugs or ids for the field parameter
        $field = is_int($terms) || (is_array($terms) && isset($terms[0]) && is_int($terms[0])) ? "term_id" : "slug";

        // If $replace, remove all filters made on that specific taxonomy
        if ($replace) {
            foreach ($this->filters["tax_query"] as $filter_key => $filter) {
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
            "include_children" => $children,
        ];

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
        $this->filters["post_parent__in"] = (array) $ids;
        return $this;
    }

    /**
     * Filter by post_name
     *
     * @param  string $name
     * @return self
     */
    public function name($name)
    {
        $this->filters["name"] = $name;
        return $this;
    }

    /**
     * Filter by author(s)
     *
     * @param  int    $ids
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
     * @param  Syltaen\ $post_model
     * @return void
     */
    public function join($post_model)
    {
        $this->filters["post_type"]            = (array) $this->filters["post_type"];
        $this->filters["post_type"][]          = $post_model::TYPE;
        $this->joinedModels[$post_model::TYPE] = $post_model;
        return $this;
    }

    /**
     * Allow chilidren to support joined model
     *
     * @return ModelItem of a different model
     */
    public function parseJoinItem($item)
    {
        if (!isset($this->joinedModels[$item->post_type])) {
            return false;
        }

        $class = $this->joinedModels[$item->post_type]::ITEM_CLASS;
        return new $class($item, $this->joinedModels[$item->post_type]);
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
                $tax   = Text::namespaced($tax);
                $terms = get_terms([
                    "taxonomy"   => $tax::SLUG,
                    "hide_empty" => true,
                    "name__like" => $word,
                    "fields"     => "ids",
                ]);

                $all_terms = array_merge($all_terms, $terms);
                foreach ($terms as $term) {
                    $all_terms = array_merge($all_terms, get_term_children($term, $tax::SLUG));
                }
            }

            // If no match, don't alter query
            if (empty($all_terms)) {
                return $where;
            }

            $found_terms = true;

            // Else, update where statement to include terms
            return preg_replace(
                "/\(\s*" . $wpdb->posts . ".post_title\s+LIKE\s*(\'\{[a-z0-9]+\}" . $word . "\{[a-z0-9]+\}\')\s*\)/",
                "(" . $wpdb->posts . ".post_title LIKE $1) OR (searched_tax.term_taxonomy_id IN (" . implode(",", $all_terms) . "))",
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
        if (is_array($meta_keys)) {
            foreach ($meta_keys as $key) {
                $query["join"] .= " AND {$identifiers['meta_alias']}.meta_key IN (" . implode(",", array_map(function ($key) {return "'$key'";}, $meta_keys)) . ")";
            }
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
     *                   restrict to specific key by passing an array
     * @param  string     $search
     * @param  array|bool $include_meta Specify if the search should apply to the metadata,
     * @param  bool       $strict
     * @return self
     */
    public function search($search, $include_meta = true, $include_children = false)
    {
        $search             = trim($search);
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
    // > TRANSLATIONS
    // ==================================================
    /**
     * Link all the translations in a post
     *
     * @param  array   $posts
     * @return array
     */
    public static function linkTranslations(array $posts)
    {
        $posts = static::parseTranslationsList($posts);
        if (empty($posts)) {
            return false;
        }

        pll_save_post_translations($posts);

        return $posts;
    }

    /**
     * Check that this post type is translated
     *
     * @return boolean
     */
    public static function isTranslated()
    {
        if (!function_exists("pll_is_translated_post_type")) return false;
        return pll_is_translated_post_type(static::TYPE);
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
        $supports = array_keys(array_filter([
            "title"           => static::HAS_TITLE,
            "editor"          => static::HAS_EDITOR,
            "author"          => static::HAS_AUTHOR,
            "thumbnail"       => static::HAS_THUMBNAIL,
            "excerpt"         => static::HAS_EXCERPT,
            "trackbacks"      => static::HAS_TRACKBACKS,
            "custom-fields"   => static::HAS_CUSTOMFIELDS,
            "comments"        => static::HAS_COMMENTS,
            "revisions"       => static::HAS_REVISIONS,
            "page-attributes" => static::HAS_PAGEATTRIBUTES,
            "post-formats"    => static::HAS_POSTFORMATS,
        ]));

        $rewrite = static::CUSTOMPATH ? ["slug" => static::CUSTOMPATH] : static::HAS_PAGE;

        register_post_type(static::TYPE, [
            "label"               => static::LABEL,
            "public"              => static::PUBLIK,
            "publicly_queryable"  => static::HAS_PAGE,
            "exclude_from_search" => !static::PUBLIK,
            "menu_icon"           => static::ICON,
            "supports"            => $supports,
            "rewrite"             => $rewrite,
            "has_archive"         => false,
        ]);

        if (!empty(static::TAXONOMIES)) {
            foreach ((array) static::TAXONOMIES as $class) {
                $class = Text::namespaced($class);

                register_taxonomy_for_object_type(
                    $class::SLUG,
                    static::TYPE
                );
            }
        }

        if (!empty(static::CUSTOM_STATUS)) {
            foreach ((array) static::CUSTOM_STATUS as $status => $label) {
                static::registerCustomStatus($status, $label);
                static::makeCustomStatusEditable($status, $label);
            }
        }

        if (static::OPTIONS_PAGE) {
            static::registerOptionsPage();
        }
    }

    /**
     * Register an new custom status
     *
     * @param  string $status
     * @param  string $label
     * @return void
     */
    public static function registerCustomStatus($status, $label, $options = [])
    {
        register_post_status($status, array_merge([
            "label"                     => $label,
            "public"                    => true,
            "exclude_from_search"       => false,
            "show_in_admin_all_list"    => true,
            "show_in_admin_status_list" => true,
            "label_count"               => _n_noop("$label <span class='count'>(%s)</span>", "$label <span class='count'>(%s)</span>"),
        ], $options));
    }

    /**
     * Register hooks to allow a specific post status in the edition fields
     *
     * @param  string $status
     * @param  string $label
     * @return void
     */
    public static function makeCustomStatusEditable($status, $label, $show_in_list = true)
    {
        // Add in quick-edit
        add_action("admin_footer-edit.php", function () use ($status, $label) {
            global $post;if (!$post || $post->post_type !== static::TYPE) {
                return false;
            }

            echo "<script>jQuery(document).ready( function() {
                jQuery('select[name=\"_status\"]').append('<option value=\"$status\">$label</option>');
            });</script>";
        });

        // Show in list
        if ($show_in_list) {
            add_filter("display_post_states", function ($statuses) use ($status, $label) {
                global $post;
                if (empty($post)) {return $statuses;}

                if (get_query_var("post_status") == $status) {
                    return;
                }
                if ($post->post_status == $status) {
                    return [$label];
                }

                return $statuses;
            });
        }

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
        return Lang::addURLPrefix(site_url(static::getCustomSlug() . "/" . $path));
    }

    // ==================================================
    // > OPTIONS PAGE
    // ==================================================
    /**
     * Register an options page for this post type
     *
     * @return void
     */
    public static function registerOptionsPage()
    {
        if (static::OPTIONS_PAGE && function_exists("acf_add_options_page")) {
            // ========== HEADER & FOOTER ========== //
            acf_add_options_page([
                "page_title" => static::LABEL . " - Options",
                "menu_title" => static::LABEL . " - Options",
                "menu_slug"  => static::OPTIONS_PAGE,
                "post_id"    => static::OPTIONS_PAGE,
                "capability" => "edit_posts",
                "redirect"   => false,
                "autoload"   => true,
            ]);
        }
    }

    /**
     * Return an option from the options page
     *
     * @param  string  $key
     * @return mixed
     */
    public static function option($key)
    {
        return Data::get($key, static::OPTIONS_PAGE);
    }

    // ==================================================
    // > MASS DATA MANIPULATION
    // ==================================================
    /**
     * Get all the IDs of this model's objects
     *
     * @return array
     */
    public static function getAllIDs()
    {
        return (array) Database::get_col("SELECT ID FROM posts WHERE post_type = '" . static::TYPE . "' AND post_status = 'publish'");
    }

    /**
     * Add the children's ids to the list
     *
     * @return array
     */
    public static function addChildrenIDs($parent_ids)
    {
        $children = Database::get_col("SELECT ID FROM posts WHERE post_parent IN " . Database::inArray($parent_ids) . " AND post_type = '" . static::TYPE . "'");
        return (array) $children->merge($parent_ids)->unique()->map("intval");
    }

    /**
     * Add the parent's ids to the list
     *
     * @return array
     */
    public static function addParentsIDs($children_ids)
    {
        $parents = Database::get_col("SELECT post_parent FROM posts WHERE ID IN " . Database::inArray($children_ids) . " AND post_type = '" . static::TYPE . "'");
        return (array) $parents->merge($children_ids)->unique()->map("intval");
    }

    /**
     * Return all the parents of the given posts
     *
     * @param  array   $ids
     * @return array
     */
    public static function getParents($post_ids)
    {
        return (array) Database::get_results("SELECT ID, post_parent FROM posts WHERE ID IN " . Database::inArray($post_ids))->groupBy("post_parent", "ID");
    }

    /**
     * Get the full list
     *
     * @param  array
     * @return void
     */
    public static function addTranslationsIDs($post_ids)
    {
        if (empty((array) $post_ids)) {
            return [];
        }

        $translations = static::getTranslations($post_ids);
        return $translations->callEach()->values()->merge()->map("intval");
    }

    /**
     * Get all the translations for the given posts
     *
     * @return Set
     */
    public static function getTranslations($post_ids)
    {
        $translations = recusive_set(Database::get_results(
            "SELECT p.ID post_id, lang_t.slug lang, trans_tt.description translations FROM posts p
                -- Lang
                JOIN term_relationships lang_tr ON lang_tr.object_id = p.ID
                JOIN term_taxonomy lang_tt ON lang_tt.term_taxonomy_id = lang_tr.term_taxonomy_id AND lang_tt.taxonomy = 'language'
                JOIN terms lang_t ON lang_t.term_id = lang_tt.term_id

                -- Translations
                LEFT JOIN term_relationships trans_tr ON trans_tr.object_id = p.ID
                LEFT JOIN term_taxonomy trans_tt ON trans_tt.term_taxonomy_id = trans_tr.term_taxonomy_id AND trans_tt.taxonomy = 'post_translations'

                WHERE p.ID IN " . Database::inArray($post_ids)
        ))->reduce(function ($posts, $row) {
            $posts[$row->post_id] = $posts[$row->post_id] ?? ["lang" => $row->lang];
            if ($row->translations) {
                $posts[$row->post_id]["translations"] = $row->translations;
            }
            return $posts;
        }, []);

        return set($translations)->mapAssoc(function ($post_id, $data) {
            if (!empty($data["translations"])) {
                return [$post_id, set(unserialize($data["translations"]))];
            }
            // No translation : return only the post with its language
            return [$post_id, set([$data["lang"] => $post_id])];
        });
    }

    /**
     * Get the language of all the posts
     *
     * @return Set
     */
    public static function getLangs($post_ids = false)
    {
        return static::getTranslations($post_ids)->mapAssoc(function ($id, $translations) {
            return [$id, $translations->search($id)];
        });
    }

    /**
     * Get all the possible taxonomues for this type of posts
     *
     * @return void
     */
    public static function getAllTaxonomiesChoices()
    {
        return cache("taxonomy_choices")->get(function () {
            global $wp_taxonomies;
            $choices = [];

            foreach ($wp_taxonomies as $tax) {
                if (!in_array(static::TYPE, $tax->object_type)) {
                    continue;
                }

                if (empty($tax->public)) {
                    continue;
                }

                $terms = new TaxonomyModel($tax->name);
                $terms = $terms->lang(Lang::getDefault())->getFlatHierarchy();

                foreach ($terms as $term) {
                    $choices[$tax->labels->singular_name][$tax->name . "|" . $term->term_id] = "[" . $tax->labels->singular_name . "] " . $term->name;
                }
            }

            return $choices;
        });
    }

    // ==================================================
    // > ACTIONS
    // ==================================================
    /**
     * Create a new post
     * see https://developer.wordpress.org/reference/functions/wp_insert_post/
     * @param  array  $attrs  The post attributes
     * @param  array  $fields Custom ACF fields with their values
     * @param  string $status Status for the post
     * @return self   A new model item instance containing the new item
     */
    public static function add($attrs = [], $fields = [], $tax = [])
    {
        // Create the post
        $post_id = wp_insert_post(array_merge([
            "post_type"    => static::TYPE,
            "post_title"   => "",
            "post_content" => "",
            "post_status"  => "publish",
        ], $attrs));

        if ($post_id instanceof \WP_Error) {
            return $post_id;
        }

        return static::getItem($post_id)->update(false, $fields, $tax);
    }

    /**
     * Add a comment to all matchin posts
     *
     * @param  string  $message
     * @param  string  $author_name
     * @param  string  $author_email
     * @param  string  $author_url
     * @param  integer $parent_comment
     * @return void
     */
    public function addComment($message, $author_name = "", $author_email = "", $author_url = "", $parent_comment = 0)
    {
        $this->callEach()->addComment($message, $author_name, $author_email, $author_url, $parent_comment);
    }
}