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
            'extension' => '.pug',
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
     * Log data into the console by giving a datakey, a value
     *
     * @param $var
     * @param string $tags
     * @return void
     */
    public function log($tolog = false, $tags = null)
    {
        if (is_string($tolog)) {
            $tolog = $this->data[$tolog];
        } elseif (!$tolog) {
            $tolog = $this->data;
        }

        \PhpConsole\Connector::getInstance()->getDebugDispatcher()->dispatchDebug($tolog, $tags, 1);
    }

    /**
     * Return data in JSON format
     *
     * @return void
     */
    public function json()
    {

    }

    /**
     * Return data in XML format
     *
     * @return void
     */
    public function xml()
    {

    }

    /**
     * Return data in a PHP format
     *
     * @return void
     */
    public function php()
    {

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
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'";');
        $data = $data ?: $this->data;
        $f = fopen('php://output', 'w');
        foreach ($data as $line) {
            fputcsv($f, $line, $delimiter);
        }
    }
}