<?php
/**
 * PostStatusExtender
 *
 * @author Hyyan Abo Fakher<hyyanaf@gmail.com>
 */
class PostStatusExtender
{

    /**
     * Extend
     *
     * Extend the current status list for the given post type
     *
     * @global \WP_POST $post
     *
     * @param string $postType the post type name , ex: product
     * @param array $states array of states where key is the id(state id) and value
     *                      is the state array
     */
    public static function extend($postType, $states)
    {

        foreach ($states as $id => $state) {
            register_post_status($id, $state);
        }

        add_action('admin_footer-post.php', function() use($postType, $states) {

            global $post;
            if (!$post || $post->post_type !== $postType) {
                return false;
            }

            foreach ($states as $id => $state) {

                printf(
                        '<script>'
                        . 'jQuery(document).ready(function($){'
                        . '   $("select#post_status").append("<option value=\"%s\" %s>%s</option>");'
                        . '   $("a.save-post-status").on("click",function(e){'
                        . '      e.preventDefault();'
                        . '      var value = $("select#post_status").val();'
                        . '      $("select#post_status").value = value;'
                        . '      $("select#post_status option").removeAttr("selected", true);'
                        . '      $("select#post_status option[value=\'"+value+"\']").attr("selected", true)'
                        . '    });'
                        . '});'
                        . '</script>'
                        , $id
                        , $post->post_status !== $id ? '' : 'selected=\"selected\"'
                        , $state['label']
                );

                if ($post->post_status === $id) {
                    printf(
                            '<script>'
                            . 'jQuery(document).ready(function($){'
                            . '   $(".misc-pub-section #post-status-display").text("%s");'
                            . '});'
                            . '</script>'
                            , $state['label']
                    );
                }
            }
        });


        add_action('admin_footer-edit.php', function() use($states, $postType) {

            global $post;

            if (!$post || $post->post_type !== $postType) {
                return false;
            }

            foreach ($states as $id => $state) {
                printf(
                        '<script>'
                        . 'jQuery(document).ready(function($){'
                        . " $('select[name=\"_status\"]' ).append( '<option value=\"%s\">%s</option>' );"
                        . '});'
                        . '</script>'
                        , $id
                        , $state['label']
                );
            }
        });

        // add_filter('display_post_states', function($states, $post) use($states, $postType) {

        // 	foreach ($states as $id => $state) {
        // 		if ($post->post_type == $postType && $post->post_status === $id) {
        // 			return array($state['label']);
        // 		} else {
        // 			if (array_key_exists($id, $states)) {
        // 				unset($states[$id]);
        // 			}
        // 		}
        // 	}

        // 	return $states;
        // }, 10, 2);

        /* PREVENT POST FROM GETTING DEFAULT-PUBLISHED */
        add_filter('wp_insert_post_data' , function ($data , $postarr) use ($states, $postType) {
            if ($data['post_type'] == $postType) {
                $data['post_status'] = $data['post_status'] == "publish" ? $postarr["original_post_status"] : $data['post_status'];
            }
            return $data;
        } , '99', 2);

    }

}


// ==================================================
// > EXTENDS
// ==================================================
add_action('init', function() {
    PostStatusExtender::extend('gnp_team', [
        'closed_team' => [
            'label' => "Équipe fermée",
            'public' => true,
            'exclude_from_search' => true,
            'show_in_admin_all_list' => true,
            'show_in_admin_status_list' => true,
            'label_count' => _n_noop('Équipes fermées <span class="count">(%s)</span>', 'Équipes fermées <span class="count">(%s)</span>'),
        ]
    ]);
});