<?php

namespace Syltaen;

abstract class BaseController extends Controller
{

    public $view = "page";

    /**
     * The current user
     *
     * @var Syltaen\Users
     */
    public $user;

    /**
     * The current post
     *
     * @var WP_Posts
     */
    public $post;

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
        $this->user = (new Users)->logged();

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
            "main" => View::menu(
                "main_menu",
                "site-header__menu"
            ),
            "mobile" => View::menu(
                "main_menu",
                "site-mobilenav__menu"
            ),
            "footer" => View::menu(
                "footer_menu",
                "site-footer__menu"
            )
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
    public function breadcrumb($filter = false)
    {
        if ($filter) add_filter("breadcrumb_trail_items", $filter, 10, 1);
        return breadcrumb_trail([
            "show_browse" => false,
            "echo"        => false
        ]);
    }

    /**
     * Re-generate a new breadcrumb with custom filters
     *
     * @param callable $filter
     * @return void
     */
    public function updateBreadcrumb($filter)
    {
        $this->data["site"]["breadcrumb"] = $this->breadcrumb($filter);
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
        if ($this->user->found()) {
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
    // > SECURITY
    // ==================================================
    /**
     * Check if the user is logged, if not redirect to a page with an error
     *
     * @param boolean $error
     * @return void
     */
    public function requireLogged($error = false, $page = "/connexion")
    {
        global $post;

        $error = $error ? $error : "Veuillez vous connecter pour accéder à cette page.<br>Une problème ? <a href=".site_url("contact").">Contactez un administrateur.</a>";

        if (!Users::isLogged()) {
            (new Controller)->error($error, $page . "?ref=" . $post->ID . "&" . $_SERVER["QUERY_STRING"]);
        }
    }

    /**
     * Check if the user is logged, if not redirect to a page with an error
     *
     * @param boolean $error
     * @return void
     */
    public function requireRoles($role = false, $error = false, $page = "/connexion")
    {
        global $post;

        $roles = (array) $role;
        $error = $error ? $error : "Vous n'avez pas le droit d'accéder à cette page.<br>Une problème ? <a href=".site_url("contact").">Contactez un administrateur.</a>";

        if (!$this->user->can($roles, "any")) {
            (new Controller)->error($error, $page . "?ref=" . $post->ID . "&" . $_SERVER["QUERY_STRING"]);
        }
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
     * Change the document title (require YOAST SEO)
     *
     * @param string $title
     * @return void
     */
    protected function setPageTitle($title)
    {
        add_filter("wpseo_title", function () use ($title) {
            return $title;
        });
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
            Route::respond($responce, $args, true);
        }
    }

}