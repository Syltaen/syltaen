<?php

// ==================================================
// > CUSTOM QUERY VARS
// ==================================================
add_filter("query_vars", function ($public_query_vars) {
	$public_query_vars[] = "token";
	return $public_query_vars;
});


// ==================================================
// > ARCHIVE PAGINATION
// ==================================================
add_rewrite_rule('news/([0-9]*)/?$', 'index.php?pagename=news&page=$matches[1]', "top");