<?php

namespace Syltaen\App\Generators;


class Breadcrumb
{
    /**
     * Config for each breadcrumb
     *
     * @var array
     */
    private $config = [];

    /**
     * Create a config by merging custom arguments with default ones
     *
     * @param array $custom_config
     */
    public function __construct($custom_config = [])
    {
        $this->config = array_merge([
            'container'     => 'nav',
            'before'        => '',
            'after'         => '',
            'show_on_front' => true,
            'network'       => false,
            'show_title'    => true,
            'show_browse'   => false,
            'echo'          => true,

            'post_taxonomy' => [
                // 'post'  => 'post_tag', // 'post' post type and 'post_tag' taxonomy
                // 'book'  => 'genre',    // 'book' post type and 'genre' taxonomy
            ],

            'labels' => [
                'browse'              => esc_html__( 'Browse:',                               'breadcrumb-trail' ),
                'aria_label'          => esc_attr_x( 'Breadcrumbs', 'breadcrumbs aria label', 'breadcrumb-trail' ),
                'home'                => esc_html__( 'Home',                                  'breadcrumb-trail' ),
                'error_404'           => esc_html__( '404 Not Found',                         'breadcrumb-trail' ),
                'archives'            => esc_html__( 'Archives',                              'breadcrumb-trail' ),
                'search'              => esc_html__( 'Search results for &#8220;%s&#8221;',   'breadcrumb-trail' ),
                'paged'               => esc_html__( 'Page %s',                               'breadcrumb-trail' ),
                'archive_minute'      => esc_html__( 'Minute %s',                             'breadcrumb-trail' ),
                'archive_week'        => esc_html__( 'Week %s',                               'breadcrumb-trail' ),
                'archive_minute_hour' => '%s',
                'archive_hour'        => '%s',
                'archive_day'         => '%s',
                'archive_month'       => '%s',
                'archive_year'        => '%s'
            ]
        ], $custom_config);
    }

    /**
     * Get the generated breadcrumb
     *
     * @return HTML
     */
    public function get()
    {
        if (function_exists('breadcrumb_trail')) {
            return breadcrumb_trail($this->config);
        } else {
            die('Error : Breadcrumb Trail not installed');
        }
    }
}