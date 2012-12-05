<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Components
 * @subpackage Jira
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 */

namespace Shopware\Components\Jira\API\Model;

/**
 * Abstract base class representing any JIRA entity.
 */
abstract class ValueObject
{
    /**
     * Creates a new value object instance with pre filled properties, based on
     * the given <b>$values</b> parameter.
     *
     * @param array $values Optional default/initialize values.
     *
     * @throws \OutOfRangeException
     */
    public function __construct(array $values = array())
    {
        foreach ($values as $name => $value) {
            if (false === property_exists($this, $name)) {
                throw new \OutOfRangeException("No property \${$name} exists.");
            }
            $this->{$name} = $value;
        }
    }

    /**
     * Magic getter method, just to avoid some strange/unintended object use
     * within application code.
     *
     * @param string $property Name of the requested
     *
     * @return void
     * @throws \OutOfRangeException
     */
    public function __get($property)
    {
        throw new \OutOfRangeException("No property \${$property} exists.");
    }

    /**
     * Magic setter method, just to avoid some strange/unintended object use
     * within application code.
     *
     * @param string $property Name of the requested
     * @param string $value The new property value.
     *
     * @return void
     * @throws \OutOfRangeException
     */
    public function __set($property, $value)
    {
        throw new \OutOfRangeException("Property \${$property} is readonly.");
    }
}