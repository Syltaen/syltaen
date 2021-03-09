<?php

namespace Syltaen;

class Cache
{
    /**
     * The cache folder/file identifier
     *
     * @var string
     */
    private $key = "";

    /**
     * The directory in which to store all cache files
     *
     * @var string
     */
    private $directory = "";

    /**
     * List of files found in the directory
     *
     * @var array
     */
    private $files     = [];

    /**
     * Format to use for each cache file.
     * Used as a file extension and a filter for each data
     *
     * @var string
     */
    private $format    = "json";

    /**
     * Time To Live in minutes.
     * Define how much time a file can be used before it is expired.
     *
     * @var integer
     */
    private $ttl       = 60;

    /**
     * Number of cache file to keep in the folder.
     * Used to have an history and backups of each cache files.
     *
     * @var integer
     */
    private $keep      = 10;

    /**
     * Timestamp when the instance was created.
     * Used to check for expiration and to create a new cache file.
     *
     * @var integer
     */
    private $now       = 0;

    // ==================================================
    // > PUBLIC
    // ==================================================

    /**
     * Create a cache instance
     *
     * @param string $key Folder in with the cache files are stored
     * @param string $format File format to use
     */
    public function __construct($key = "logs", $ttl = 60, $format = "json", $keep = 10)
    {
        $this->key       = $key;
        $this->ttl       = round($ttl * 60);
        $this->format    = $key == "logs" ? "log" : $format;
        $this->keep      = $keep;

        $this->now       = Time::current();
        $this->directory = static::getDirectory($this->key);
        $this->files     = static::getAllFiles($this->directory, $this->format);
    }



    /**
     * Get data from the last cache file, or create a new one if its expired
     *
     * @param callable $resultCallback Function to execute to get the result
     * @param int $ttl Expiration time of the cache, in minutes
     * @param int $keep Number of cache elements to keep
     * @return void
     */
    public function get($resultCallback = false)
    {
        $last = $this->getDataFrom(0);

        if ($resultCallback && $this->isExpired()) {
            return static::storeNew($resultCallback($last));
        } else {
            return $last;
        }
    }

    /**
     * Log a new line into a log text file
     *
     * @param string $newLine The line to add
     * @param string $filename The logfile name
     * @param int $linesToKeep Limit of lines to keep in the file
     * @return void
     */
    public function log($newLine, $filename = "logs", $linesToKeep = 500)
    {
        $last = $this->getDataFrom($filename);

        // Get the files
        $filename = $this->directory . "/" . $filename . ".log";
        $file     = fopen($filename, "w");

        // The new content
        $content = $last ? $last . "\n" : "";
        $content .= "[". date("d/m/Y H:i:s", $this->now) . "] ". $newLine;
        $content = implode("\n", array_slice(explode("\n", $content), -$linesToKeep, $linesToKeep));

        // Rewrite the file
        fwrite($file, $content);
        chmod($filename, 0777);
        fclose($file);
    }

    /**
     * Check if the last cache file is expired
     *
     * @return boolean
     */
    public function isExpired()
    {
        $lastTime = empty($this->files) ? 0 : intval($this->files[0]);
        return $this->now - $this->ttl > $lastTime;
    }

    /**
     * Clear all files found in the cache folder
     *
     * @return void
     */
    public function clear()
    {
        $this->checkGarbage(0);
    }


    // ==================================================
    // > STATIC TOOLS
    // ==================================================
    /**
     * Get the timestamp of the last cached file
     *
     * @param string $key
     * @return void
     */
    public static function getTime($key)
    {
        $files = static::getAllFiles(static::getDirectory($key));
        return empty($files) ? 0 : intval($files[0]);
    }

    /**
     * Get the timestamp of the last cached file
     *
     * @param string $key
     * @return void
     */
    public static function getDirectory($key)
    {
        return Files::path("app/cache/{$key}");
    }


    // ==================================================
    // > PRIVATE
    // ==================================================
    /**
     * Get a list of all cached files
     *
     * @return void
     */
    private static function getAllFiles($directory, $format = "json")
    {
        $files = [];
        if (is_dir($directory)) {
            foreach (scandir($directory, SCANDIR_SORT_DESCENDING) as $file) {
                if (strpos($file, ".".$format)) {
                    $files[] = $file;
                }
            }

        // Or create the cache directory if there is none
        } else {
            mkdir($directory);
        }

        return $files;
    }

    /**
     * Create a new cache file
     *
     * @param [type] $content
     * @param integer $keep
     * @return void
     */
    public function storeNew($content)
    {
        // Get the file to write in
        $filename = $this->now . "." . $this->format;
        $filepath = $this->directory . "/" . $filename;
        $file     = fopen($filepath, "w");

        // Encode the content
        switch ($this->format) {
            case "json":
            case "json:array":
                $txt = json_encode($content);
                break;
            case "txt":
            default:
                $txt = $content;
            break;
        }

        // Store the content in the file
        fwrite($file, $txt);
        chmod($filepath, 0777);
        fclose($file);

        // Add file to the list and delete files that are too old
        array_unshift($this->files, $filename);
        $this->checkGarbage($this->keep);

        return $content;
    }

    /**
     * Remove files that are too old.
     * Only keep $this->keep number of files
     *
     * @return void
     */
    private function checkGarbage($keep)
    {
        while (count($this->files) > $keep) {
            $filename = array_pop($this->files);
            unlink($this->directory . "/" . $filename);
        }
    }


    /**
     * Get the data from a file by its index
     *
     * @param mixed $fileSearch Name or index of the file
     * @return mix
     */
    private function getDataFrom($fileSearch = 0)
    {
        if (empty($this->files)) return false;

        // Get the correct file
        $file = false;

        if (is_int($fileSearch)) { // by id
            $file = $this->directory . "/" . $this->files[$fileSearch];

        } elseif (is_string($fileSearch)) { // by name
            foreach ($this->files as $fileIndex=>$fileName) {
                if ($fileName == $fileSearch . "." . $this->format) {
                    $file = $this->directory . "/" . $this->files[$fileIndex];
                    break;
                }
            }
        }

        if (!file_exists($file)) return false;

        $content = file_get_contents($file);

        switch ($this->format) {
            case "json":
                return json_decode($content);
            case "json:array":
                return json_decode($content, true);
            case "txt":
            default:
                return $content;
        }
    }
}