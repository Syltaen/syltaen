<?php

namespace Syltaen;

class Controller
{

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
        $this->args = $args;
    }

    /**
     * Get the controller stored data
     *
     * @return void
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Get the controller stored data
     *
     * @return void
     */
    public function addData($data)
    {
        Data::store($this->data, $data);
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
        return View::render(
            $filename ?: $this->view,
            $data ?: $this->data
        );
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
        View::display(
            $filename ?: $this->view,
            $data ?: $this->data
        );
    }

    /**
     * Log data into the console
     *
     * @param $data
     * @param string $tags
     * @return void
     */
    public static function log($data, $tags = null, $levelLimit = 10, $itemsCountLimit = 100, $itemSizeLimit = 50000, $dumpSizeLimit = 500000)
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
    public function dlog($key = false, $tags = null, $levelLimit = 10, $itemsCountLimit = 100, $itemSizeLimit = 50000, $dumpSizeLimit = 500000)
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
    public function json($data = false)
    {
        $data = $data ? $data : $this->data;
        wp_send_json($data);
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
     * Force the download of a media
     *
     * @param $id The media ID
     * @return void
     */
    public function media($id)
    {
        $file   = get_attached_file($id);
        $quoted = sprintf('"%s"', addcslashes(basename($file), '"\\'));
        $size   = filesize($file);

        header("Content-Description: File Transfer");
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=" . $quoted);
        header("Content-Transfer-Encoding: binary");
        header("Connection: Keep-Alive");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");
        header("Content-Length: " . $size);
        exit;
    }

    // ==================================================
    // > MESSAGES : Errors, success, warnings...
    // ==================================================
    public function message($message, $replace_content = false, $redirection = false, $message_key = "message")
    {
        $message_data = [
            $message_key    => $message,
            "empty_content" => $replace_content
        ];

        if (!$redirection) {
            Data::currentPage($message_data);
        } else {
            Data::nextPage($message_data, $redirection);
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