<?php

namespace Syltaen;

class News extends Post {

	const TYPE     = "news";
	const LABEL    = "News";
	const ICON     = "dashicons-megaphone";
	const SUPPORTS = array("title", "editor", "excerpt", "thumbnail");

	static protected function populate($news) {

		return $news;
	}

}

add_action( 'init', function() { News::register(); } );
