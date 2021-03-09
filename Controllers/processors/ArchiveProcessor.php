<?php

namespace Syltaen;

class ArchiveProcessor extends DataProcessor
{
    /**
     * Archives for News
     *
     * @param array $c Local context
     * @return void
     */
    public function news(&$c)
    {
        $this->paginate($c, new News, 9);
    }



    // =============================================================================
    // > TOOLS
    // =============================================================================
    /**
     * Create a pagination from a model
     *
     * @param array $c Local context
     * @param \Syltaen\Model $model
     * @param int $perpage
     * @return void
     */
    private function paginate(&$c, $model, $perpage = 6)
    {
        $pagination   = (new Pagination($model, $perpage));
        $c["walker"]  = $pagination->walker();
        $c["posts"]   = $pagination->posts();
    }


    // =============================================================================
    // > METHOD ROUTING
    // =============================================================================
    /**
     * Process a single archive
     *
     * @param array $content
     * @return void
     */
    public function process($archive)
    {
        // Run the correct mehtod by looking at the archive content type
        switch ($archive["type"]) {

            // Add custom layout-method routes here
            // Ex:
            // case "type-of-archive":
            //     static::nameOfTheMethod($archive);
            //     break;


            // By default : use the type as a method name
            default:
                $method = $archive["type"];
                if (method_exists(static::class, $method)) {
                    $this->{$method}($archive);
                } else {
                    throw new \Exception(__CLASS__ . " does not implement {$method}(). Please add it to process this archive type.", 1);
                }
        }

        return $archive;
    }

}