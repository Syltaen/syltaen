<?php

/**
 * Wrapper for any array to allow OOP methods on them
 */

namespace Syltaen;

class Set extends \ArrayObject implements \JsonSerializable

{
    // ==================================================
    // > FINDING ITEMS
    // ==================================================
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

    /**
     * Return the last item in the set
     *
     * @return mixed
     */
    public function first()
    {
        $array = $this->getArrayCopy();
        return current($array);
    }

    /**
     * Return the last item in the set
     *
     * @return mixed
     */
    public function last()
    {
        $array = $this->getArrayCopy();
        return end($array);
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
     * Sort keys of the set
     *
     * @return self
     */
    public function sortKeys()
    {
        $this->ksort();
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
    public function insert($array, $position = null)
    {
        // Get the numerical index where the set should be split
        $index = is_null($position) ? $this->count() : (
            is_int($position) ? $position : (
                // If a string/key is given : try to get its position
                ($index = $this->keys()->search($position)) !== false ? $index + 1 : (
                    // Default to the end of the set
                    $this->count()
                )
            )
        );

        $this->exchangeArray(array_merge(
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
            $data               = Data::getAdvanced($key, $value, $data_source, $this);
            $this[$data["key"]] = $data["value"];
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
     * Get all items that apear more than once
     * @param  int   $limit Minimum number of occurance
     * @return Set
     */
    public function duplicates($limit = 2)
    {
        return $this->groupBy(function ($item) {
            return $item;
        })->filter(function ($item) use ($limit) {
            return count($item) >= $limit;
        })->keys();
    }

    /**
     * Implementation of the "array_diff" function
     *
     * @param  array $array
     * @return Set
     */
    public function remove($array, $preserve_keys = false)
    {
        $array = array_diff((array) $this, (array) $array);
        if (!$preserve_keys) {
            $array = array_values($array);
        }

        return new static($array);
    }

    /**
     * Implementation of the "array_intersect" function
     *
     * @param  array $array
     * @return Set
     */
    public function keep($array)
    {
        $array = array_intersect((array) $this, (array) $array);
        return new static($array);
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
        return new static(array_reduce(array_map($callback, (array) $this->keys(), (array) $this->values()), function ($total, $subarray) {
            return $total + $subarray;
        }, []));
    }

    /**
     * Implementation of the "array_keys" function
     *
     * @return Set
     */
    public function keys()
    {
        return new static(array_keys((array) $this));
    }

    /**
     * Implementation of the array_v"alues function
     *
     * @return Set
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
     * Flatten a multi-dimensional array into a single level
     *
     * @return Set
     */
    public function flatten()
    {
        $return = [];
        $array  = (array) $this->getArrayCopy();
        array_walk_recursive($array, function ($a, $k) use (&$return) {
            $return[] = $k;
        });
        return new static($return);
    }

    /**
     * Keep only a specific column of each child array/set
     *
     * @param  string $name
     * @return Set
     */
    public function column($name)
    {
        $columns = [];

        foreach ($this as $i => $row) {
            $columns[$i] = (new static($row))->get($name);
        }

        return new static($columns);
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
            // Get group key
            if (is_callable($key)) {
                $key = $key($item);
            } else {
                $item = (array) $item;
                $key  = ((array) $item)[$key];
            }

            // Get value
            $value = is_callable($value_key) ? $value_key($item)
                : (is_string($value_key) ? ((array) $item)[$value_key]
                    : $item);

            // Init a new group if it does not exist
            $groups[$key] = $groups[$key] ?? [];

            // Add value to the group
            $groups[$key][] = $value;
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
            if ($value instanceof Set) {
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
    public function callEach($deepness = 1)
    {
        $cc = new CallableCollection($this);

        if ($deepness == 1) {
            return $cc;
        }

        return $cc->callEach($deepness - 1)->callEach();
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
     * Sum all values in the set, or a specific column of sub-elements if specified
     *
     * @param  string|bool $column
     * @return int|float
     */
    public function sum($column = false)
    {
        $items = $column ? $this->column($column) : $this;

        return $items->reduce(function ($sum, $item) {
            return $sum + $item;
        }, 0);
    }

    /**
     * Get the count for each unique value
     *
     * @return Set
     */
    public function valueCounts()
    {
        return $this->filter()->groupBy(function ($value) {
            return $value;
        })->map("count");
    }

    /**
     * Check if a value is present in the set
     *
     * @return boolean
     */
    public function hasValue($value, $recursive = false)
    {
        if (in_array($value, (array) $this)) {
            return true;
        }

        if ($recursive) {
            foreach ($this as $item) {
                if (is_array($item)) {
                    $item = new static($item);
                }

                if ($item instanceof Set && $item->hasValue($value, true)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Check if a key is defined in the set
     *
     * @return boolean
     */
    public function hasKey($key, $recursive = false)
    {
        if (array_key_exists($key, (array) $this)) {
            return true;
        }

        if ($recursive) {
            foreach ($this as $item) {
                if (is_array($item)) {
                    $item = new static($item);
                }

                if ($item instanceof Set && $item->hasKey($key, true)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Check if the set is empty : /!\ the empty function will always return false
     *
     * @return bool
     */
    public function empty()
    {
        return !$this->count();
    }

    // ==================================================
    // > DISPLAYING ITEMS
    // ==================================================
    /**
     * Return the items as an html list
     *
     * @param  string   $tag
     * @return string
     */
    public function htmlList($tag = "ul", $class = false)
    {
        return "<$tag" . ($class ? " class='$class'" : "") . ">" . $this->reduce(function ($html, $item) {
            return $html . "<li>$item</li>";
        }, "") . "</$tag>";
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
     * @param string $key
     * @param mixed  $val
     */
    public function set($key, $val)
    {
        $parts = static::getKeyParts($key);
        $array = $this->getArrayCopy();
        $pos   = &$array;

        foreach ($parts as $part) {
            $pos[$part] = $pos[$part] ?? [];
            $pos        = &$pos[$part];
        }

        $pos = $val;
        $this->exchangeArray($array);
    }

    /**
     * Get a key from the array using object notation
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        $parts = static::getKeyParts($key);
        $value = $this->getArrayCopy();

        foreach ($parts as $part) {
            $value = $value[$part] ?? null;
            if (is_null($value)) {return $value;}
        }

        return $value;
    }

    /**
     * Get the parts for a complexe key
     *
     * @param  string  $key
     * @return array
     */
    public static function getKeyParts($key)
    {
        $key = trim($key, "[]");
        $key = str_replace(["][", "[", "]"], ".", $key);
        return explode(".", $key);
    }

    // ==================================================
    // > DEBUG / JsonSerializable Interface
    // ==================================================
    /**
     * When parsed to JSON, return the array version
     *
     * @return array
     */
    public function jsonSerialize(): mixed
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
