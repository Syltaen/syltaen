<?php

namespace Syltaen;

class Controller
{

    /**
     * Store all the data needed for the rendering
     *
     * @var array
     */
    public $data = [];


    /**
     * Default view used by the controller
     *
     * @var string
     */
    public $view = false;


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
     * Add data to the context
     *
     * @return array of data
     */
    public function addData($array)
    {
        Data::store($this->data, $array);
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
     * Make and send an excel file form an array of data
     *
     * @param array $table
     * @return void
     */
    public static function excel($table, $filename = "export")
    {
        header("Content-Type: application/xlsx");
        header("Content-Disposition: attachment; filename={$filename}.xlsx;");

        $writer = new \XLSXWriter();

        $writer->setAuthor(App::config("project"));
        $writer->setCompany(App::config("client"));

        // Add sytled header
        if (!empty($table["header"])) {
            $writer->writeSheetRow("Export", $table["header"], [
                "font-style" => "bold",
                "fill"       => App::config("color_primary"),
                "color"      => "#fff",
                "font-size"  => 9,
                "border"     => "bottom",
                "halign"     => "left",
                "valign"     => "center",
                "height"     => 20
            ]);
        }

        // Add each rows
        foreach ($table["rows"] as $row) $writer->writeSheetRow("Export", $row, [
            "height"     => 15,
            "font-size"  => 8,
            "halign"     => "left",
            "valign"     => "center",
        ]);


        // Send file
        $f = fopen("php://output", "w");
        fwrite($f, $writer->writeToString());
        exit;
    }



    /**
     * Make and send a CSV file form an array of data
     *
     * @param array $table
     * @return void
     */
    public static function csv($table, $filename = "export.csv", $delimiter = ";")
    {
        header("Content-Type: application/csv");
        header("Content-Disposition: attachment; filename='{$filename}';");

        $f = fopen("php://output", "w");
        foreach ($table as $row) {
            fputcsv($f, (array) $row, $delimiter);
        }
        exit;
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