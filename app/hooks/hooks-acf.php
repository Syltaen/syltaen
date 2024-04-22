<?php

namespace Syltaen;

/**
 * Enable performance mode with ultra engine (default)
 * Config:
 *     'engine'     => 'ultra',      // ultra (default) | hybrid
 *     'mode'       => 'production', // test | production (default) | rollback
 *     'ui'         => false,        // display metabox (dev mode should be enabled)
 *     'post_types' => array(),      // allowed post types (all)
 *     'taxonomies' => array(),      // allowed taxonomies (all)
 *     'users'      => false,        // allowed user roles (none)
 *     'options'    => false,        // allowed option id  (none)
 */
add_action("acfe/init", function () {
    acfe_update_setting("modules/performance", [
        "engine"     => "ultra",
        "ui"         => true,
        "mode"       => "production",
        "post_types" => ["page", "included_sections"],
    ]);
});
