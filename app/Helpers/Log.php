<?php

namespace Syltaen;

class Log
{
    /**
     * Add a new log entry, the method name is the filename
     *
     * @param  string $method The name of the log file
     * @param  string $line   The line to add
     * @return void
     */
    public static function __callStatic($method, $line)
    {
        static::add($line[0], $method);
    }

    /**
     * Add a new line to a log file
     *
     * @param  mixed  $line    If it's not a scalar value, it will be converted to JSON
     * @param  string $logfile Name of the log file
     * @return void
     */
    public static function add($line, $logfile)
    {
        if (!is_scalar($line)) {
            $line = json_encode($line);
        }

        $cache = cache("logs", 0, 0, "log");

        // Get the files
        $path = $cache->directory . "/" . $logfile . ".log";

        // Add the line
        $last    = file_exists($path) ? file_get_contents($path) : "";
        $content = $last ? $last . "\n" : "";
        $content .= "[" . date("d/m/Y H:i:s", $cache->now) . "] " . $line;

        // Trim lines over the LOG_HISTORY
        $content = implode("\n", array_slice(explode("\n", $content), -config("debug.log_history"), config("debug.log_history")));

        // Rewrite the file
        $file = fopen($path, "w");
        fwrite($file, $content);
        chmod($path, 0777);
        fclose($file);
    }

    /**
     * Get data from a log file
     *
     * @param  string   $logfile
     * @return string
     */
    public static function get($logfile)
    {
        return cache("logs", 0, 0, "log")->getDataFrom($logfile);
    }

    // =============================================================================
    // > DEBUGGING
    // =============================================================================
    /**
     * Log debug data in the console
     *
     * @param  [type]  $data
     * @param  string  $log_file
     * @param  boolean $context
     * @return void
     */
    public static function debug()
    {
        $data = static::prepareData(func_get_args());
        $data = !is_scalar($data) ? json_encode($data) : $data;
        static::add("[" . static::getBacktrace() . "] " . $data, "debug");
    }

    /**
     * Log every arguments passed in the console useing PhpConsole
     *
     * @return void
     */
    public static function console()
    {
        $levelLimit      = 10;
        $itemsCountLimit = 100;
        $itemSizeLimit   = 50000;
        $dumpSizeLimit   = 500000;
        $dumper          = new \PhpConsole\Dumper($levelLimit, $itemsCountLimit, $itemSizeLimit, $dumpSizeLimit);

        $connector = \PhpConsole\Connector::getInstance();
        $connector->setDebugDispatcher(new \PhpConsole\Dispatcher\Debug($connector, $dumper));
        $connector->getDebugDispatcher()->dispatchDebug(static::prepareData(func_get_args()), static::getBacktrace() . "\n");
    }

    /**
     * Send every argument passed as json
     *
     * @return void
     */
    public static function json()
    {
        wp_send_json([static::getBacktrace() => static::prepareData(func_get_args())]);
    }

    /**
     * Process data to be logged
     *
     * @param  array  $args
     * @return void
     */
    public static function prepareData($data)
    {
        $data = (array) $data;

        if (config("debug.fetch_fields")) {
            $data = array_map(function ($item) {
                if ($item instanceof Model || $item instanceof ModelItem) {
                    $item->fetchFields();
                }
                return $item;
            }, $data);
        }

        if (count($data) === 1) {
            return $data[0];
        }

        return $data;
    }

    /**
     * Get the backtrace as a string, do not include calls made inside this class
     *
     * @return string
     */
    public static function getBacktrace()
    {
        $calls = array_filter(array_map(function ($call) {
            if (!empty($call["file"]) && strpos($call["file"], "Log.php") != false) {
                return false;
            }
            return (!empty($call["file"]) ? basename($call["file"]) : ($call["class"] ?? "")) . ":" . ($call["line"] ?? "");
        }, debug_backtrace()));

        // Keep only the desired amount
        $calls = array_slice($calls, 0, config("debug.backtrace_level"));

        // Return as a string
        return implode(" > ", array_reverse($calls));
    }
}
