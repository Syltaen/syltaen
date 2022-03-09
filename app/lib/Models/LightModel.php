<?php

namespace Syltaen;

abstract class LightModel
{
    /**
     * The SQL table to act on
     */
    const TABLE = "";

    /**
     * The class to wrap each result with
     */
    const ITEM_CLASS = "";

    /**
     * List of filters used to fetch notifications
     *
     * @var array
     */
    public $filters = [1 => 1];

    /**
     * Results of a GET, cached for multiple runs
     *
     * @var boolean|Set
     */
    public $cachedResults = false;

    /**
     * Order to use in the query
     *
     * @var string
     */
    public $order = "ORDER BY id DESC";

    // =============================================================================
    // > FILTERS
    // =============================================================================
    /**
     * Register a new filter
     *
     * @param  string $column
     * @param  mixed  $value
     * @return self
     */
    public function setFilter($column, $value)
    {
        $this->cachedResults    = false;
        $this->filters[$column] = $value;
        return $this;
    }

    /**
     * Match a specific row by id
     *
     * @param  int    $id
     * @return self
     */
    public function is($id)
    {
        return $this->setFilter("id", $id);
    }

    /**
     * Set the order of the select
     *
     * @param  string $order
     * @return self
     */
    public function order($order)
    {
        $this->cachedResults = false;
        $this->order         = $order;
        return $this;
    }

    // =============================================================================
    // > RESULTS PROCESSING
    // =============================================================================
    /**
     * @param  $row
     * @return mixed
     */
    public function processResult($row)
    {
        return $row;
    }

    // =============================================================================
    // > QUERIES
    // =============================================================================
    /**
     * Run an SQL Select
     * @return Set
     */
    public function get()
    {
        return $this->cachedResults = ($this->cachedResults ?: Database::get_results("SELECT * FROM " . static::TABLE . " WHERE " . static::getArrayAsSQL($this->filters) . " " . $this->order)->map([$this, "processResult"]));
    }

    /**
     * Run an SQL Update
     * @param $columns
     */
    public function update($updates)
    {
        $this->cachedResults = false;
        return Database::get_results("UPDATE " . static::TABLE . " SET " . static::getArrayAsSQL($updates) . " WHERE " . static::getArrayAsSQL($this->filters));
    }

    /**
     * Delete the matching results
     *
     * @return void
     */
    public function delete()
    {
        return Database::get_results("DELETE FROM " . static::TABLE . " WHERE " . static::getArrayAsSQL($this->filters));
    }

    /**
     * Add a new row in the model.
     * Parameters should be sent in the right order.
     *
     * @return void
     */
    public static function add($columns)
    {
        Database::insert(static::TABLE, $columns);
    }

    // =============================================================================
    // > INTERNAL TOOLS
    // =============================================================================
    /**
     * Get an array as a list of SQL EQUALS
     *
     * @param  [type] $array
     * @param  string $join
     * @return void
     */
    public static function getArrayAsSQL($array, $join = " AND ")
    {
        return set($array)->mapWithKey(function ($value, $key) {
            return "$key = $value";
        })->join($join);
    }
}