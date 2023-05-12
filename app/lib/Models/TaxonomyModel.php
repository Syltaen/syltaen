<?php

namespace Syltaen;

class TaxonomyModel extends Model
{
    const SLUG         = "";
    const NAME         = "Name of the taxonomy";
    const DESC         = "";
    const PUBLIK       = true;
    const ADMIN_COLS   = true;
    const HIERARCHICAL = true;
    const HAS_PAGE     = false;

    /**
     * Query arguments used by the model's methods
     */
    const QUERY_CLASS  = "WP_Term_Query";
    const OBJECT_CLASS = "WP_Term";
    const ITEM_CLASS   = "\Syltaen\Term";
    const OBJECT_KEY   = "terms";
    const QUERY_IS     = "include";
    const QUERY_ISNT   = "exclude";
    const QUERY_LIMIT  = "number";
    const QUERY_PAGE   = "offset";
    const TYPE         = "taxonomy";
    const QUERY_HOOK   = "terms_clauses";
    const META_TABLE   = "termmeta";
    const META_OBJECT  = "term_id";

    /**
     * Hold a variable reference of the slug, allow for dynamic taxonomy models
     *
     * @var string
     */
    public static $slug = false;

    /**
     * Should specify $taxonomyFields and $termsFields
     */
    public function __construct($slug = false)
    {
        static::$slug = $slug ?: static::SLUG;

        parent::__construct();

        $this->addFields([

            /**
             * The URL of each term.
             * May be altered with the "term_link" filter in routes.php
             */
            "@url"         => function ($term) {
                return static::HAS_PAGE ? get_term_link($term) : false;
            },

            /**
             * The global description of each terms
             */
            "@description" => function ($term) {
                return term_description($term->term_id, static::getSlug());
            },
        ]);
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
        return parent::clearFilters($filter_keys, [
            "taxonomy"   => static::getSlug(),
            "fields"     => "all",
            "hide_empty" => false,
        ]);
    }

    /**
     * Filter by parent (only one allowed)
     *
     * @param  int    $id The parent ID
     * @return self
     */
    public function parent($id)
    {
        $this->filters["parent"] = $id;
        return $this;
    }

    /**
     * Fetch the parent of the given term (only on allowed)
     *
     * @param  int    $id The parent ID
     * @return self
     */
    public function childOf($id)
    {
        $this->filters["child_of"] = $id;
        return $this;
    }

    /**
     * Get terms by slug
     *
     * @return self
     */
    public function bySlug($slugs)
    {
        $this->filters["slug"] = (array) $slugs;
        return $this;
    }

    /**
     * Get terms for specific objects
     *
     * @param int $post_id The post ID
     */
    function for ($ids) {
        $this->filters["object_ids"] = (array) $ids;
        return $this;
    }

    /**
     * Offset the results to a certain page.
     * See https://codex.wordpress.org/Class_Reference/WP_User_Query#Pagination_Parameters
     * @param  int    $page
     * @return self
     */
    public function page($page = false)
    {
        if ($page) {
            $this->filters[static::QUERY_PAGE] = ($page - 1) * ($this->filters[static::QUERY_LIMIT] ?? 0);
        }
        return $this;
    }

    /**
     * Only show terms that have at least one post
     *
     * @return self
     */
    public function hideEmpty()
    {
        $this->filters["hide_empty"] = true;

        $this->addResultFilter(function ($item) {
            return !isset($item->count) || $item->count > 0;
        });

        return $this;
    }

    /* Update parent method */
    /**
     * @param  $search
     * @param  $ignore
     * @param  null      $ignore_2
     * @return mixed
     */
    public function search($search, $ignore = null, $ignore_2 = null)
    {
        $this->filters["search"] = $search;
        return $this;
    }

    /**
     * Set the fields to be returned
     *
     * 'all' Returns an array of complete term objects (`WP_Term[]`).
     * 'all_with_object_id' Returns an array of term objects with the 'object_id' param (`WP_Term[]`). Works only when the `$object_ids` parameter is populated.
     * 'ids' Returns an array of term IDs (`int[]`).
     * 'tt_ids' Returns an array of term taxonomy IDs (`int[]`).
     * 'names' Returns an array of term names (`string[]`).
     * 'slugs' Returns an array of term slugs (`string[]`).
     * 'count' Returns the number of matching terms (`int`).
     * 'id=>parent' Returns an associative array of parent term IDs, keyed by term ID (`int[]`).
     * 'id=>name' Returns an associative array of term names, keyed by term ID (`string[]`).
     * 'id=>slug' Returns an associative array of term slugs, keyed by term ID (`string[]`).
     * @param  string  $fields Term fields to query for. Accepts:
     * @return self
     */
    public function fields($fields)
    {
        $this->filters["fields"] = $fields;
        return $this;
    }

    // ==================================================
    // > GETTERS - TERMS
    // ==================================================
    /**
     * Only return the matching items' IDs
     *
     * @return Set
     */
    public function getSlugs()
    {
        return $this->fields("slugs")->get();
    }

    /**
     * Get only the names
     *
     * @return Set
     */
    public function getNames()
    {
        return $this->fields("names")->get();
    }

    /**
     * Get every result as a anchor tag to its page
     *
     * @return Set
     */
    public function getLinks($class = "")
    {
        return $this->fields("all")->map(function ($term) use ($class) {
            $class = is_array($class) ? implode(" ", $class) : $class;
            return "<a class='$class' href='{$term->url}'>$term->name</a>";
        });
    }

    /**
     * Return all terms as an associative array of slug=>name
     *
     * @param  bool  $all_option Add a "All" option
     * @return Set
     */
    public function getAsOptions($all_option = false)
    {
        $options = $this->get()->index("slug", "name");

        if ($all_option) {
            $options = $options->insert(["*" => $all_option === true ? __("All", "syltaen") : $all_option], 0);
        }

        return (array) $options;
    }

    /* Update parent method */
    /**
     * @param $paginated
     */
    public function count($paginated = true)
    {
        return empty($this->getQuery()->terms) ? 0 : count($this->getQuery()->terms); // No clean way to get total number of result
    }

    /* Update parent method */
    /**
     * @return int
     */
    public function getPagesCount()
    {
        return 1;
    }

    /**
     * Embed children terms in their parents.
     *
     * @return array
     */
    public function getHierarchy()
    {
        if (!empty($this->hierarchy)) {
            return $this->hierarchy;
        }

        $hierarchy = [];
        $parents   = [];
        $terms     = $this->get();

        // Index by term_id
        $terms = $terms->index("term_id");
        if ($terms->empty()) {
            return [];
        }

        // Parents index
        foreach ($terms as $term) {
            $parents[$term->parent]   = $parents[$term->parent] ?? [];
            $parents[$term->parent][] = $term->term_id;
        }

        // Build hierarchy recusively
        foreach ($parents[0] as $term_id) {
            $term = $terms[$term_id];
            static::hierarchyAddTermChildren($term, $parents, $terms);
            $hierarchy[$term_id] = $term;
        }

        $this->hierarchy = $hierarchy;
        return $hierarchy;
    }

    /**
     * Recursive function to add term children in a hierarchy
     *
     * @param  WP_Term $term
     * @param  array   $parents
     * @param  array   $terms
     * @return void
     */
    private static function hierarchyAddTermChildren(&$term, $parents, $terms)
    {
        $term->children = [];

        // No children for this term
        if (empty($parents[$term->term_id])) {
            return;
        }

        // Add children, with their own children
        foreach ($parents[$term->term_id] as $child_id) {
            $child = $terms[$child_id];
            static::hierarchyAddTermChildren($child, $parents, $terms);
            $term->children[$child->term_id] = $child;
        }
    }

    /**
     * Get the hierarchy in a flat format
     *
     * @return array
     */
    public function getFlatHierarchy()
    {
        $hierarchy = $this->getHierarchy();
        $list      = [];

        foreach ($hierarchy as $term) {
            static::hierarchyAddFlatTerm($list, $term);
        }

        return $list;
    }

    /**
     * @param array  $list
     * @param Term   $term
     * @param string $path
     */
    public static function hierarchyAddFlatTerm(&$list, $term, $path = "")
    {
        $term->name = $path . $term->name;
        $path       = $term->name . " > ";
        $list[]     = $term;

        foreach ($term->children as $children) {
            static::hierarchyAddFlatTerm($list, $children, $path);
        }
    }

    /**
     * Add the parent terms ids to a list of terms ids
     *
     * @param  array   $terms_ids
     * @return array
     */
    public static function addParentTerms($terms_ids)
    {
        global $wpdb;

        $terms_ids = (array) $terms_ids;

        // No term ids to fetch parents for
        if (empty($terms_ids)) {
            return [];
        }

        // Get direct parents
        $parents = array_filter(array_map("intval", array_column($wpdb->get_results(
            "SELECT parent FROM $wpdb->term_taxonomy
            WHERE term_id IN (" . implode(",", $terms_ids) . ") AND taxonomy='" . static::getSlug() . "'"
        ), "parent")));

        // Get parents of parents
        $parents = static::addParentTerms($parents);

        return array_values(array_unique(array_merge($parents, $terms_ids)));
    }

    /**
     * Add the children terms ids to a list of terms ids
     *
     * @param  array   $term_ids
     * @return array
     */
    public static function addChildrenTerms($term_ids)
    {
        return array_reduce($term_ids, function ($list, $term_id) {
            return array_unique(array_merge($list, [$term_id], get_term_children($term_id, static::getSlug())));
        }, []);
    }

    /**
     * Add all the translations possible for the matching terms
     *
     * @return Set
     */
    public static function addAllTermsTranslations($terms)
    {
        $terms = set($terms);

        if ($terms->empty()) {
            return [];
        }

        $use_slugs = is_string($terms[0]);

        // Transform slugs into ids
        if ($use_slugs) {
            $terms_id = static::slugsToIDs($terms);
        } else {
            $terms_id = $terms;
        }

        // Select all translations
        $translations = Database::get_col(
            "SELECT description FROM term_taxonomy
            WHERE taxonomy = 'term_translations' AND (" . $terms_id->map(function ($term_id) {
                return "description LIKE '%:{$term_id};%'";
            })->join(" OR ") . ")
        ")->reduce(function ($translations, $relation) {
            return $translations->merge(array_values(unserialize($relation)));
        })->unique();

        // Transform back into terms slugs
        if ($use_slugs && $translations->count()) {
            $translations = Database::get_col("SELECT slug FROM terms WHERE term_id IN " . Database::inArray($translations));
        }

        return $terms->merge($translations)->unique();
    }

    /**
     * Transform a list of slugs into their IDs equivalent
     *
     * @param  array   $slugs
     * @return array
     */
    public static function slugsToIDs($slugs)
    {
        return Database::get_results(
            "SELECT t.term_id, t.slug FROM terms t
            JOIN term_taxonomy tt ON tt.term_id = t.term_id
            WHERE tt.taxonomy = '" . static::getSlug() . "' AND t.slug IN " . Database::inArray($slugs)
        )->index("slug", "term_id");
    }

    /**
     * Normalize a list of terms.
     * For example: make sure it's a list of names and not a mix.
     * Or create  missing terms and get the new IDs.
     *
     * @param  array   $terms      A single or a list of term names or ids. Do NOT provide slugs, as they will be considered as names.
     * @param  string  $term_field What term field should be used. Either "name", "term_id" or "slug".
     * @return array
     */
    public function normalize($terms, $term_field = "name")
    {
        if (empty($terms)) {return [];}

        // Create an index of all the terms and cache it, so that several normalization can be done quickly
        $index = Cache::value("tax_normalize_index:" . static::getSlug(), function () {
            return $this->get();
        });

        // Process each terms
        $normalized_terms = set((array) $terms)->unique()->map(function ($term) use ($index, $term_field) {
            // Not a name or an ID, should not happen
            if (!is_string($term) && !is_int($term)) {
                return false;
            }
            $match = false;
            // Get by name
            if (is_string($term)) {
                $match = $index->getBy("name", $term);
            }
            // Get by ID
            if (is_int($term)) {
                $match = $index->getBy("term_id", $term);
            }
            // Was not found, create it by name with a lang suffix to avoid collisions
            if (empty($match)) {
                if (is_int($term)) {return false;}
                $created_term = wp_insert_term($term, static::getSlug(), ["slug" => Lang::suffixed(sanitize_title($term))]);
                if (is_wp_error($created_term) || empty($created_term["term_id"])) {return false;}
                $match = get_term_by("id", $created_term["term_id"], static::getSlug());
                $index->push($match);
            }

            return $match->{$term_field};
        })->filter();

        // Return the one result we want, or all of the results
        if (is_scalar($terms)) {return $normalized_terms[0] ?? false;}
        return (array) $normalized_terms;
    }

    // ==================================================
    // > POST FIELDS
    // ==================================================
    /**
     * Get all posts corresponding to each terms.
     *
     * @param  \Sytaen\Model\Posts $model            the post model.
     * @param  bool                $include_children Specify if the terms children should be included too
     * @return self
     */
    public function addPosts($model, $include_children = true)
    {
        return $this->addFields([
            "@posts" => function ($term) use ($model, $include_children) {
                return $model->tax(static::getSlug(), $term->slug, "AND", true, "IN", $include_children)->get();
            },
        ]);
    }

    /**
     * Use a specific post model to recalculate term counts
     *
     * @param  PostsModel $model
     * @return self
     */
    public function withCountsFor($model)
    {
        // Clone the model and clean it
        $model = (clone $model)->limit(-1)->page(0)->order("date")->clearQueryModifiers("order");

        // Remove tax query on this taxonomy, if any
        if (!empty($model->filters["tax_query"])) {
            $model->filters["tax_query"] = array_values(array_filter($model->filters["tax_query"], function ($query) {
                return empty($query["taxonomy"]) || $query["taxonomy"] != static::getSlug();
            }));
        }

        // Keep only the IDs
        $ids = $model->getIDs()->join(",");

        // No ID : set all count to 0 and skip fetching
        if (empty($ids)) {
            return $this->addFields(["@count" => 0])->fetchFields("count");
        }

        // Get counts
        $results = Database::get_results(
            "SELECT count(*) as count, tt.term_id as term_id
           FROM term_relationships tr
           JOIN term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = '" . static::getSlug() . "'
           WHERE tr.object_id IN ({$ids}) GROUP BY tr.term_taxonomy_id
        ")->index("term_id", "count");

        // Register count modifier based on index
        return $this->addFields([
            "@count" => function ($term) use ($results) {
                if (empty($results[$term->term_id])) {
                    return 0;
                }

                return (int) $results[$term->term_id];
            },
        ])->fetchFields("count");
    }

    // ==================================================
    // > CHECKERS
    // ==================================================
    /**
     * Check if one or several terms exists in the taxonomy
     *
     * @param  array     $terms The terms to look for
     * @return boolean
     */
    public static function termsExist($terms)
    {
        $terms = (array) $terms;
        foreach ($terms as $term) {
            if (!term_exists($term, static::getSlug())) {
                return false;
            }

        }
        return true;
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
    public static function linkTranslations($terms)
    {
        $terms = static::parseTranslationsList($terms);

        if (empty($terms)) {
            return false;
        }

        return pll_save_term_translations($terms);
    }

    // ==================================================
    // > CACHE
    // ==================================================
    /**
     * Clear all cached data, forcing new data fetching
     *
     * @return self
     */
    public function clearCache()
    {
        $this->hierarchy = false;
        return parent::clearCache();
    }

    // ==================================================
    // > TAXONOMY REGISTRATION
    // ==================================================
    /**
     * Register a new taxonomy
     * @see https://codex.wordpress.org/Function_Reference/register_taxonomy
     *
     * @return class
     */
    public static function register()
    {
        register_taxonomy(static::getSlug(), null, [
            "labels"             => [
                "name" => static::NAME,
            ],
            "public"             => static::PUBLIK,
            "publicly_queryable" => static::HAS_PAGE,
            "show_admin_column"  => static::ADMIN_COLS,
            "hierarchical"       => static::HIERARCHICAL,
            "description"        => static::DESC,
            "rewrite"            => static::HAS_PAGE,
        ]);

        return static::class;
    }

    /**
     * Get this taxonomy SLUG
     *
     * @return string
     */
    public static function getSlug()
    {
        return static::SLUG ?: static::$slug;
    }

    /**
     * Get this taxonomy name, allow for translation
     *
     * @param  bool     $singular
     * @return string
     */
    public static function getName($singular = false)
    {
        return static::NAME;
    }

    /**
     * Get an object instance
     *
     * @return WP_Term
     */
    public static function getObject($id)
    {
        $class = static::OBJECT_CLASS;
        return $class::get_instance($id, static::getSlug());
    }

    /**
     * Get an item instance, but only with an ID (prevent useless DB queries)
     *
     * @return Term
     */
    public static function getLightItem($id)
    {
        $class = static::ITEM_CLASS;
        return new $class($id, static::getSlug());
    }

    /**
     * Get all the IDs of this model's objects
     *
     * @return array
     */
    public static function getAllIDs()
    {
        return (array) Database::get_col("SELECT term_id FROM term_taxonomy WHERE taxonomy = '" . static::getSlug() . "'");
    }

    // ==================================================
    // > ACTIONS
    // ==================================================
    /**
     * Add a new term to the taxonomy
     *
     * @param  string|array $name   A single string, or an assoc list of translations
     * @param  array        $attrs  See args here https://developer.wordpress.org/reference/functions/wp_insert_term/
     * @param  array        $fields Meta data
     * @return ModelItem    of term IDs
     */
    public static function add($name, $attrs = [], $fields = [])
    {
        $term = wp_insert_term($name, static::getSlug(), $attrs);

        if ($term instanceof \WP_Error || empty($term["term_id"])) {
            return $term;
        }

        $term = new Term($term["term_id"], static::getSlug());

        // Set fields if any
        if (!empty($fields)) {
            $term->setFields($fields);
        }

        return $term;
    }
}