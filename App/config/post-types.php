<?php

namespace Syltaen\Models;

add_action('init', function() {

    News::register();
    Locations::register();

});
