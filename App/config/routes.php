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

Routes::register(
    'jobs/([0-9]*)/?$',
    'index.php?pagename=jobs&page=$matches[1]'
);

Routes::register(
    'press/([0-9]*)/?$',
    'index.php?pagename=press&page=$matches[1]'
);
