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
 * Link Validator.
 *
 * The Enlight_Components_Test_Constraint_LinkExists class checks whether links exist.
 *
 * @category   Enlight
 * @package   Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Test_Constraint_LinkExists extends PHPUnit_Framework_Constraint
{
    /**
     * Evaluates the constraint for parameter $other. Returns TRUE if the
     * constraint is met, FALSE otherwise.
     *
     * @param mixed $other Value or object to evaluate.
     * @return bool
     */
    public function matches($other)
    {
        $headers = @get_headers($other);
        if (empty($headers)) {
            return false;
        }
        return preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $headers[0]);
    }

    /**
     * Constraint fail method.
     *
     * @param   mixed   $other The value passed to evaluate() which failed the
     *                         constraint check.
     * @param   string  $description A string with extra description of what was
     *                               going on while the evaluation failed.
     * @param   boolean $not Flag to indicate negation.
     * @throws  PHPUnit_Framework_ExpectationFailedException
     */
    public function fail($other, $description, $not = false)
    {
        $failureDescription = sprintf('Failed asserting that link "%s" exists.', $other);

        if ($not) {
            $failureDescription = self::negate($failureDescription);
        }

        if (!empty($description)) {
            $failureDescription = $description . "\n" . $failureDescription;
        }

        throw new PHPUnit_Framework_ExpectationFailedException($failureDescription);
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return 'link exists';
    }
}
