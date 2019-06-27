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
 * Basic class for plugin test cases.
 *
 * The Enlight_Components_Test_Plugin_TestCase extends the Enlight_Components_Test_Controller_TestCase
 * to grant an easy way to create Enlight event arguments. This class represents the basic for plugin tests.
 *
 * @category   Enlight
 * @package    Enlight_Test
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Components_Test_Plugin_TestCase extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Creates a new instance of Enlight_Event_EventArgs by the passed parameters.
     * If the name didn't passed, the class name will be used.
     * If the name passed as array, the name will be used as arguments.
     *
     * @param string|array $name
     * @param array        $args
     * @return Enlight_Event_EventArgs
     */
    public function createEventArgs($name = null, $args = array())
    {
        if ($name === null) {
            $name = get_class($this);
        } elseif (is_array($name)) {
            $args = $name;
            $name = get_class($this);
        }
        return new Enlight_Event_EventArgs($args);
    }

    /**
     * Tests set up method
     */
    public function setUp()
    {
        parent::setUp();

        Shopware()->Container()->load('plugins');
    }
}
