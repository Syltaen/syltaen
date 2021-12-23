<?php

/**
 * Set type that force all array elements in it to be a Set
 */

namespace Syltaen;

class RecursiveSet extends Set
{
    /**
     * Add a new item at the end of the array
     *
     * @param  mixed  $item
     * @return self
     */
    public function push($item)
    {
        if (is_array($item)) {
            $item = new self($item);
        }

        return parent::push($item);
    }

    /**
     * Recusively create Set for each children
     *
     * @param array $array
     */
    public function __construct($array = [])
    {
        parent::__construct($array);

        // Make each array children a RecusiveSet
        $this->walk(function (&$child) {
            if (is_array($child)) {
                $child = new self($child);
            }
        });
    }
}