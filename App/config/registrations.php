<?php

use Syltaen\Models\Taxonomies;
use Syltaen\Models\Posts;
use Syltaen\App\Services\Forms\NinjaForms;


// ==================================================
// > TAXONOMIES
// ==================================================
add_action("init", function() {

    Taxonomies\LocationTypes::register();
    Taxonomies\Countries::register();

});


// ==================================================
// > POST TYPES
// ==================================================
add_action("init", function() {

    Posts\News::register();
    Posts\Locations::register();
    Posts\Jobs::register();
    Posts\Applications::register();
    Posts\Press::register();

});
