<?php

namespace Syltaen;

class PageController extends Controller {

	static $default_view = "page";

	// ==================================================
	// > CONSTRUCT
	// ==================================================
	public function __construct( $auto = true ) {
		parent::__construct( $auto );
	}


	// ==================================================
	// > POPULATE
	// ==================================================
	public function populate() {
		global $post;

	}

	// ==================================================
	// > RESTRICT
	// ==================================================
	public function restrict() {
		wp_redirect( site_url("connexion") );
	}

}