<?php

/**
 * Represents a collection of OpenSocial objects.  Can be iterated over.
 * @package OpenSocial
 */
class OpenSocial_Collection implements IteratorAggregate, Countable, ArrayAccess
{
    public $startIndex = 0;
    public $totalResults = 0;
    private $items = null;

    /**
     * Constructor
     */
    public function __construct($start = 0, $total = 0, $items = array())
    {
        $this->startIndex = $start;
        $this->totalResults = $total;
        $this->items = $items;
    }

    /**
     * Implements IteratorAggregate.  Allows using foreach on this class.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Implements Countable.  Allows using count() on this class.
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Implements ArrayAccess.  Allows using [$index] access on this class.
     */
    public function offsetExists($offset)
    {
        return isSet($this->items[$offset]);
    }

    /**
     * Implements ArrayAccess.  Allows using [$index] access on this class.
     */
    public function offsetGet($offset)
    {
        if (isSet($this->items[$offset])) {
            return $this->items[$offset];
        }
        else {
            return null;
        }
    }

    /**
     * Implements ArrayAccess.  Allows using [$index] access on this class.
     */
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    /**
     * Implements ArrayAccess.  Allows using [$index] access on this class.
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
}
