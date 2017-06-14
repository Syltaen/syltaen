<?php

namespace Syltaen\Controllers\Pages;


// ==================================================
// > 404
// ==================================================
if (is_404()) {

    (new Page(true))->error404();

// ==================================================
// > SEARCH
// ==================================================
} elseif (is_search()) {

    (new Page(true))->search(get_search_query());

// ==================================================
// > NINJA FORM PREVIEW
// ==================================================
} elseif (isset($_GET["nf_preview_form"])) {

    (new Page(true))->ninjaFormPreview($_GET["nf_preview_form"]);


// ==================================================
// > SINGLES
// ==================================================
} elseif (is_single()) {

    (new Single)->render();

// ==================================================
// > HOMEPAGE
// ==================================================
} elseif ( is_home() || is_front_page() ) {

    (new Home)->render();

// ==================================================
// > PAGES
// ==================================================
} else {

    (new Page)->render();

}

// use Syltaen\App\Services\Routes;

// Routes::get("404", "Page@error404");

// Routes::get("home", "Page@home");


