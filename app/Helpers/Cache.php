<?php

namespace Syltaen;

class Cache
{
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
    public function __construct($key, $ttl = 60, $format = "json", $keep = 10)
    {
        $this->directory = Files::path("cache") . $key;
        $this->format    = $format;
        $this->files     = $this->getAllFiles();
        $this->ttl       = round($ttl * 60);
        $this->keep      = $keep;
        $this->now       = time();
    }

    /**
     * Get data from the last cache file, or create a new one if its expired
     *
     * @param callable $resultCallback Function to execute to get the result
     * @param int $ttl Expiration time of the cache, in minutes
     * @param int $keep Number of cache elements to keep
     * @return void
     */
    public function get($resultCallback)
    {
        $last = $this->getDataFrom(0);

        if ($this->isExpired()) {
            return static::storeNew($resultCallback($last));
        } else {
            return $last;
        }
    }

    // ==================================================
    // > PRIVATE
    // ==================================================
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
     * Get a list of all cached files
     *
     * @return void
     */
    private function getAllFiles()
    {
        $files = [];
        if (is_dir($this->directory)) {
            foreach (scandir($this->directory, SCANDIR_SORT_DESCENDING) as $file) {
                if (strpos($file, ".".$this->format)) {
                    $files[] = $file;
                }
            }

        // Or create the cache directory if there is none
        } else {
            mkdir($this->directory);
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
    private function storeNew($content)
    {
        // Get the file to write in
        $filename = $this->now . "." . $this->format;
        $file     = fopen($this->directory . "/" . $filename, "w");

        // Encode the content
        switch ($this->format) {
            case "json":
                $txt = json_encode($content);
                break;
            case "txt":
            default:
                $txt = $content;
            break;
        }

        // Store the content in the file
        fwrite($file, $txt);
        fclose($file);

        // Add file to the list and delete files that are too old
        array_unshift($this->files, $filename);
        static::checkGarbage();

        return $content;
    }

    /**
     * Remove files that are too old.
     * Only keep $this->keep number of files
     *
     * @return void
     */
    private function checkGarbage()
    {
        while (count($this->files) > $this->keep) {
            $filename = array_pop($this->files);
            unlink($this->directory . "/" . $filename);
        }
    }

    /**
     * Get the data from a file by its index
     *
     * @param integer $fileIndex
     * @return mix
     */
    private function getDataFrom($fileIndex = 0)
    {
        if (empty($this->files)) return false;

        $file = $this->directory . "/" . $this->files[$fileIndex];
        if (!file_exists($file)) return false;

        $content = file_get_contents($file);

        switch ($this->format) {
            case "json":
                return json_decode($content, true);
            case "txt":
            default:
                return $content;
        }
    }

}