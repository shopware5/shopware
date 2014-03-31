<?php

namespace Shopware\Struct;

/**
 * @package Shopware\Struct
 */
class Attribute
{
    private $storage = array();

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