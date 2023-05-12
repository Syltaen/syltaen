<?php

namespace Syltaen;

class AdminPreviewController extends Controller
{
    /**
     * Create a new instance of the controller
     */
    public function __construct()
    {
        parent::__construct();
        static::addScript();
    }

    // =============================================================================
    // > OFFLOAD RENDERING
    // =============================================================================
    /**
     * Save the field in a file and ask an iframe to render it through an ajax URL
     *
     * @return void
     */
    public function offloadRender()
    {
        $key = uniqid();
        Files::write("app/cache/admin-preview/$key", serialize(["post" => get_the_ID(), "section" => get_fields()["sections"][0]]));
        $url = site_url("wp-admin/admin-ajax.php?action=syltaen_admin_preview&key=$key");
        $this->iframe($url);
    }

    /**
     * Render a section in an iframe
     *
     * @return void
     */
    public function ajaxRender()
    {
        $key = $_GET["key"];

        if (!Files::exists("app/cache/admin-preview/$key")) {
            wp_die("Section could not be rendered. Please try to edit it again.");
        }

        // Get data from the file and then delete it
        $data = unserialize(Files::read("app/cache/admin-preview/$key"));
        Files::delete(Files::path("app/cache/admin-preview/$key"));

        // Set the global post
        $GLOBALS["post"] = get_post($data["post"]);
        static::setLang();

        die($this->html($data["section"]));
    }

    // =============================================================================
    // > DIRECT RENDERING
    // =============================================================================
    /**
     * When the request is made through AJAX, we can render the content directly, save it in an html file and display it in an iframe.
     * That avoids making two long requests (ajax + rendering iframe).
     *
     * @return void
     */
    public function directRender()
    {
        // Change the language if needed
        static::setLang();

        // Render in an HTML file and display it in an iframe
        $id = uniqid();
        Files::write("app/cache/admin-preview/{$id}.html", $this->html(get_fields()["sections"][0]));
        $this->iframe(Files::url("app/cache/admin-preview/{$id}.html"));
    }

    // =============================================================================
    // > TOOLS
    // =============================================================================
    /**
     * Render a section
     *
     * @param  array  $section
     * @return string The HTML result
     */
    public function html($section)
    {
        $section = (new SectionProcessor($section, $this, null))->getData();

        return $this->view("admin-preview", [
            "sections" => isset($section["sections"]) ? $section["sections"] : [$section],
        ]);
    }

    /**
     * Display an iframe of the given url
     *
     * @param  string $url
     * @return void
     */
    public function iframe($url)
    {
        echo "<iframe style='display: block; width: 100%; height: 200px;' onload='resizeIframe(this)' src='$url'></iframe>";
    }

    /**
     * Before rendering, set the language to the global post lang
     *
     * @return void
     */
    public static function setLang()
    {
        if (function_exists("pll_is_translated_post_type") && isset($GLOBALS["post"]->post_type) && pll_is_translated_post_type($GLOBALS["post"]->post_type)) {
            Lang::switchTo(Lang::ofPost($GLOBALS["post"]->ID));
        }
    }

    /**
     * Add a JS function that allows iframe to resize themselves
     *
     * @return void
     */
    public static function addScript()
    {
        ?>
            <script>
                function resizeIframe(obj) {
                    obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
                    jQuery(obj).parent().css("height", obj.contentWindow.document.documentElement.scrollHeight + 'px')

                    jQuery(window).resize(function () {
                        obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
                        jQuery(obj).parent().css("height", obj.contentWindow.document.documentElement.scrollHeight + 'px')
                    });
                }
            </script>
        <?php
}
}