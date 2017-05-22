<?php

namespace Syltaen\Models;

abstract class Post
{

    const TYPE     = "news";
    const LABEL    = "Articles";
    const ICON     = false; // https://developer.wordpress.org/resource/dashicons
    const SUPPORTS = array("title", "editor", "author", "thumbnail", "excerpt", "trackbacks", "custom-fields", "comments", "revisions", "page-attributes", "post-formats");
    const PUBLIK   = true;
    const REWRITE  = true; // Ex: array( "slug" => "agenda" );
    const TAX      = array("");

    private $wp_query      = false;
    private $wp_query_args = array();

    /**
     * Register a post type using the class constants
     *
     * @return void
     */
    public static function register()
    {
        return register_post_type(static::TYPE, array(
            "label"       => static::LABEL,
            "public"      => static::PUBLIK,
            "menu_icon"   => static::ICON,
            "supports"    => static::SUPPORTS,
            "rewrite"     => static::REWRITE,
            "has_archive" => false
        ));
    }


    /**
     * Create a base query to instract with
     */
    public function __construct()
    {
        $this->wp_query_args = [
            "post_type"   => static::TYPE,
            "nopaging"    => true,
        ];
    }

    /**
     * Add search filter to the query
     *
     * @param $terms
     * @param boolean $exclusive : Specify if the search is incluive (||) or inclusive (&&)
     * @return void
     */
    public function search($terms, $exclusive = false)
    {
        // "s" => "keyword",
        // "post_in"
    }

    /**
     * Update one of the query's argument
     *
     * @param string $filter
     * @return void
     */
    public function filter($filter)
    {
        // "post_status" => array("publish", "pending", "draft", "future", "private", "trash", "any"),
    }


    public function metaFilter()
    {
            // "tax_query"			=> array(
            // 	"relation"		=> "AND",
            // 	array(
            // 		'taxonomy'	=> 'tax',
            // 		'field'		=> 'slug',
            // 		'terms'		=> array("term_1", "term_2")
            // 	),
            // 	array(
            // 		'taxonomy'	=> 'tax',
            // 		'field'		=> 'slug',
            // 		'terms'		=> array("term_1", "term_2")
            // 	),
            // ),
            // "meta_query"			=> array(
            // 	"relation"		=> "AND",
            // 	array(
            // 		'key'		=> 'key',
            // 		'compare'	=> 'EXISTS',
            // 	),
            // 	array(
            // 		'key'		=> 'key',
            // 		'value'		=> array("2017-01-01", "2018-01-01"),
            // 		'compare'	=> 'BETWEEN',
            // 		'type'		=> 'DATE'
            // 	),
            // )
    }


    /**
     * Update the query to only retrive one post
     *
     * @param [type] $post_id
     * @return void
     */
    public function one($post_id)
    {
        return $post_id;
    }

    /**
     * Execute the query and retrive all the found posts
     *
     * @param boolean $perpage
     * @param int $offset
     * @return void
     */
    public function get($perpage = false, $offset = 0)
    {
        return static::addFields( $this->query()->posts );
    }

    /**
     * Add Custom Fields's values to matching posts
     *
     * @param $posts
     * @return $posts
     */
    static protected function addFields($posts)
    {
        return $posts;
    }

    /**
     * Get the query
     *
     * @return WP_Query
     */
    public function query()
    {
        return new \WP_Query( $this->wp_query_args );
    }

    /**
     * Create a new post
     *
     * @param string $title
     * @param string $content
     * @param boolean $fields
     * @return void
     */
    public static function add($title = "", $content = "", $fields = false)
    {

    }

    /**
     * Update all posts matching the query
     *
     * @param [type] $post_attrs
     * @param [type] $filds
     * @return void
     */
    public function update($post_attrs, $filds)
    {
        foreach ($this->posts as $p) {
            //
        }
    }

    /**
     * Delete all posts matching the query
     *
     * @param boolean $force : Completely remove the posts instead of placing them in the trash
     * @return void
     */
    public function delete($force = false)
    {
        foreach ($this->posts as $p) {
            wp_delete_post($p->id, $force);
        }
    }

}