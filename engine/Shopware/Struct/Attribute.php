<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Attribute extends Base
{
    private $storage = array();

    /**
     * Checks if a storage key exists
     * @param $key
     * @return bool
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->storage);
    }

    public function set($name, $value)
    {
        if (!is_scalar($value) && $value !== null) {
            throw new \Exception(sprintf(
                'Class values should be serializable',
                __CLASS__
            ));
        }

        $this->storage[$name] = $value;
    }

    public function get($name)
    {
        return $this->storage[$name];
    }
}