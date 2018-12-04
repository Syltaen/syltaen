<?php

namespace Syltaen;

abstract class DataProcessor
{
    /**
     * A reference to the controller using the processor
     *
     * @var boolean
     */
    protected $controller = false;

    /**
     * Initialization
     *
     * @param boolean $controller
     */
    public function __construct(&$controller = false) {
        $this->controller = $controller;
    }


    /**
     * Process a set of data and return the result
     *
     * @param mixed $data
     * @return mixed
     */
    public function process($raw)
    {
        throw new \Exception("This " . __CLASS__ . " does not implement process()", 1);
        return false;
    }


    /**
     * Process each item of an array and return the result array
     *
     * @param mixed $item
     * @return mixed
     */
    public function processEach($raw)
    {
        $proccessed = [];

        if (empty($raw)) return [];

        foreach ($raw as $rawItem) {
            $item = $this->process($rawItem);

            // Only include the result if it is valid
            if ($item) {
                $proccessed[] = $item;
            }
        }

        return $proccessed;
    }
}