<?php

namespace Syltaen;

class SingleController extends PageController {

	static $default_view = "single";

	// ==================================================
	// > CONSTRUCT
	// ==================================================
	public function __construct($post_type, $auto = true ) {
		$this->post_type = $post_type;
		parent::__construct( $auto );
	}

	// ==================================================
	// > POPULATE
	// ==================================================
	public function populate() {
		global $post;
		$this->data["post"] = $post;
	}

}