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
 * @package    Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     $Author$
 */

/**
 * Grants to get access on all array functions.
 *
 * The Enlight_Components_Test_Constraint_ArrayCount is an extension of the PHPUnit_Framework_Constraint
 * to get access on all array functions in the test cases.
 *
 * @category   Enlight
 * @package   Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Test_Constraint_ArrayCount extends PHPUnit_Framework_Constraint
{
    /**
     * @var int
     */
    protected $count;

    /**
     * Constructor method
     *
     * @param int $count
     */
    public function __construct($count)
    {
        $this->count = $count;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     * @return bool
     */
    public function evaluate($other)
    {
        return count($other) === $this->count;
    }

    /**
     * Returns an custom failure description.
     *
     * @param mixed   $other
     * @param string  $description
     * @param boolean $not
     * @return string
     */
    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf('Failed asserting that an array %s.', $this->toString());
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return 'has ' . PHPUnit_Util_Type::toString($this->count) . ' values';
    }
}
