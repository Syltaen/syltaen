<?php

namespace Syltaen;

class TaxonomyModel extends Model
{
    const SLUG         = "taxonomy";
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
    const OBJECT_KEY   = "terms";
    const ITEM_CLASS   = "ModelItemTaxonomy";
    const QUERY_IS     = "include";
    const QUERY_ISNT   = "exclude";
    const QUERY_LIMIT  = "number";
    const QUERY_PAGE   = "offset";
    const TYPE         = "taxonomy";


    /**
     * Should specify $taxonomyFields and $termsFields
     */
    public function __construct()
    {
        parent::__construct();

        $this->addFields([

            /**
             * The URL of each term.
             * May be altered with the "term_link" filter in routes.php
             */
            "@url" => function ($term) {
                return static::HAS_PAGE ? get_term_link($term) : false;
            },

            /**
             * The global description of each terms
             */
            "@description" => function ($term) {
                return term_description($term->term_id, static::getSlug());
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
            "taxonomy"   => static::getSlug(),
            "fields"     => "all",
            "hide_empty" => false
        ]);
    }

    /**
     * Filter by parent (only one allowed)
     *
     * @param int $id The parent ID
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
     * @param int $id The parent ID
     * @return self
     */
    public function childOf($id)
    {
        $this->filters["child_of"] = $id;
        return $this;
    }

    /**
     * Get terms for specific objects
     *
     * @param int $post_id The post ID
     */
    public function for($ids)
    {
        $this->filters["object_ids"] = (array) $ids;
        return $this;
    }

    /**
     * Offset the results to a certain page.
     * See https://codex.wordpress.org/Class_Reference/WP_User_Query#Pagination_Parameters
     * @param int $page
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
     * @return void
     */
    public function withPosts()
    {
        $this->filters["hide_empty"] = true;
        return $this;
    }



    /* Update parent method */
    public function search($search, $ignore = null, $ignore_2 = null)
    {
        $this->filters["search"] = $search;
        return $this;
    }


    /**
     * Set the fields to be returned
     *
     * @param string $fields Term fields to query for. Accepts:
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
     * @return array
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
     * @return array
     */
    public function getSlugs()
    {
        return $this->fields("slugs")->get();
    }

    /**
     * Shortcut for get with hide_empty set to false
     *
     * @param int $limit Number of posts to return
     * @param int $page Page offset to use
     * @return array of WP_Post
     */
    public function getAll($limit = false, $page = false)
    {
        return $this->showEmpty()->get();
    }

    /**
     * Return all terms as an associative array of slug=>name
     *
     * @return array
     */
    public function getAsOptions()
    {
        return $this->reduce(function ($options, $term) {
            $options[$term->slug] = $term->name;
            return $options;
        }, []);
    }

    /* Update parent method */
    public function count($paginated = true)
    {
        return count($this->getQuery()->terms); // No clean way to get total number of result
    }

    /* Update parent method */
    public function getPagesCount()
    {
        return 1;
    }

    /**
     * Embed children terms in their parents.
     *
     * @return void
     */
    public function getHierarchy()
    {
        if (!empty($this->hierarchy)) return $this->hierarchy;

        $hierarchy = [];
        $parents   = [];
        $terms     = $this->get();

        // Index by term_id
        $terms = Data::mapKey($terms, "term_id");

        // Parents index
        foreach ($terms as $term) {
            $parents[$term->parent] = $parents[$term->parent] ?? [];
            $parents[$term->parent][] = $term->term_id;
        }

        // Build hierarchy recusively
        foreach ($parents[0] as $term_id) {
            $term = $terms[$term_id];
            static::hierarchyAddTermChildren($term, $parents, $terms);
            $hierarchy[$term_id] = $term;
        }

        return $hierarchy;
    }

    /**
     * Recursive function to add term children in a hierarchy
     *
     * @param WP_Term $term
     * @param array $parents
     * @param array $terms
     * @return void
     */
    private static function hierarchyAddTermChildren(&$term, $parents, $terms)
    {
        $term->children = [];

        // No children for this term
        if (empty($parents[$term->term_id])) return;

        // Add children, with their own children
        foreach ($parents[$term->term_id] as $child_id) {
            $child = $terms[$child_id];
            static::hierarchyAddTermChildren($child, $parents, $terms);
            $term->children[$child->term_id] = $child;
        }
    }

    /**
     * Add the parent terms ids to a list of terms ids
     *
     * @param array $terms_ids
     * @return array
     */
    public static function addParentTerms($terms_ids)
    {
        global $wpdb;

        // No term ids to fetch parents for
        if (empty($terms_ids)) return [];

        // Get direct parents
        $parents = array_filter(array_map("intval", array_column($wpdb->get_results(
           "SELECT parent FROM $wpdb->term_taxonomy
            WHERE term_id IN (".implode(",", $terms_ids).") AND taxonomy='".static::getSlug()."'"
        ), "parent")));

        // Get parents of parents
        $parents = static::addParentTerms($parents);

        return array_values(array_unique(array_merge($parents, $terms_ids)));
    }

    /**
     * Add the children terms ids to a list of terms ids
     *
     * @param array $term_ids
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
     * @return void
     */
    public static function addAllTermsTranslations($terms)
    {
        if (empty($terms)) return [];
        $terms     = (array) $terms;
        $use_slugs = is_string($terms[0]);

        // Transform slugs into ids
        if ($use_slugs) {
            $terms_id = Database::get_col(
               "SELECT t.term_id FROM terms t
                JOIN term_taxonomy tt ON tt.term_id = t.term_id
                WHERE tt.taxonomy = '".static::SLUG."' AND t.slug IN " . Database::inArray($terms)
            );
        } else {
            $terms_id = $terms;
        }

        // Select all translations
        $relations = Database::get_col(
           "SELECT description FROM term_taxonomy
            WHERE taxonomy = 'term_translations' AND (" . implode(" OR ", array_map(function ($term_id) {
                return "description LIKE '%:{$term_id};%'";
            }, $terms_id)) .")
        ");

        $translations = empty($relations) ? [] : array_values(array_unique(array_reduce($relations, function ($terms, $relation) {
            return array_merge($terms, array_values(unserialize($relation)));
        }, [])));

        // Transform back into terms slugs
        if ($use_slugs && !empty($translations)) {
            $translations = Database::get_col("SELECT slug FROM terms WHERE term_id IN " . Database::inArray($translations));
        }

        return array_values(array_unique(array_merge($translations, $terms)));
    }


    // ==================================================
    // > POST FIELDS
    // ==================================================
    /**
     * Get all posts corresponding to each terms.

     * @param \Sytaen\Model\Posts $model the post model.
     * @param bool $include_children Specify if the terms children should be included too
     * @return self
     */
    public function addPosts($model, $include_children = true)
    {
        return $this->addFields([
            "@posts" => function ($term) use ($model, $include_children) {
                return $model->tax(static::getSlug(), $term->slug, "AND", true, "IN", $include_children)->get();
            }
        ]);
    }

    /**
     * Use a specific post model to recalculate term counts
     *
     * @param PostsModel $model
     * @return self
     */
    public function withCountsFor($model)
    {
        global $wpdb;

        // Clone the model and clean it
        $model = (clone $model)->limit(-1)->page(0)->order("date")->clearQueryModifiers("order");

        // Remove tax query on this taxonomy, if any
        if (!empty($model->filters["tax_query"])) {
            $model->filters["tax_query"] = array_values(array_filter($model->filters["tax_query"], function ($query) {
                return empty($query["taxonomy"]) || $query["taxonomy"] != static::getSlug();
            }));
        }

        // Keep only the IDs
        $ids = implode(",", $model->getIDs());

        // No ID : set all count to 0 and skip fetching
        if (empty($ids)) {
            return $this->addFields([
                "@count" => 0
            ])->fetchFields();
        }

        // Get counts
        $results = $wpdb->get_results(
           "SELECT count(*) as count, tt.term_id as term_id
           FROM {$wpdb->term_relationships} as tr
           JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = '".static::getSlug()."'
           WHERE tr.object_id IN ({$ids}) GROUP BY tr.term_taxonomy_id
        ");

        // Build index
        $index = [];
        foreach ($results as $result) $index[$result->term_id] = $result->count;

        // Register count modifier based on index
        return $this->addFields([
            "@count" => function ($term) use ($index) {
                if (empty($index[$term->term_id])) return 0;
                return (int) $index[$term->term_id];
            }
        ])->fetchFields();
    }


    // ==================================================
    // > CHECKERS
    // ==================================================
    /**
     * Check if one or several terms exists in the taxonomy
     *
     * @param array $terms The terms to look for
     * @return boolean
     */
    public static function termsExist($terms)
    {
        $terms = (array) $terms;
        foreach ($terms as $term) {
            if (!term_exists($term, static::getSlug())) return false;
        }
        return true;
    }


    // ==================================================
    // > CACHE
    // ==================================================
    /**
     * Clear all cached data, forcing new data fetching
     *
     * @return void
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
        register_taxonomy(static::getSlug(), null, array(
            "labels" => [
                "name"           => static::NAME
            ],
            "public"             => static::PUBLIK,
            "publicly_queryable" => static::HAS_PAGE,
            "show_admin_column"  => static::ADMIN_COLS,
            "hierarchical"       => static::HIERARCHICAL,
            "description"        => static::DESC
        ));

        return static::class;
    }

    /**
     * Get this taxonomy SLUG
     *
     * @return void
     */
    public static function getSlug()
    {
        return static::SLUG;
    }


    // ==================================================
    // > ACTIONS
    // ==================================================
    /**
     * Add a new term to the taxonomy
     *
     * @param string|array $name A single string, or an assoc list of translations
     * @param array $attrs See args here https://developer.wordpress.org/reference/functions/wp_insert_term/
     * @param array $fields Meta data
     * @return int|array of term IDs
     */
    public static function add($name, $attrs = [], $fields = [], $order = null)
    {
        $is_translated = is_array($name);
        $translations  = (array) $name;

        foreach ($translations as $lang=>$item) {

            // Create term
            $term = wp_insert_term($item, static::getSlug(), array_map(function ($value) use ($lang, $item) {
                if (is_callable($value) && !is_string($value)) return $value($lang, $item);
                return $value;
            }, $attrs));

            // Term already exists with this name (most probably)
            if (is_wp_error($term) || empty($term["term_id"])) {
                $translations[$lang] = 0;
                continue;
            }

            // Replace name with term_id for the return value
            $term_id             = $term["term_id"];
            $translations[$lang] = $term_id;


            // Update fields
            if ($fields) {
                (new ModelItemTaxonomy($term_id))->updateFields($fields);
            }

            // Set language
            if ($is_translated) {
                (new ModelItemTaxonomy($term_id))->updateLang($lang);
            }

            // Set order
            if ($order != null) {
                (new ModelItemTaxonomy($term_id))->updateOrder($order);
            }
        }

        // Set link between languages
        if ($is_translated) {
            pll_save_term_translations($translations);
        }

        return $translations;
    }


}