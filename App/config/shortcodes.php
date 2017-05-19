<?php

// ==================================================
// > SHORTCODES
// ==================================================

// ========== PAGE TITLE ========== //
add_shortcode( "page_title", function () {
	return get_the_title();
} );