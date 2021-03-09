<?php

namespace Syltaen;

// Remove Guttenberg
Hooks::add(["use_block_editor_for_post", "use_block_editor_for_post_type"], "__return_false", 10);

// Remove distraction-free mode
add_filter("wp_editor_expand", "__return_false", 10, 2);

// Increase "remember me" duration
add_filter("auth_cookie_expiration", function ($duration, $user_id, $remember_me) {
    return 30 * DAY_IN_SECONDS;
}, 10, 3);


/**
 * Remove NF metabox in admin
 */
add_action("add_meta_boxes", function () {
    $screen = get_current_screen();
    if (!$screen) return;
    remove_meta_box("nf_admin_metaboxes_appendaform", $screen->id, "side");
});
remove_all_filters("media_buttons_context");