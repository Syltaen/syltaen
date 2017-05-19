<?php

namespace Syltaen;

class HomeController extends PageController {

	static $default_view = "home";

	// ==================================================
	// > POPULATE
	// ==================================================
	public function populate() {
		global $post;

		// ========== INTRO ========== //
		$this->data["intro_content"] = get_field("intro_content");
		$this->data["intro_image"]   = get_field("intro_image");

		// ========== GATES ========== //

		// ========== NEWS ========== //
		$this->data["news_last"] = ( new News() )->get(3);
		$this->data["news_link"] = site_url("news");

		// ========== KEY FIGURES ========== //

		// ========== GATES 2 ========== //

		// ========== PRESENCE ========== //

	}

}