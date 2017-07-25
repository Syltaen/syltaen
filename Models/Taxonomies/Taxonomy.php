<?php

namespace Syltaen;

abstract class Taxonomy
{

    const SLUG         = "taxonomy";
    const NAME         = "Name of the taxonomy";
    const DESC         = "A description for the taxonomy";
    const PUBLIK       = true;
    const ADMIN_COLS   = true;
    const HIERARCHICAL = true;

    protected $taxonomy;
    protected $taxonomyFields;
    protected $taxonomyFieldsOptionPage;
    protected $terms;
    protected $termsFields;

    /**
     * Should specify $taxonomyFields and $termsFields
     */
    public function __construct()
    {

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
     * @return array List of terms
     * see https://developer.wordpress.org/reference/functions/get_terms/
     */
    public function getTerms($fields = "all", $hide_empty = false, $limit = 0, $orderby = "slug", $order = "ASC")
    {
        $this->terms = get_terms([
            "taxonomy"   => static::SLUG,
            "fields"     => $fields,
            "hide_empty" => $hide_empty,
            "number"     => $limit,
            "order"      => $order,
            "orderby"    => $orderby
        ]);

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

        return $this->terms;
    }

    public function getPostTerms($post_id, $fields = "all", $orderby = "slug", $order = "ASC")
    {
        $this->terms = wp_get_post_terms($post_id, static::SLUG, [
            "fields"     => $fields,
            "order"      => $order,
            "orderby"    => $orderby
        ]);

        if ($fields == "all") {
            foreach($this->terms as $term) {
                $this->populateTermFields($term);
            }
        }

        return $this->terms;
    }


    /**
     * Get all posts corresponding to each terms.
     * Extend the $terms parameter to store each corresponding posts.
     * @param \Sytaen\Model\Posts $model the post model.
     * @param boolean $hide_empty Prevent the return of unused terms
     * @return array List of terms each storing a list of posts
     */
    public function getPosts($model, $hide_empty = true)
    {
        $this->getTerms("all", $hide_empty);
        foreach ($this->terms as $term) {
            $term->posts = $model->tax(static::SLUG, $term->slug, "AND", true)->get();
        }

        return $this->terms;
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
    protected function populateTaxonomyFields()
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
    protected function populateTermFields(&$term)
    {
        Data::store($term, $this->termsFields, "term_".$term->term_id);
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
            "publicly_queryable" => false,
            "show_admin_column"  => static::ADMIN_COLS,
            "hierarchical"       => static::HIERARCHICAL,
            "description"        => static::DESC
        ));

        return static::class;
    }

    /**
     * Bind this taxonomy to a Models\Posts\Posts children
     *
     * @param array $posts_models
     * @return void
     */
    public static function useFor($posts_models)
    {
        $posts_models = (array) $posts_models;

        foreach ($posts_models as $model) {
            register_taxonomy_for_object_type(
                static::SLUG,
                $model::TYPE
            );
        }
    }


}