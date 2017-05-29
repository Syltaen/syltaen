<?php

namespace Syltaen\Controllers;

class Controller {

    /**
     * Store all the data needed for the rendering
     *
     * @var array
     */
    protected $data;

    /**
     * Handle the conversion of pug to php
     *
     * @var Pug\Pug
     */
    private $renderer;

    /**
     * Path to the folder containg all views
     *
     * @var string
     */
    private $viewfolder;

    /**
     * Default view used by the controller
     *
     * @var string
     */
    protected $view = false;

    /**
     * Dependencies creation
     *
     * @param boolean $auto
     */
    public function __construct()
    {
        $this->renderer = new \Pug\Pug([
            "extension" => ".pug",
        ]);

        $this->viewfolder =  get_template_directory() . "/views/";
    }

    /**
     * Get all the controller stored data
     *
     * @return void
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Return rendered HTML by passing a view filename
     *
     * @param string $filename
     * @param array $data
     * @return string
     */
    public function view($filename = false, $data = false)
    {
        $filename = $filename ?: $this->view;
        $filename = $this->viewfolder . $filename . ".pug";
        $data     = $data ?: $this->data;

        if (file_exists($filename)) {
            return $this->renderer->render($filename, $data);
        } else {
            die("View file not found : $filename");
        }
    }

    /**
     * Display a view
     *
     * @param string $filename
     * @param array $data
     * @return void
     */
    public function render($filename = false, $data = false)
    {
        echo $this->view($filename, $data);
    }

    /**
     * Log data into the console
     *
     * @param $data
     * @param string $tags
     * @return void
     */
    public static function log($data, $tags = null)
    {
        \PhpConsole\Connector::getInstance()->getDebugDispatcher()->dispatchDebug($data, $tags, 1);
    }

    /**
     * Log the controller data into the console
     *
     * @param string $key
     * @param string $tags
     * @return void
     */
    public function dlog($key = false, $tags = null)
    {
        if ($key) {
            self::log($this->data[$key], $tags);
        } else {
            self::log($this->data, $tags);
        }
    }

    /**
     * Return data in JSON format
     *
     * @return string
     */
    public function json()
    {
        header('Content-Type: application/json');
        return json_encode($this->data);
    }

    /**
     * Return data in XML format
     *
     * @return string
     */
    public function xml()
    {
        header('Content-type: text/xml; charset=utf-8');
        return $data;
    }

    /**
     * Return data in a PHP format
     *
     * @return string
     */
    public function php()
    {
        return "<pre>".print_r($this->data, true)."</pre>";
    }

    /**
     * Return a downloadable CSV
     *
     * @param string $filename
     * @param string $delimiter
     * @return void
     */
    public function csv($filename = "export.csv", $delimiter = ";", $data = false)
    {
        header("Content-Type: application/csv");
        header("Content-Disposition: attachment; filename='".$filename."';");
        $data = $data ?: $this->data;
        $f = fopen("php://output", "w");
        foreach ($data as $line) {
            fputcsv($f, $line, $delimiter);
        }
    }
}