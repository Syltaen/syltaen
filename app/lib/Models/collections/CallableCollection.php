<?php

namespace Syltaen;

/**
 * A class that hold a set and allow to call a specific method on each of its elements.
 */

class CallableCollection
{
    /**
     * The set of elements to executre the methods on
     *
     * @var Set
     */
    public $set = null;

    /**
     * Transfer each method calls to the elements of the set
     *
     * @param  [type] $method
     * @param  [type] $args
     * @return void
     */
    public function __call($method, $args)
    {
        return $this->set->mapAssoc(function ($key, $item) use ($method, $args) {
            $item = is_array($item) ? new Set($item) : $item;
            // Call the method on each item
            $result = call_user_func_array([$item, $method], $args);
            // If it's a collection of ModelItem, index each result by the item's ID
            $key = $item instanceof ModelItem ? $item->getID() : $key;
            // Return the key and the result
            return [$key, $result];
        });
    }

    /**
     * Create a new instance
     *
     * @param Set|Array $set
     */
    public function __construct($set)
    {
        if (!($set instanceof Set)) {
            $this->set = new Set($set);
        } else {
            $this->set = $set;
        }
    }
}