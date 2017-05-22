<?php

use Syltaen\App\Services\Routes;

// ==================================================
// > CUSTOM QUERY VARS
// ==================================================
// Routes::registerVar('token');


// ==================================================
// > ARCHIVE PAGINATION
// ==================================================
Routes::register(
    'news/([0-9]*)/?$',
    'index.php?pagename=news&page=$matches[1]'
);