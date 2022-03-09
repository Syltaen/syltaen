<?php

namespace Syltaen;

abstract class DataProcessor
{
    /**
     * A reference to the controller using the processor
     *
     * @var Controller
     */
    protected $controller;

    /**
     * Store the local data that needs processing
     *
     * @var Set
     */
    public $data;

    /**
     * Initialization
     *
     * @param boolean $controller
     */
    public function __construct($data, &$controller = false)
    {
        $this->controller = $controller;
        $this->data       = set($data);
    }

    /**
     * Add data to the context
     *
     * @return array of data
     */
    public function addData($array, $post_id = null)
    {
        return $this->data->store((array) $array, $post_id);
    }

    /**
     * Get the context
     *
     * @param  string  $key
     * @return mixed
     */
    public function getData($key = false)
    {
        if ($key) {
            return $this->data[$key];
        }

        return $this->data;
    }
}