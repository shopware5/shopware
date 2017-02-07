<?php
namespace Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 * @package   Shopware\Bundle\StoreFrontBundle\Struct
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
interface ExtendableInterface
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
     * @param Attribute[] $attributes
     */
    public function addAttributes(array $attributes);

    /**
     * Returns a single attribute struct element of this class.
     * The passed name is used as unique identifier.
     *
     * @param $name
     * @return Attribute
     */
    public function getAttribute($name);

    /**
     * Helper function which checks if an associated
     * attribute exists.
     *
     * @param $name
     * @return bool
     */
    public function hasAttribute($name);

    /**
     * Returns all stored attribute structures of this class.
     * The array has to be an associated array with name and attribute instance.
     *
     * @return Attribute[]
     */
    public function getAttributes();
}
