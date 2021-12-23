<?php

/**
 * Wrapper for any array to allow OOP methods on them
 */

namespace Syltaen;

class Set extends \ArrayObject implements \JsonSerializable
{
    // =============================================================================
    // > ITEMS MANIPULATIONS
    // =============================================================================

    // ==================================================
    // > FINDING ITEMS
    // ==================================================
    /**
     * @param  $key_path. Example : config.mail.from
     * @return mixed
     */
    public function get($key_path)
    {
        $parts = explode(".", $key_path);
        $value = $this;

        foreach ($parts as $part) {
            $value = $value[$part];
        }

        return $value;
    }

    /**
     * Implementation of the "array_search" function
     *
     * @param  mixed $item
     * @return mixed Key
     */
    public function search($item)
    {
        return array_search($item, (array) $this);
    }

    /**
     * Find an array|object item by its property.
     * Find the first match's key.
     * @param  [type] $key
     * @param  [type] $value
     * @return void
     */
    public function searchBy($key, $value)
    {
        foreach ($this as $i => $item) {
            if (static::itemMatchBy($item, $key, $value)) {
                return $i;
            }

        }
        return false;
    }

    /**
     * Find an array|object item by its property.
     * Return the first match.
     *
     * @param  string         $key
     * @param  mixed          $value
     * @return array|object
     */
    public function getBy($key, $value)
    {
        $found = $this->searchBy($key, $value);
        if ($found !== false) {
            return $this[$found];
        }

        return false;
    }

    // ==================================================
    // > SORTING ITEMS
    // ==================================================
    /**
     * Shortcut for uasort and asort
     *
     * @param  mixed  $callback Sort automatically or with a callback function
     * @return self
     */
    public function sort($callback = false)
    {
        if ($callback) {
            $this->uasort($callback);
        } else {
            $this->asort();
        }
        return $this;
    }

    /**
     * Implementation of the "array_reverse" function
     *
     * @return Set
     */
    public function reverse()
    {
        return new static(array_reverse((array) $this));
    }

    // ==================================================
    // > ADDING ITEMS
    // ==================================================
    /**
     * Insert new elements in the list at a specific position
     *
     * @return self
     */
    public function insert($position, $array)
    {
        // Get the numerical index where the set should be split
        $index = is_int($position) ? $position
        // If a string/key is given : try to get its position
         : (($index = $this->keys()->search($position)) !== false ? $index + 1
            // Default to the end of the set
             : count($array));

        static::exchangeArray(array_merge(
            array_slice((array) $this, 0, $index, true),
            (array) $array,
            array_slice((array) $this, $index, null, true)
        ));

        return $this;
    }

    /**
     * Add a new item at the end of the array
     *
     * @param  mixed  $item
     * @return self
     */
    public function push($item)
    {
        $this->append($item);
        return $this;
    }

    /**
     * Add a new item at the end of the array
     *
     * @param  mixed  $item
     * @return self
     */
    public function merge($items = false)
    {
        if ($items) {
            return new static(array_merge(
                (array) $this,
                (array) $items
            ));
        }

        // No items to merge with, try to merge all the set's children
        return $this->reduce(function ($set, $row) {
            return $set->merge($row);
        }, new static );
    }

    /**
     * Store one or several fields values
     *
     * @param  [type]     $fields
     * @param  string|int $data_source The object ID or option page
     * @return void
     */
    public function store($fields, $data_source = null)
    {
        if (empty($fields)) {
            return false;
        }

        // Normalize keys that don't have a default value
        $fields = Data::normalizeFieldsKeys($fields);

        foreach ($fields as $key => $value) {
            $data                 = Data::getAdvanced($key, $value, $data_source, $this);
            $this->{$data["key"]} = $data["value"];
        }

        return $this;
    }

    // ==================================================
    // > REMOVING/FILTERING ITEMS
    // ==================================================
    /**
     * Implementation of the "array_filter" function
     *
     * @param  callable $callback
     * @return Set
     */
    public function filter($callback = false)
    {
        return $callback
            ? new static(array_filter((array) $this, $callback, ARRAY_FILTER_USE_BOTH))
            : new static(array_filter((array) $this));
    }

    /**
     * Filter allarray|object item by their properties.
     * Return only the one that match a speicifc key=>value
     *
     * @param  string $key
     * @param  mixed  $value
     * @return Set
     */
    public function filterBy($key, $value)
    {
        return $this->filter(function ($item) use ($key, $value) {
            return static::itemMatchBy($item, $key, $value);
        });
    }

    /**
     * Remove an array|object item by its property.
     * Remove all
     *
     * @param  string         $key
     * @param  mixed          $value
     * @return array|object
     */
    public function removeBy($key, $value)
    {
        foreach ($this as $i => $item) {
            if (static::itemMatchBy($item, $key, $value)) {
                unset($this[$i]);
            }
        }
        return $this;
    }

    /**
     * Unset all the keys
     *
     * @return self
     */
    public function clear()
    {
        foreach ($this->keys() as $key) {
            unset($this[$key]);
        }
        return $this;
    }

    /**
     * Implementation of the "array_unique" function
     *
     * @param  callable $callback
     * @return Set
     */
    public function unique($preserve_keys = false, $flags = SORT_STRING)
    {
        $array = array_unique((array) $this, $flags);
        if (!$preserve_keys) {
            $array = array_values($array);
        }

        return new static($array);
    }

    /**
     * Implementation of the "array_diff" function
     *
     * @param  [type] $array
     * @return void
     */
    public function diff($array, $preserve_keys = false)
    {
        $array = array_diff((array) $this, (array) $array);
        if (!$preserve_keys) {
            $array = array_values($array);
        }

        return new static($array);
    }

    /**
     * Implementation of the "array_diff_key" function
     *
     * @param  [type] $array
     * @return void
     */
    public function keyDiff($array)
    {
        return new static(array_diff_key((array) $this, (array) $array));
    }

    /**
     * Campare both keys and value and return the difference
     *
     * @return Set
     */
    public function fullDiff($array)
    {
        return $this->filter(function ($value, $key) use ($array) {
            if (!isset($array[$key]) || $array[$key] != $value) {
                return true;
            }
            return false;
        });
    }

    /**
     * Implementation of the "array_slice" function
     *
     * @return Set
     */
    public function slice($offset, $length = null, $preserve_keys = false)
    {
        return new static(array_slice((array) $this, $offset, $length, $preserve_keys));
    }

    /**
     * Retrieve a list of items based on a callback
     *
     * @return Set The filtered results
     */
    public function keepKeys($keys_to_keep)
    {
        return new static(array_intersect_key(
            (array) $this,
            array_flip((array) $keys_to_keep)
        ));
    }

    /**
     * Retrieve a list of items based on a callback
     *
     * @return Set The filtered results
     */
    public function removeKeys($keys_to_remove)
    {
        return new static(array_diff_key(
            (array) $this,
            array_flip((array) $keys_to_remove)
        ));
    }

    // ==================================================
    // > CHANGING ITEMS
    // ==================================================
    /**
     * Implementation of the "array_map" function
     *
     * @param  callable $callback
     * @return Set
     */
    public function map($callback)
    {
        return new static(array_map($callback, (array) $this));
    }

    /**
     * Implementation of the "array_map" function and add the keys in the passed arguments
     *
     * @param  callable $callback
     * @return Set
     */
    public function mapWithKey($callback)
    {
        return new static(array_map($callback, (array) $this->values(), (array) $this->keys()));
    }

    /**
     * Map an associative array, allow to change its key and value
     *
     * @param  callable $callback Should return [$key, $value] array
     * @param  array    $assoc    The array to process
     * @return Set
     */
    public function mapAssoc($callback)
    {
        return new static(array_column(array_map($callback, (array) $this->keys(), (array) $this->values()), 1, 0));
    }

    /**
     * Implementation of the "array_keys" function
     *
     * @return array
     */
    public function keys()
    {
        return new static(array_keys((array) $this));
    }

    /**
     * Implementation of the array_v"alues function
     *
     * @return array
     */
    public function values()
    {
        return new static(array_values((array) $this));
    }

    /**
     * Implementation of the "array_flip" function
     *
     * @return Set
     */
    public function flip()
    {
        return new static(array_flip((array) $this));
    }

    /**
     * Keep only a specific column of each child array/set
     *
     * @param  string  $name
     * @return array
     */
    public function column($name)
    {
        $array = [];
        foreach ($this as $i => $row) {
            $array[$i] = (array) $row;
        }

        return new static(array_combine(array_keys($array), array_column($array, $name)));
    }

    /**
     * Reindex an set using a specific column of each each item, or a callback
     *
     * @param  string|callback      $key
     * @param  bool|string|callback $value_key The key to keep for each value
     * @return Sed
     */
    public function index($key, $value_key = false)
    {
        return new static($this->reduce(function ($set, $item) use ($key, $value_key) {
            $key = is_string($key) ? ((array) $item)[$key]
                : $key($item);

            $value = is_string($value_key) ? (((array) $item)[$value_key] ?? null)
                : (is_callable($value_key) ? $value_key($item)
                    : $item);

            $set[$key] = $value;
            return $set;
        }));
    }

    /**
     * Group all children by a common value
     *
     * @param  string               $key       Key of the value to group by
     * @param  bool|string|callback $value_key The key to keep for each value
     * @return Set
     */
    public function groupBy($key, $value_key = false)
    {
        return $this->reduce(function ($groups, $item) use ($key, $value_key) {
            $item = (array) $item;
            // Init a new group if it does not exist
            $groups[$item[$key]] = $groups[$item[$key]] ?? [];
            // Add value to the group
            $groups[$item[$key]][] = is_callable($value_key) ? $value_key($item)
                : (is_string($value_key) ? ((array) $item)[$value_key]
                    : $item);
            return $groups;
        });
    }

    // ==================================================
    // > ACT ON ITEMS
    // ==================================================
    /**
     * Custom implementation of the "array_walk" function
     *
     * @param  callable $callback
     * @return Set
     */
    public function walk($callback)
    {
        foreach ($this as $key => &$value) {
            $callback($value, $key);
        }
        return $this;
    }

    /**
     * Custom implementation of the "array_walk_recursive" function
     *
     * @param  callable $callback
     * @return Set
     */
    public function walkRecursive($callback)
    {
        foreach ($this as $key => &$value) {
            if ($value instanceof \Traversable) {
                $value->walkRecursive($callback);
            } else {
                $callback($value, $key);
            }
        }
        return $this;
    }

    /**
     * Return a CallableSet that allows to use a specific method on each element of this set.
     *
     * @return CallableCollection
     */
    public function callEach()
    {
        return new CallableCollection($this);
    }

    // ==================================================
    // > REDUCING ITEMS
    // ==================================================
    /**
     * Implementation of the "array_reduce" function
     *
     * @param  callable $callback
     * @param  mixed    $initial    new Set by default
     * @return mixed
     */
    public function reduce($callback, $initial = null)
    {
        return array_reduce((array) $this, $callback, is_null($initial) ? new static  : $initial);
    }

    /**
     * Implode all items with a join
     *
     * @return string
     */
    public function join($join)
    {
        return implode($join, (array) $this->values());
    }

    /**
     * Get the minimum value in the set
     *
     * @return mixed
     */
    public function min()
    {
        return min((array) $this);
    }

    /**
     * Get the maxmium value in the set
     *
     * @return mixed
     */
    public function max()
    {
        return max((array) $this);
    }

    /**
     * Check if a value is present in the set
     *
     * @return boolean
     */
    public function hasValue($value)
    {
        return in_array($value, (array) $this);
    }

    /**
     * Check if a key is defined in the set
     *
     * @return boolean
     */
    public function hasKey($key)
    {
        return array_key_exists($key, (array) $this);
    }

    /**
     * Check if the set is empty : /!\ the empty function will always return false
     *
     * @return bool
     */
    function empty() {
        return !$this->count();
    }

    // ==================================================
    // > STATIC TOOLS
    // ==================================================

    /**
     * Check that a set item match a key/value pair
     *
     * @param  array|object $item
     * @param  string       $key
     * @param  mixed        $value
     * @return bool
     */
    public static function itemMatchBy($item, $key, $value)
    {
        if (is_array($item) && isset($item[$key]) && $item[$key] == $value) {
            return true;
        }
        if (is_object($item) && isset($item->{$key}) && $item->{$key} == $value) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getArray()
    {
        $array = [];
        foreach ($this as $key => $item) {
            $array[$key] = $item;
        }

        return $array;
    }

    /**
     * Check if the item is a set
     *
     * @param  mixed     $object
     * @return boolean
     */
    public static function is($object)
    {
        return $object instanceof self;
    }

    // ==================================================
    // > MAGIC METHODS
    // ==================================================
    /**
     * When used as string, auto-join with a comma
     *
     * @return string
     */
    public function __toString()
    {
        return $this->join(", ");
    }

    /**
     * Set a key in the array using object notation
     *
     * @param string $name
     * @param mixed  $val
     */
    public function __set($name, $val)
    {
        $this[$name] = $val;
    }

    /**
     * Get a key from the array using object notation
     *
     * @param  string $name
     * @return void
     */
    public function __get($name)
    {
        return $this[$name];
    }

    // ==================================================
    // > DEBUG / JsonSerializable Interface
    // ==================================================
    /**
     * When parsed to JSON, return the array version
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return (array) $this;
    }

    /**
     * Dump the result of a model with all its fields loaded
     *
     * @return void
     */
    public function json()
    {
        wp_send_json($this);
    }

    /**
     * Multiply items until a specifc number of items is met, for testing purposes
     *
     * @param  int   $number
     * @return Set
     */
    public function dummies($number)
    {
        $items   = (array) $this;
        $dummies = new static;

        if (empty($items)) {
            return $dummies;
        }

        for ($i = 0; $i < $number; $i++) {
            $dummies = $dummies->push($items[$i % count($items)]);
        }

        return $dummies;
    }
}