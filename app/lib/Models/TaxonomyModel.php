<?php

namespace Syltaen;

abstract class TaxonomyModel
{

    const SLUG         = "taxonomy";
    const NAME         = "Name of the taxonomy";
    const DESC         = "A description for the taxonomy";
    const PUBLIK       = true;
    const ADMIN_COLS   = true;
    const HIERARCHICAL = true;
    const HAS_PAGE     = false;

    public $taxonomy;
    public $taxonomyFields;
    public $taxonomyFieldsOptionPage;
    public $terms;
    public $termsFields = [];

    /**
     * Should specify $taxonomyFields and $termsFields
     */
    public function __construct()
    {
        $this->termsFields = array_merge($this->termsFields, [
            "@description" => function ($term) {
                return term_description($term->term_id, static::SLUG);
            },
        ]);
    }

    // ==================================================
    // > GETTERS
    // ==================================================
    /**
     * Get the taxonomy object
     * see https://developer.wordpress.org/reference/functions/get_taxonomy/
     * @return WP_Taxonomy
     */
    public function get()
    {
        $this->taxonomy = $this->taxonomy ?: get_taxonomy(static::SLUG);
        return $this->populateTaxonomyFields()->taxonomy;
    }


    /**
     * Get all taxonomy terms
     *
     * @param string $fields Fields to return : all, ids, id=>parent, names, count, id=>name, id=>slug
     * @param boolean $hide_empty Prevent the return of unused terms
     * @param int $limit Number of terms to return
     * @param string $orderby name, slug, term_group, term_id, id, description, count, ...
     * @param string $order ASC or DESC
     * @param array $custom_args Additional query arguments
     * @return array List of terms
     * see https://developer.wordpress.org/reference/functions/get_terms/
     */
    public function fetchTerms($fields = "all", $hide_empty = false, $limit = 0, $orderby = "slug", $order = "ASC", $custom_args = [])
    {
        $args = array_merge([
            "taxonomy"   => static::SLUG,
            "fields"     => $fields,
            "hide_empty" => $hide_empty,
            "number"     => $limit,
            "order"      => $order,
            "orderby"    => $orderby,
        ], $custom_args);

        $this->terms = get_terms($args);

        if ($fields == "all") {
            foreach($this->terms as $term) {
                $this->populateTermFields($term);
            }
            $terms = $this->terms;
            $this->terms = [];
            foreach ($terms as $term) {
                $this->terms[$term->slug] = $term;
            }
        }

        return $this;
    }


    /**
     * Run fetchTerms and return the resulting terms
     *
     * @return array
     */
    public function getTerms($fields = "all", $hide_empty = false, $limit = 0, $orderby = "slug", $order = "ASC", $custom_args = [])
    {
        return $this->fetchTerms($fields, $hide_empty, $limit, $orderby, $order, $custom_args)->terms;
    }


    /**
     * Get all posts corresponding to each terms.
     * Extend the $terms parameter to store each corresponding posts.
     * @param \Sytaen\Model\Posts $model the post model.
     * @param boolean $hide_empty Prevent the return of unused terms
     * @return array List of terms each storing a list of posts
     */
    public function fetchPosts($model, $hide_empty = true, $children = true)
    {
        if (!$this->terms) $this->fetchTerms("all", $hide_empty);

        foreach ($this->terms as $term) {
            $term->posts = $model->tax(static::SLUG, $term->slug, "AND", true, "IN", $children)->get();
        }

        return $this;
    }

    /**
     * Run fetchPosts and return the result
     */
    public function getPosts($model, $hide_empty = true, $children = true)
    {
        return $this->fetchPosts($model, $hide_empty, $children)->terms;
    }


    /**
     * Get terms for a specific post
     *
     * @param int $post_id The post ID
     * @param string $fields The fields to retrieve
     * @param string $orderby Order key
     * @param string $order Order
     * @return array of terms
     */
    public function getPostTerms($post_id, $fields = "all", $orderby = "slug", $order = "ASC")
    {
        $this->terms = wp_get_post_terms($post_id, static::SLUG, [
            "fields"     => $fields,
            "order"      => $order,
            "orderby"    => $orderby
        ]);

        if (is_wp_error($this->terms)) {
            $this->terms = [];
            return $this->terms;
        }

        if ($fields == "all") {
            foreach($this->terms as $term) {
                $this->populateTermFields($term);
            }
        }

        return $this->terms;
    }

    /**
     * Embed children terms in their parents.
     *
     * @return void
     */
    public function getTermsHierarchy()
    {
        if (!$this->terms) $this->fetchTerms("all", $hide_empty);
        $hierarchy = [];
        $terms     = $this->terms;

        while (!empty($terms)) {
            foreach ($terms as $slug=>$term) {

                // First level : add the term to the list
                if (!$term->parent) {
                    $term->children = [];
                    $hierarchy[] = $term;
                }

                // Children : add to its parent
                else {

                    $found = false;
                    foreach ($hierarchy as $parent) {
                        if ($parent->term_id == $term->parent) {
                            $parent->children[] = $term;
                            $found = true;
                            break;
                        }
                    }

                    // Parent not listed, skip and try again
                    if (!$found) continue;
                }

                unset($terms[$slug]);
            }
        }

        return $hierarchy;
    }



    // ==================================================
    // > DATA HANDLING FOR THE TAXONOMY AND ITS TERMS
    // ==================================================
    /**
     * Add all Custom Fields's values specified in the model's constructor to $this->taxonomy
     * Note : ACF does not support custom fields for taxonomy, only form terms.
     *        Each field should be in an option page ($taxonomyFieldsOptionPage).
     * @return self
     */
    public function populateTaxonomyFields()
    {
        if ($this->taxonomyFields && $this->taxonomyFieldsOptionPage && !empty($this->taxonomyFields)) {
            Data::store($this->taxonomy, $this->taxonomyFields, $this->taxonomyFieldsOptionPage);
        }
        return $this;
    }

    /**
     * Add all Custom Fields's values specified in the model's constructor to each $this->terms
     *
     * @return self
     */
    public function populateTermFields(&$term)
    {
        // Fields
        Data::store($term, $this->termsFields, "term_".$term->term_id);

        // Public URL
        if (static::HAS_PAGE) {
            $term->url = get_term_link($term->slug, static::SLUG);
        }

        return $this;
    }


    /**
     * Add fields to populate each terms
     *
     * @param [type] $fields
     * @return void
     */
    public function addTermFields($fields)
    {
        $this->termsFields = array_merge($this->termsFields, (array) $fields);
        $this->clearCache();
        return $this;
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
    public function termsExist($terms)
    {
        $terms = (array) $terms;
        foreach ($terms as $term) {
            if (!term_exists($term, static::SLUG)) return false;
        }
        return true;
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
        register_taxonomy(static::SLUG, null, array(
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
     * Clear the cached terms
     *
     * @return self
     */
    public function clearCache()
    {
        $this->terms = null;
        return $this;
    }

    // ==================================================
    // > ACTIONS
    // ==================================================
    /**
     * Add a new term to the taxonomy
     *
     * @return void
     */
    public static function addTerm($term, $args = [], $fields = [])
    {
        $term = wp_insert_term($term, static::SLUG, $args);

        if ($term instanceof \WP_Error) return $term;

        if (!empty($fields)) {
            static::updateFields($term, $fields);
        }

        return $term;
    }

    /**
     * Add a term fields
     *
     * @return void
     */
    public static function updateFields($term, $fields, $merge = false)
    {
        $term_id = is_int($term) ? $term : ((array) $term)["term_id"];

        foreach ((array) $fields as $key=>$value) {
            if (is_callable($value) && !is_string($value)) $value = $value($result);
            Data::update($key, $value, "term_{$term_id}", $merge);
        }
    }


}