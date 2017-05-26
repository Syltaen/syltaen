
<?php

use Syltaen\Models\Taxonomies;
use Syltaen\Models\Posts;


// ==================================================
// > TAXONOMIES
// ==================================================
add_action('init', function() {

    Taxonomies\LocationTypes::register();

});


// ==================================================
// > POST TYPES
// ==================================================
add_action('init', function() {

    Posts\News::register();
    Posts\Locations::register();

});