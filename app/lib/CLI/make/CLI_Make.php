<?php

namespace Syltaen;
use \WP_CLI as WP_CLI;
use Jawira\CaseConverter\Convert;

class CLI_Make
{
    /**
     * Create a new post model
     *
     * @param string $name Name of the class
     * @return void
     */
    public static function post($name)
    {
        static::make(
            "PostsModelTemplate",
            (new Convert($name))->toPascal(),
            "Models/Posts",
            [
                "postsmodeltemplate" => strtolower($name)
            ]
        );
    }


    /**
     * Create a new post taxonomy
     *
     * @param string $name Name of the class
     * @return void
     */
    public static function tax($name)
    {
        static::make(
            "TaxonomyModelTemplate",
            (new Convert($name))->toPascal() . "Taxonomy",
            "Models/Taxonomies",
            [
                "taxonomy" => strtolower($name)
            ]
        );
    }

    /**
     * Create a new controller
     *
     * @param string $name Name of the class
     * @return void
     */
    public static function controller($name)
    {
        static::make(
            "ControllerTemplate",
            (new Convert($name . "Controller"))->toPascal(),
            "Controllers",
            [
                "templateview" => (new Convert($name))->toKebab()
            ]
        );
    }


    /**
     * Create a new processor
     *
     * @param string $name Name of the class
     * @return void
     */
    public static function processor($name)
    {
        static::make(
            "ProcessorTemplate",
            (new Convert($name . "Processor"))->toPascal(),
            "Controllers/Processors"
        );
    }


    /**
     * Create a new helper
     *
     * @param string $name Name of the class
     * @return void
     */
    public static function helper($name)
    {
        static::make(
            "HelperTemplate",
            (new Convert($name))->toPascal(),
            "app/Helpers"
        );
    }


    /**
     * Create a new actions list
     *
     * @param string $name
     * @return void
     */
    public static function actions($name)
    {
        static::make(
            "hooks-template",
            "actions-$name",
            "app/hooks/actions"
        );
    }


    /**
     * Create a new filters list
     *
     * @param string $name
     * @return void
     */
    public static function filters($name)
    {
        static::make(
            "hooks-template",
            "filters-$name",
            "app/hooks/filters",
            [
                "add_action" => "add_filter"
            ]
        );
    }


    /**
     * Create a new filters list
     *
     * @param string $name
     * @return void
     */
    public static function ajax($name)
    {
        static::make(
            "hooks-template",
            "ajax-$name",
            "app/hooks/ajax",
            [
                "add_action" => "Hooks::ajax",
                ", 10"       => ""
            ]
        );
    }


    /**
     * Create a new style module
     *
     * @param string $name
     * @return void
     */
    public static function style($name)
    {
        $dir = dirname($name);
        $name = basename($name);

        static::make(
            "style-template",
            "_" . $name,
            "styles/modules/$dir",
            [
                "name" => $name,
                "DIR" => strtoupper($dir),
                "NAME" => strtoupper($name),
            ],
            ".sass",

            function ($file) use ($dir, $name) {
                $builder = explode("\n", file_get_contents(Files::path("styles/builder.sass")));
                $builder = array_reverse($builder);

                // Search where to register the new module
                foreach ($builder as $i=>$line) {
                    if (strpos($line, "@import \"modules/$dir") !== false) break;
                }
                // Module place was not found
                if ($i >= count($builder)) WP_CLI::error("Could not add the module to the builder.");

                // Insert the new line
                array_splice($builder, $i, 0, ["@import \"modules/$dir/$name\""]);

                // Write the file
                file_put_contents(Files::path("styles/builder.sass"), implode("\n", array_reverse($builder)));

                WP_CLI::success("The module has been added to the builder.");
            }
        );
    }


    /**
     * Create a new style module
     *
     * @param string $name
     * @return void
     */
    public static function script($name)
    {
        static::make(
            "script-template",
            $name,
            "scripts/modules",
            [],
            ".coffee"
        );
    }


    // ==================================================
    // > COMMON
    // ==================================================
    /**
     * Create a new file from a template
     *
     * @param string $template
     * @param string $class
     * @param string $folder
     * @return string The final file path
     */
    private static function make($template, $class, $folder, $replaces = [], $ext = ".php", $more = false)
    {
        // Create dir if it does not exist
        if (!is_dir(Files::path($folder))) {
            mkdir(Files::path($folder));
        }

        $dest = Files::path("$folder/{$class}{$ext}");

        // Copy file
        copy(
            Files::path("app/lib/CLI/make/templates/{$template}{$ext}"),
            $dest
        );

        // Replace strings
        $replaces["ClassName"] = $class;
        file_put_contents($dest, str_replace(
            array_keys($replaces),
            array_values($replaces),
            file_get_contents($dest)
        ));

        WP_CLI::success("File $folder/$class$ext has been created.");

        // Do more once the file was created
        if ($more) $more($dest);

        // Open file in VS Code
        exec("code $dest");
    }
}