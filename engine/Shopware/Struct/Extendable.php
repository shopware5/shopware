<?php

namespace Shopware\Struct;

/**
 * Interface Extendable
 *
 * @package Shopware\Struct
 */
interface Extendable
{
    /**
     * Adds a new attribute struct into the class storage.
     * The passed name is used as unique identifier and has to be stored too.
     *
     * @param string $name
     * @param Attribute $attribute
     */
    public function addAttribute($name, Attribute $attribute);

    /**
     * Returns a single attribute struct element of this class.
     * The passed name is used as unique identifier.
     *
     * @param $name
     * @return Attribute
     */
    public function getAttribute($name);

    /**
     * Returns all stored attribute structures of this class.
     * The array has to be an associated array with name and attribute instance.
     *
     * @return Attribute[]
     */
    public function getAttributes();
}