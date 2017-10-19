<?php

namespace Syltaen;

class Controller {

    /**
     * Store all the data needed for the rendering
     *
     * @var array
     */
    protected $data = [];

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
     * List of custom arguments set in the constructor
     *
     * @var array
     */
    protected $args = [];

    /**
     * Dependencies creation
     *
     * @param array $args List of arguments given by the router
     */
    public function __construct($args = [])
    {
        $this->renderer = new \Pug\Pug([
            "extension" => ".pug",
            "cache"     => include Files::path("cache-pug", "index.php"),
            // "prettyprint" => true,
            // "expressionLanguage" => "js"
        ]);

        $this->viewfolder =  get_template_directory() . "/views/";

        $this->args = $args;
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
        $data     = Data::recursiveFilter($data, "content");

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
    public static function log($data, $tags = null, $levelLimit = 5, $itemsCountLimit = 100, $itemSizeLimit = 50000, $dumpSizeLimit = 500000)
    {
        $dumper    = new \PhpConsole\Dumper($levelLimit, $itemsCountLimit, $itemSizeLimit, $dumpSizeLimit);
        $connector = \PhpConsole\Connector::getInstance();
        $connector->setDebugDispatcher(new \PhpConsole\Dispatcher\Debug($connector, $dumper));
        $connector->getDebugDispatcher()->dispatchDebug($data, $tags, 1);
    }

    /**
     * Log the controller data into the console
     *
     * @param string $key
     * @param string $tags
     * @return void
     */
    public function dlog($key = false, $tags = null, $levelLimit = null, $itemsCountLimit = null, $itemSizeLimit = null, $dumpSizeLimit = null)
    {
        if ($key) {
            self::log($this->data[$key], $tags, $levelLimit, $itemsCountLimit, $itemSizeLimit, $dumpSizeLimit);
        } else {
            self::log($this->data, $tags, $levelLimit, $itemsCountLimit, $itemSizeLimit, $dumpSizeLimit);
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
        header("Content-type: text/xml; charset=utf-8");
        return $this->data;
    }

    /**
     * Return data in a PHP format
     *
     * @return string
     */
    public function php()
    {
        echo "<pre>";
        if (is_array($this->data) || is_object($this->data)) {
            print_r($this->data, true);
        } else {
            print($this->data);
        }
        echo "</pre>";
    }

    /**
     * Return a downloadable CSV
     *
     * @param string $filename
     * @param string $delimiter
     * @param mixed $data
     * @return void
     */
    public function csv($filename = "export.csv", $delimiter = ";", $data = false)
    {
        header("Content-Type: application/csv");
        header("Content-Disposition: attachment; filename='{$filename}';");

        $data = $data ?: $this->data;

        $f = fopen("php://output", "w");
        foreach ($data as $line) {
            fputcsv($f, (array) $line, $delimiter);
        }
        exit;
    }

    /**
     * Return a downloadable .xlxs
     *
     * @param string $filename
     * @param mixed $data
     * @uses composer require "mk-j/php_xlsxwriter"
     * @return void
     */
    public function excel($filename = "export.xlsx", $data)
    {
        header("Content-Type: application/xlsx");
        header("Content-Disposition: attachment; filename={$filename};");

        $data = $data ?: $this->data;

        $writer = new \XLSXWriter();
        $writer->writeSheet($data);

        $f = fopen("php://output", "w");
        fwrite($f, $writer->writeToString());
        exit;
    }

    // ==================================================
    // > MESSAGES : Errors, success, warnings...
    // ==================================================
    public function message($message, $replace_content = false, $redirection = false, $message_key = "message")
    {
        $error_data = [
            $message_key    => $message,
            "empty_content" => $replace_content
        ];

        if (!$redirection) {
            Data::currentPage($error_data);
        } else {
            Data::nextPage($error_data, $redirection);
        }

    }

    /**
     * Shortcut to send an error message
     */
    public function error($message, $replace_content = false, $redirection = false)
    {
        $this->message($message, $replace_content, $redirection, "error_message");
    }

    /**
     * Shortcut to Send a success message
     */
    public function success($message, $replace_content = false, $redirection = false)
    {
        $this->message($message, $replace_content, $redirection, "success_message");
    }
}