<?php

namespace Syltaen;

abstract class Database
{
    /**
     * Shortcut for the global wpdb
     *
     * @return global $wpdb
     */
    public static function db()
    {
        global $wpdb;
        return $wpdb;
    }

    /**
     * Add DB prefixes to an SQL query
     *
     * @param  string   $sql
     * @return string
     */
    public static function addDBPrefixes($query)
    {
        $protected_keywords = [
            "KEY UPDATE",
            "ON UPDATE",
        ];

        $prefixed_keywords = [
            "FROM",
            "JOIN",
            "INSERT INTO",
            "UPDATE",
        ];

        // Protect protected keywords
        foreach ($protected_keywords as $keyword) {
            $query = str_replace($keyword, md5($keyword), $query);
        }

        // Add prefixes
        foreach ($prefixed_keywords as $keyword) {
            $prefixed_keyword = $keyword . " " . static::db()->prefix;
            $query            = str_replace($prefixed_keyword, md5($prefixed_keyword), $query); // Protec keywords that are already prefixed
            $query            = str_replace($keyword . " ", $prefixed_keyword, $query); // Add prefix
            $query            = str_replace(md5($prefixed_keyword), $prefixed_keyword, $query); // Revert protection
        }

        // Revert protected keywords
        foreach ($protected_keywords as $keyword) {
            $query = str_replace(md5($keyword), $keyword, $query);
        }

        return $query;
    }

    /**
     * Run a WordPress SQL query and auto-add table prefixes
     *
     * @return int The number of rows affected
     */
    public static function query($query)
    {
        return static::db()->query(static::addDBPrefixes($query));
    }

    /**
     * Get results from the database
     *
     * @param  string $query
     * @param  const  $output_type OBJECT, OBJECT_K, ARRAY_A, ARRAY_N
     * @return Set
     */
    public static function get_results($query, $output_type = OBJECT)
    {
        return set(static::db()->get_results(static::addDBPrefixes($query), $output_type));
    }

    /**
     * Get a single row from the database
     *
     * @param  string $query
     * @param  const  $output_type OBJECT, OBJECT_K, ARRAY_A, ARRAY_N
     * @return Set
     */
    public static function get_row($query, $output_type = OBJECT, $row_offset = 0)
    {
        $row = static::db()->get_row(static::addDBPrefixes($query), $output_type, $row_offset);
        if ($row) {
            return set($row);
        }

        return false;
    }

    /**
     * Get a single col from the database
     *
     * @param  string $query
     * @return Set
     */
    public static function get_col($query, $col_offset = 0)
    {
        return set(static::db()->get_col(static::addDBPrefixes($query), $col_offset));
    }

    /**
     * Run a WordPress SQL query and auto-add table prefixes
     *
     * @return mixed The value
     */
    public static function get_var($query, $column_offset = 0, $row_offset = 0)
    {
        return static::db()->get_var(static::addDBPrefixes($query), $column_offset, $row_offset);
    }

    /**
     * Insert data into the database
     *
     * @return int The number of rows affected
     */
    public static function insert($table, $data, $format = null)
    {
        return static::db()->insert(static::db()->prefix . $table, $data, $format);
    }

    /**
     * Get the last inserted ID
     *
     * @return int
     */
    public static function getInsertID()
    {
        return static::db()->insert_id;
    }

    /**
     * Replaces a row in a table if it exists or inserts a new row in a table if the row did not already exist
     * In mot cases, it should be a INSERT + DUPLICATE KEY UPDATE statement
     *
     * @return int The number of rows affected
     */
    public static function replace($table, $data, $format = null)
    {
        return static::db()->replace(static::db()->prefix . $table, $data, $format);
    }

    /**
     * Replaces a row in a table if it exists or inserts a new row in a table if the row did not already exist
     * In mot cases, it should be a INSERT + DUPLICATE KEY UPDATE statement
     *
     * @return int The number of rows affected
     */
    public static function update($table, $data, $where, $format = null, $where_format = null)
    {
        return static::db()->update(static::db()->prefix . $table, $data, $where, $format, $where_format);
    }

    /**
     * Delete rows from the database
     *
     * @return int The number of rows affected
     */
    public static function delete($table, $where, $where_format = null)
    {
        return static::db()->delete(static::db()->prefix . $table, $where, $where_format);
    }

    /**
     * Prepare a query
     *
     * @param  string   $query
     * @param  mixed    ...$args
     * @return string
     */
    public static function prepare($query, ...$args)
    {
        return static::db()->prepare($query, ...$args);
    }

    // =============================================================================
    // > DATA PARSING
    // =============================================================================
    /**
     * Prepare an array to be used for IN clauses
     *
     * @param  array    $array
     * @return string
     */
    public static function inArray($array)
    {
        return "(" . implode(",", array_map(function ($item) {
            return "\"$item\"";
        }, (array) $array)) . ")";
    }

    // =============================================================================
    // > TOOLS
    // =============================================================================
    /**
     * Get the DB_NAME constant from a configuration file.
     *
     * @param  string   $config_file The name of the config file
     * @return string
     */
    public static function getName($config_file = "wp-config.php")
    {
        $config_file    = ABSPATH . $config_file;
        $config_content = file_get_contents($config_file);
        preg_match("/define\( ?\'DB_NAME\', \'([^)]+)\' ?\);/", $config_content, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        }

        return false;
    }

    /**
     * Create a backup of a database and save it in a file
     *
     * @param  boolean $download If the file should be downloaded by the browser
     * @param  string  $filename Custom filename
     * @param  string  $path     Custom path
     * @return void
     */
    public static function backup($download = false, $filename = false, $path = false)
    {
        $filename = $filename ? $filename : "backup-" . date("Ymd-His") . ".sql";
        $path     = $path ? $path : ABSPATH . "backups/";

        if (!is_dir($path)) {
            mkdir($path);
        }

        exec(static::mysqlCommand(DB_NAME . " > {$path}{$filename}", "mysqldump"));

        if ($download) {
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            passthru("cat {$path}{$filename}");
        }
    }

    /**
     * Clone the current database into another, mostly to use as a test environnement
     * Note: the two database must have the same access information - Only the name must be different
     * @param  string $to_db   Name of the database to clone to.
     * @param  string $from_db Name of the database to clone from.
     * @return void
     */
    function clone ($from_db, $to_db) {
        exec(static::mysqlCommand("-e \"CREATE DATABASE IF NOT EXISTS {$to_db}\""));
        exec(static::mysqlCommand($from_db, "mysqldump") . " | " . static::mysqlCommand($to_db));
    }

    /**
     * Generate a runnable MySql command using that use the provided binary
     *
     * @param  string $commad   The command to executre
     * @param  string $bin      The binary to use : mysql, mysqldump, ...
     * @param  string $bin_path You may need to change that depending on the system running your databases
     * @return void
     */
    public static function mysqlCommand($command, $bin = "mysql", $bin_path = "/Applications/MAMP/Library/bin/")
    {
        return "{$bin_path}{$bin} -h " . DB_HOST . " -u " . DB_USER . " --password=" . DB_PASSWORD . " " . $command;
    }

    /**
     * Change the configuration file "wp-config.php" to use a different database
     * Mainly used during acceptance tests
     * @param  string $current The name of the current database
     * @param  string $new     The name of the new database
     * @return void
     */
    function switch ($current, $new, $config_file = "wp-config.php") {
            $config_content = file_get_contents(ABSPATH . $config_file);
            $config_content = str_replace(
                [
                    "define( 'DB_NAME', '" . $current . "' );",
                    "define('DB_NAME', '" . $current . "');",
                ],
                "define('DB_NAME', '" . $new . "');",
                $config_content
            );
            file_put_contents(ABSPATH . $config_file, $config_content);
    }
}