<?php

function get_sharelink_facebook($post) {
	$post_url 	= get_the_permalink($post->ID);
	$post_image = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
	return "https://www.facebook.com/dialog/feed?app_id=184683071273&link=$post_url&picture=$post_image&name=".$post->post_title."&caption=".$post->post_excerpt."&description=%20&redirect_uri=http%3A%2F%2Fwww.facebook.com%2F";
}


function get_sharelink_twitter($post) {
	$post_url 	= get_the_permalink($post->ID);
	return "http://twitter.com/share?url=$post_url&via=LivingLabsinWal";
}


function get_sharelink_mail($post) {
	return "mailto:destinatair@mail.com?subject=".$post->post_title."&body=".get_the_permalink($post->ID);
}