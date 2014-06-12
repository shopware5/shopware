<?php

namespace Shopware\Struct;

/**
 * Interface Extendable
 *
 * @package Shopware\Struct
 */
abstract class Extendable
{
    /**
     * Contains an array of attribute structs.
     *
     * @var Attribute[]
     */
    protected $attributes = array();

    /**
     * Adds a new attribute struct into the class storage.
     * The passed name is used as unique identifier and has to be stored too.
     *
     * @param string $name
     * @param Attribute $attribute
     */
    public function addAttribute($name, Attribute $attribute)
    {
        $this->attributes[$name] = $attribute;
    }

    /**
     * Returns a single attribute struct element of this class.
     * The passed name is used as unique identifier.
     *
     * @param $name
     * @return Attribute
     */
    public function getAttribute($name)
    {
        return $this->attributes[$name];
    }

    /**
     * Helper function which checks if an associated
     * attribute exists.
     *
     * @param $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return array_key_exists($name, $this->attributes);
    }

    /**
     * Returns all stored attribute structures of this class.
     * The array has to be an associated array with name and attribute instance.
     *
     * @return Attribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
