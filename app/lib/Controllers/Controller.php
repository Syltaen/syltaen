<?php

namespace Syltaen;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class Controller
{
    /**
     * Store all the data needed for the rendering
     *
     * @var Set
     */
    public $data;

    /**
     * Default view used by the controller
     *
     * @var string
     */
    public $view;

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
        $this->data = new Set;
        $this->args = $args;
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

    /**
     * Return rendered HTML by passing a view filename
     *
     * @param  string   $filename
     * @param  array    $data
     * @return string
     */
    public function view($filename = false, $data = false)
    {
        return View::render(
            "pages/" . ($filename ?: $this->view),
            $data ?: $this->data,
            true
        );
    }

    /**
     * Display a view
     *
     * @param  string $filename
     * @param  array  $data
     * @return void
     */
    public function render($filename = false, $data = false)
    {
        View::display(
            "pages/" . ($filename ?: $this->view),
            $data ?: $this->data,
            true
        );
        exit;
    }

    /**
     * Log data into the console
     *
     * @param  $data
     * @param  string  $tag
     * @return void
     */
    public static function log($data, $tag = null)
    {
        Log::console($data, $tag);
    }

    /**
     * Return data in JSON format
     *
     * @return string
     */
    public function json($data = false)
    {
        Log::json($data ?: $this->data);
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
     * @param  array  $table Associative array of data with header as keys
     * @return void
     */
    public static function excel($table, $filename = false, $fillColor = false)
    {
        error_reporting(null);
        ini_set("display_errors", 0);

        $writer   = new \XLSXWriter ();
        $filename = ($filename ?: ($table instanceof Model ? $table::TYPE : "export")) . "_" . date("Y-m-d_H-i-s");
        $table    = $table instanceof Model ? $table->getAsTable() : (array) $table;

        $writer->setAuthor(config("project"));
        $writer->setCompany(config("client"));

        // Add sytled header
        $writer->writeSheetRow("Export", array_keys($table[0]), [
            "font-style" => "bold",
            "fill"       => $fillColor ?: config("color.primary"),
            "color"      => "#fff",
            "font-size"  => 10,
            "border"     => "bottom",
            "halign"     => "left",
            "valign"     => "center",
            "height"     => 20,
        ]);

        // Add each rows
        foreach ($table as $row) {
            $writer->writeSheetRow("Export", $row, [
                "height"    => 15,
                "font-size" => 10,
                "halign"    => "left",
                "valign"    => "center",
            ]);
        }

        // Send file
        header("Content-Type: application/xlsx");
        header("Content-Disposition: attachment; filename={$filename}.xlsx;");
        $f = fopen("php://output", "w");
        fwrite($f, $writer->writeToString());
        exit;
    }

    /**
     * Make and send a CSV file form an array of data
     *
     * @param  array  $table
     * @return void
     */
    public static function csv($table, $filename = "export.csv", $delimiter = ";")
    {
        header("Content-Type: application/csv");
        header("Content-Disposition: attachment; filename={$filename};");

        $f = fopen("php://output", "w");
        foreach ($table as $row) {
            fputcsv($f, (array) $row, $delimiter);
        }
        exit;
    }

    /**
     * Force the download of a media
     *
     * @param  $id    The media ID
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
    /**
     * @param $message
     * @param $redirection
     * @param false          $replace_content
     * @param false          $message_key
     */
    public function message($message, $redirection = false, $replace_content = false, $message_key = "message")
    {
        $message_data = [
            $message_key    => $message,
            "empty_content" => $replace_content,
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
    public function error($message, $redirection = false, $replace_content = false)
    {
        $this->message($message, $redirection, $replace_content, "error_message");
    }

    /**
     * Shortcut to Send a success message
     */
    public function success($message, $redirection = false, $replace_content = false)
    {
        $this->message($message, $redirection, $replace_content, "success_message");
    }
}
