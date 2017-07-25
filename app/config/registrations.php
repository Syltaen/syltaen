<?php

namespace Syltaen;

add_action("init", function() {

    // ==================================================
    // > POST TYPES
    // ==================================================
    News::register();

    // ==================================================
    // > TAXONOMIES
    // ==================================================
    NewsTaxonomy::register();
    NewsTaxonomy::useFor([
        News::class
    ]);

    // ==================================================
    // > CUSTOM STATUS
    // ==================================================
    // News::addStatusTypes([
    //     "old_news"  => ["News dépassée", "News dépassées"],
    // ]);

});