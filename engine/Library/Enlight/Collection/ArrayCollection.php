<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * Interface allows an easy implementation for the Countable, IteratorAggregate, ArrayAccess class
 *
 * Array collection class which implements the Enlight_Collection_Collection interface.
 * The interface allows an easy implementation for the Countable, IteratorAggregate, ArrayAccess class.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Collection_ArrayCollection implements Enlight_Collection_Collection
{
    /**
     * This property contains all added elements.
     *
     * @var array
     */
    protected $_elements;

    /**
     * Expects an array as a parameter with default elements.
     *
     * @param array $elements
     */
    public function __construct($elements = [])
    {
        $this->_elements = (array) $elements;
    }

    /**
     * Sets a value of an element in the list.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value = null)
    {
        $this->set($key, $value);
    }

    /**
     * Returns a value of an element in the list.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }

    /**
     * Checks whether an element with a given name is stored.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return $this->containsKey($key);
    }

    /**
     * Deletes an item from the list.
     *
     * @param string $key
     *
     * @return Enlight_Collection_ArrayCollection
     */
    public function __unset($key)
    {
        $this->remove($key);
    }

    /**
     * Captures the magic phone calls and executes them accordingly.
     *
     * @param string $name
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($name, $args = null)
    {
        switch (substr($name, 0, 3)) {
            case 'get':
                $key = strtolower(substr($name, 3, 1)) . substr($name, 4);
                $key = strtolower(preg_replace('/([A-Z])/', '_$0', $key));

                return $this->get($key);
            case 'set':
                $key = strtolower(substr($name, 3, 1)) . substr($name, 4);
                $key = strtolower(preg_replace('/([A-Z])/', '_$0', $key));

                return $this->set($key, isset($args[0]) ? $args[0] : null);
            default:
                throw new Enlight_Exception(
                    'Method "' . get_class($this) . '::' . $name . '" not found failure',
                    Enlight_Exception::METHOD_NOT_FOUND
                );
        }
    }

    /**
     * Counts the stored items.
     *
     * @return int
     */
    public function count()
    {
        return count($this->_elements);
    }

    /**
     * Sets a value of an element in the list.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return Enlight_Collection_ArrayCollection
     */
    public function set($key, $value)
    {
        $this->_elements[$key] = $value;

        return $this;
    }

    /**
     * Returns a value of an element in the list.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->_elements[$key]) ? $this->_elements[$key] : null;
    }

    /**
     * Checks whether an element with a given name is stored.
     *
     * @param string $key
     *
     * @return bool
     */
    public function containsKey($key)
    {
        return array_key_exists($key, $this->_elements);
    }

    /**
     * Deletes an item from the list.
     *
     * @param string $key
     *
     * @return Enlight_Collection_ArrayCollection
     */
    public function remove($key)
    {
        unset($this->_elements[$key]);

        return $this;
    }

    /**
     * Checks whether an element with a given name is stored.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->containsKey($key);
    }

    /**
     * Deletes an item from the list.
     *
     * @param unknown_type $key
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * Returns a value of an element in the list.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Sets a value of an element in the list.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Returns the iterator instance for the list.
     *
     * @return Iterator
     */
    public function getIterator()
    {
        $ref = &$this->_elements;

        return new ArrayIterator($ref);
    }
}
