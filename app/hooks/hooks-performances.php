<?php

namespace Syltaen;

// =============================================================================
// > NINJA FORMS
// =============================================================================
/**
 * Remove NF metabox in admin
 */
add_action("add_meta_boxes", function () {
    $screen = get_current_screen();
    if (!$screen) {
        return;
    }

    remove_meta_box("nf_admin_metaboxes_appendaform", $screen->id, "side");
});
remove_all_filters("media_buttons_context");

// =============================================================================
// > PERMALINKS
// =============================================================================
/**
 * Remove useless endpoints in rewrite rules
 */
// add_filter("rewrite_rules_array", function ($rules) {
//     $search = ["attachment", "comments", "search", "author", "embed", "feed"];

//     foreach ($rules as $regex => $query) {
//         foreach ($search as $slug) {
//             if (strpos($regex, "$slug/") !== false) {
//                 unset($rules[$regex]);
//             }
//         }
//     }

//     return $rules;
// }, 5);

// =============================================================================
// > EVERYTHING ELSE
// =============================================================================
new Performances();