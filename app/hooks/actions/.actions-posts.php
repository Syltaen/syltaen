<?php

namespace Syltaen;

// ==================================================
// > POST DELETION
// ==================================================
add_action("before_delete_post", function ($post_id) {

}, 10, 1);


// ==================================================
// > POST CREATION
// ==================================================
add_action("wp_insert_post", function ($post_id, $post, $update) {

}, 10, 3);