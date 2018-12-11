<?php

namespace Syltaen;

abstract class PageController extends Controller
{

    protected $view = "page";

    /**
     * The current user
     *
     * @var Syltaen\Users
     */
    protected $user;

    /**
     * The current post
     *
     * @var WP_Posts
     */
    protected $post;

    /**
     * Add data for the rendering
     */
    public function __construct($args = [])
    {
        global $post;

        parent::__construct($args);

        // Global post
        $this->post = $post;
        $this->data["post"] = $this->post;

        // Store the current user of internal use
        $this->user = Data::globals("user");
        $this->userData = $this->user ? $this->user->getOne() : false;

        // Add common data needed all pages
        $this->setBase();
    }


    // ==================================================
    // > PARTS
    // ==================================================
    /**
     * Rendering of all the websites menus
     *
     * @return array
     */
    protected function menus()
    {
        return [
            "main" => wp_nav_menu([
                "theme_location" => "main_menu",
                "menu_id"        => false,
                "menu_class"     => "menu site-header__menu",
                "container"      => "ul",
                "echo"           => false
            ]),

            "mobile" => wp_nav_menu([
                "theme_location" => "main_menu",
                "menu_id"        => false,
                "menu_class"     => "menu site-mobilenav__menu",
                "container"      => "ul",
                "echo"           => false
            ]),

            "footer" =>	wp_nav_menu([
                "theme_location" => "footer_menu",
                "menu_id"        => false,
                "menu_class"     => "menu site-footer__menu",
                "container"      => "ul",
                "echo"           => false
            ])
        ];
    }

    /**
     * Data for the website main header
     *
     * @return array
     */
    protected function header()
    {
        Data::store($header, [
            "(img:tag) logo@logo_tag",
            "(img:url) logo@logo_url",
            "social" => []
        ], "headerfooter");

        return $header;
    }

    /**
     * Data for the website main footer
     *
     * @return array
     */
    protected function footer()
    {
        Data::store($footer, [
            "copyright", "@copyright" => function ($footer) {
                return str_replace("%year%", date("Y"), $footer["copyright"]);
            }
        ], "headerfooter");

        return $footer;
    }

    /**
     * Generate a breadcrumb
     *
     * @uses Plugin : Breadcrumb Trail
     * @return string The Rendered breadcrumb
     */
    protected function breadcrumb()
    {
        return breadcrumb_trail([
            "show_browse" => false,
            "echo"        => false
        ]);
    }


    /**
     * Pre-load all the ninja forms so that they can be used with barba.js
     *
     * @return array of forms
     */
    protected function forms()
    {
        return array_map(function ($formModel) {
            return [
                "id" => $formModel->get_id(),
                "html" => "[ninja_forms id={$formModel->get_id()}]"
            ];
        }, Ninja_Forms()->form()->get_forms());
    }

    /**
     * Generated the classes used on the body tag
     *
     * @return array of string
     */
    protected function bodyClasses()
    {
        $classes = get_body_class();

        // Logged as admin
        if ($this->userData) {
            $classes[] = "is-logged";
            if ($this->user->can("administrator")) {
                $classes[] = "is-logged--admin";
            }
        } else {
            $classes[] = "is-unlogged";
        }

        return $classes;
    }


    // ==================================================
    // > MESSAGES HANDLING
    // ==================================================
    /**
     * Update the view method to catch any message set in the controller
     *
     * @param boolean $filename
     * @param boolean $data
     * @return void
     */
    public function view($filename = false, $data = false)
    {
        $this->data["error_message"]   = Data::currentPage("error_message");
        $this->data["success_message"] = Data::currentPage("success_message");

        if (Data::currentPage("empty_content")) {
            $this->data["sections"] = [];
        }

        return parent::view($filename, $data);
    }


    // ==================================================
    // > SETTERS / ADDERS
    // ==================================================
    /**
     * Add common data needed each page
     * Can be launched after modifing the global $post to refresh data
     * @return void
     */
    protected function setBase()
    {
        Data::store($this->data, [
            "@site"       => [
                "menus"        => $this->menus(),
                "header"       => $this->header(),
                "footer"       => $this->footer(),
                // "breadcrumb"   => $this->breadcrumb(),
                "forms"        => $this->forms(),

                "name"         => get_bloginfo("name"),
                "url"          => get_bloginfo("url"),
                "language"     => get_locale(),
                "charset"      => get_bloginfo("charset"),
                "description"  => get_bloginfo("description"),
                "pingback_url" => get_bloginfo("pingback_url"),
                "body_class"   => $this->bodyClasses(),
            ]
        ]);
    }

    /**
     * Change the document title
     *
     * @param string $title
     * @return void
     */
    protected function setPageTitle($title)
    {
        add_filter("document_title_parts", function ($parts) use ($title) {
            $parts["title"] = $title;
            return $parts;
        }, 10, 1);
    }

    /**
     * Add class to the body
     *
     * @param array|string $classes Class(es) to add
     * @return void
     */
    public function addBodyClass($classes)
    {
        $this->data["site"]["body_class"] = array_merge(
            $this->data["site"]["body_class"],
            (array) $classes
        );
    }

    /**
     * Set the current page/post to a model result.
     * Usefull to create aliases and/or displaying a page/post that is not found by default
     * @param mixed $model The model used to get the page/post.
     * @param string $responce Specify an other controller method to handle the post
     * @param array $args
     * @return void
     */
    protected function setPage($model, $refreshBase = false, $responce = false, $args = false)
    {
        global $wp_query;
        global $post;

        $wp_query   = $model->limit(1)->getSingularQuery();
        $post       = $model->getOne();
        $this->post = $post;

        if ($refreshBase) {
            $this->setBase();
        }

        if ($responce) {
            Route::respond($resp, $args, true);
        }
    }

}