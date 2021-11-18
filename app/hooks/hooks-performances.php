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
    if (!$screen) return;
    remove_meta_box("nf_admin_metaboxes_appendaform", $screen->id, "side");
});
remove_all_filters("media_buttons_context");



// =============================================================================
// > EVERYTHING ELSE
// =============================================================================
new Performances();