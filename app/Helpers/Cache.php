<?php

namespace Syltaen;

class Cache
{
    /**
     * The cache folder/file identifier
     *
     * @var string
     */
    public $key = "";

    /**
     * The directory in which to store all cache files
     *
     * @var string
     */
    public $directory = "";

    /**
     * List of files found in the directory
     *
     * @var array
     */
    public $files = [];

    /**
     * Format to use for each cache file.
     * Used as a file extension and a filter for each data
     *
     * @var string
     */
    public $format = "serialized";

    /**
     * Compress/Decompress the stored data
     *
     * @var boolean
     */
    public $compressed = false;

    /**
     * Time To Live in minutes.
     * Define how much time a file can be used before it is expired.
     * A negative number means the cache never expires.
     *
     * @var integer
     */
    public $ttl = 60;

    /**
     * Number of cache file to keep in the folder.
     * Used to have an history and backups of each cache files.
     *
     * @var integer
     */
    public $history = 10;

    /**
     * Timestamp when the instance was created.
     * Used to check for expiration and to create a new cache file.
     *
     * @var integer
     */
    public $now = 0;

    // ==================================================
    // > PUBLIC
    // ==================================================

    /**
     * Create a cache instance
     *
     * @param string $key    Folder in with the cache files are stored
     * @param string $format File format to use
     */
    public function __construct($key, $ttl = 60, $history = 10, $format = "serialized")
    {
        $this->key       = $key;
        $this->ttl       = round($ttl * 60);
        $this->format    = $format;
        $this->history   = $history;
        $this->now       = Time::current();
        $this->directory = static::getDirectory($this->key);
        $this->files     = static::getAllFiles($this->directory, $this->format);
    }

    /**
     * Set the data as compressed
     *
     * @return self
     */
    public function setCompressed($value = true)
    {
        $this->compressed = $value;
        return $this;
    }

    // /**
    //  * Overwrite the default cache directory, to use a directory outside the teme for example
    //  *
    //  * @param string $path
    //  * @return self
    //  */
    // public function setDirectory($path)
    // {
    //     $this->directory = $path;
    //     $this->files     = static::getAllFiles($this->directory, $this->format);
    // }

    // =============================================================================
    // > RETRIEVING DATA
    // =============================================================================

    /**
     * Get data from the last cache file, or create a new one if its expired
     *
     * @param  callable $resultCallback Function to execute to get the result
     * @return mixed
     */
    public function get($resultCallback = false)
    {
        $last = $this->getDataFrom(0);

        if ($resultCallback && $this->isExpired()) {
            return static::store($resultCallback($last));
        } else {
            return $last;
        }
    }

    /**
     * Check if the last cache file is expired
     *
     * @return boolean
     */
    public function isExpired()
    {
        // Negative TTL means never expires
        if ($this->ttl < 0 && !empty($this->files)) {
            return false;
        }

        $lastTime = empty($this->files) ? 0 : intval($this->files[0]);
        return $this->now - $this->ttl > $lastTime;
    }

    /**
     * Clear all files found in the cache folder
     *
     * @param  bool   $hard Remove everything that is in the directory.
     * @return void
     */
    public function clear($hard = false)
    {
        if ($hard) {
            Files::delete($this->directory);
        }

        $this->checkGarbage(0);
    }

    // ==================================================
    // > STATIC TOOLS
    // ==================================================
    /**
     * Get the timestamp of the last cached file
     *
     * @param  string $key
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
     * @param  string $key
     * @return void
     */
    public static function getDirectory($key)
    {
        return Files::path("app/cache/{$key}");
    }

    /**
     * Cache a specific value to avoid multiple processing during a same page load
     *
     * @return void
     */
    public static function value($key, $value_or_callback = null)
    {
        $cached_values = Data::globals("cached_values") ?: set();

        // Getting a value
        if (is_null($value_or_callback)) {
            return $cached_values[$key] ?? null;
        }

        if ($cached_values->hasKey($key)) {
            return $cached_values[$key];
        }

        // Saving a new value
        $cached_values[$key] = is_callable($value_or_callback) ? $value_or_callback() : $value_or_callback;
        Data::globals(["cached_values" => $cached_values]);

        return $cached_values[$key];
    }

    // ==================================================
    // > ENCODE / DECODE
    // ==================================================
    /**
     * Encode the content so that it can be stored in a file, , using the specified format
     *
     * @param  mixed    $content
     * @return string
     */
    public function encodeContent($content)
    {
        switch ($this->format) {
            case "json":
            case "json:array":
                $content = json_encode($content);
                break;
            case "txt":
                break;
            default:
                return serialize($content);
        }

        if ($this->compressed) {
            $content = gzencode($content);
        }

        return $content;
    }

    /**
     * Decode the content from a file, using the specified format
     *
     * @param  string  $content
     * @return mixed
     */
    public function decodeContent($content)
    {
        if ($this->compressed) {
            $content = gzdecode($content);
        }

        switch ($this->format) {
            case "json":
                return json_decode($content);
            case "json:array":
                return json_decode($content, true);
            case "txt":
                return $content;
            default:
                return maybe_unserialize($content);
        }
    }

    // ==================================================
    // > PRIVATE
    // ==================================================
    /**
     * Get a list of all cached files
     *
     * @return void
     */
    public static function getAllFiles($directory, $format = "json")
    {
        $files = [];
        if (is_dir($directory)) {
            foreach (scandir($directory, SCANDIR_SORT_DESCENDING) as $file) {
                if (strpos($file, "." . $format)) {
                    $files[] = $file;
                }
            }

            // Or create the cache directory if there is none
        } else {
            mkdir($directory, 0777, true);
        }

        return $files;
    }

    /**
     * Create a new cache file
     *
     * @param  mixed   $content
     * @return mixed
     */
    public function store($content)
    {
        // Get the file to write in
        $filename = $this->now . "." . $this->format;
        $filepath = $this->directory . "/" . $filename;
        $file     = fopen($filepath, "w");

        // Store the content in the file
        fwrite($file, $this->encodeContent($content));
        chmod($filepath, 0777);
        fclose($file);

        // Add file to the list and delete files that are too old
        array_unshift($this->files, $filename);
        $this->checkGarbage($this->history);

        return $content;
    }

    /**
     * Remove files that are too old.
     * Only keep $this->histsory number of files
     *
     * @return void
     */
    public function checkGarbage($keep)
    {
        while (count($this->files) > $keep) {
            $filename = array_pop($this->files);
            if (file_exists($this->directory . "/" . $filename)) {
                unlink($this->directory . "/" . $filename);
            }
        }
    }

    /**
     * Get the data from a file by its index
     *
     * @param  mixed $fileSearch Name or index of the file
     * @return mix
     */
    public function getDataFrom($fileSearch = 0)
    {
        if (empty($this->files)) {
            return false;
        }

        // Get the correct file
        $file = false;

        if (is_int($fileSearch)) { // by id
            $file = $this->directory . "/" . $this->files[$fileSearch];

        } elseif (is_string($fileSearch)) { // by name
            foreach ($this->files as $fileIndex => $fileName) {
                if ($fileName == $fileSearch . "." . $this->format) {
                    $file = $this->directory . "/" . $this->files[$fileIndex];
                    break;
                }
            }
        }

        if (!file_exists($file)) {
            return false;
        }

        return $this->decodeContent(file_get_contents($file));
    }
}