<?php

namespace Syltaen\Controllers;

abstract class Controller {

    protected $data;

    private $renderer;
    private $viewfolder;

    /**
     * Constructor
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
     * Return rendered HTML by passing a view filename
     *
     * @param string $filename
     * @param array $data
     * @return string
     */
    public function view($filename = "404", $data = false)
    {
        $filename = $this->viewfolder . $filename . ".pug";
        $data = $data ?: $this->data;

        if (file_exists($filename)) {
            return $this->renderer->render($filename, $this->data);
        } else {
            die("View file not found : $filename");
        }

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
            self::log($this->data[$key]);
        } else {
            self::log($this->data);
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