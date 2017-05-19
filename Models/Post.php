<?php

namespace Syltaen;
use WP_Query;

abstract class Post {

	// ==================================================
	// > POST TYPE REGISTRATION
	// ==================================================
	const TYPE     = "news";
	const LABEL    = "Articles";
	const ICON     = false; // https://developer.wordpress.org/resource/dashicons
	const SUPPORTS = array("title", "editor", "author", "thumbnail", "excerpt", "trackbacks", "custom-fields", "comments", "revisions", "page-attributes", "post-formats");
	const PUBLIK   = true;
	const REWRITE  = true; // Ex: array( "slug" => "agenda" );
	const TAX      = array("");

	public static function register() {
		return register_post_type(static::TYPE, array(
			"label"       => static::LABEL,
			"public"      => static::PUBLIK,
			"menu_icon"   => static::ICON,
			"supports"    => static::SUPPORTS,
			"rewrite"     => static::REWRITE,
			"has_archive" => false
		));
	}

	// ==================================================
	// > QUERY
	// ==================================================
	private $wp_query      = false;
	private $wp_query_args = array();

	public function __construct() {
		$this->wp_query_args = [
			"post_type"   => static::TYPE,
			"nopaging"    => true,
		];
	}

	// ==================================================
	// > SEARCH
	// ==================================================
	public function search($terms, $exclusive = false) {
		// "s"					=> "keyword",
		// "post_in"
	}

	// ==================================================
	// > FILTER : TAXONOMY
	// ==================================================
	public function filter($filter) {
		// "post_status" => array("publish", "pending", "draft", "future", "private", "trash", "any"),
	}

	public function addFilter($key, $filter) {

	}

	// ==================================================
	// > FILTER : META
	// ==================================================
	public function metaFiler() {
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

	// ==================================================
	// > GET POSTS
	// ==================================================
	public function get($perpage = false, $offset = 0) {
		$get_query = $this->wp_query_args;

		$this->wp_query = new WP_Query( $get_query );
		return static::populate( $this->query()->posts );
	}

	public function getOne($post_id) {
		return $post_id;
	}

	// ==================================================
	// > POPULATE WITH CUSTOM FIELD
	// ==================================================
	static protected function populate($posts) {
		return $posts;
	}

	// ==================================================
	// > GET QUERY ARGUMENTS
	// ==================================================
	public function query() {
		return $this->wp_query;
	}

	// ==================================================
	// > CREATE NEW POST
	// ==================================================
	public static function add() {

	}

	// ==================================================
	// > UPDATE
	// ==================================================
	public function update() {

	}

	// ==================================================
	// > DELETE
	// ==================================================
	public function delete() {

	}

}