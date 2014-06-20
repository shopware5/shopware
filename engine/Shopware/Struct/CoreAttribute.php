<?php

namespace Shopware\Struct;

class CoreAttribute implements Attribute
{
    /**
     * Internal storage which contains all struct data.
     *
     * @var array
     */
    private $storage = array();

    /**
     * Checks if a storage key exists
     *
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->storage);
    }

    /**
     * Sets a single store value.
     * The attribute storage allows only serializable
     * values which allows shopware to serialize the struct elements.
     *
     * @param $name
     * @param $value
     * @throws \Exception
     */
    public function set($name, $value)
    {
        if (!is_scalar($value) && $value !== null) {
            throw new \Exception(sprintf(
                'Class values should be serializable',
                __CLASS__
            ));
        }

//        if (!$value instanceof \Serializable) {
//            //...
//        }

        $this->storage[$name] = $value;
    }

    /**
     * Returns the whole storage data.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->storage;
    }

    /**
     * Returns a single storage value.
     *
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->storage[$name];
    }
}