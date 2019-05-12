<?php

namespace Syltaen;

class View
{

    /**
     * Singleton renderer
     *
     * @var \Pug\Pug
     */
    private static $renderer = null;


    /**
     * Get the singleton renderer
     *
     * @return void
     */
    private static function getRenderer()
    {
        if (is_null(static::$renderer)) {
            static::$renderer = new \Pug\Pug([
                "extension" => ".pug",
                "cache"     => include Files::path("app/cache/pug-php", "index.php"),
                // "prettyprint" => true,
                // "expressionLanguage" => "js"
            ]);
        }

        return static::$renderer;
    }


    /**
     * Render the provided data
     *
     * @param string $filename
     * @param array $data
     * @param boolean $echo
     * @return string|null
     */
    public static function view($filename, $data = false)
    {
        $filename = get_template_directory() . "/views/" . $filename . ".pug";
        $data     = Data::recursiveFilter($data, "content");

        if (!file_exists($filename)) die("View file not found : $filename");

        return static::getRenderer()->render($filename, $data);
    }


    /**
     * Display a view
     *
     * @return void
     */
    public static function render($filename, $data = false)
    {
        echo static::view($filename, $data);
    }
}