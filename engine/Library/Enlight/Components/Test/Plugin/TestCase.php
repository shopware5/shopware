<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Basic class for plugin test cases.
 *
 * The Enlight_Components_Test_Plugin_TestCase extends the Enlight_Components_Test_Controller_TestCase
 * to grant an easy way to create Enlight event arguments. This class represents the basic for plugin tests.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
abstract class Enlight_Components_Test_Plugin_TestCase extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * Tests set up method
     */
    public function setUp()
    {
        parent::setUp();

        Shopware()->Container()->load('Plugins');
    }

    /**
     * Creates a new instance of Enlight_Event_EventArgs by the passed parameters.
     * If the name didn't passed, the class name will be used.
     * If the name passed as array, the name will be used as arguments.
     *
     * @param string|array $name
     * @param array        $args
     *
     * @return Enlight_Event_EventArgs
     */
    public function createEventArgs($name = null, $args = [])
    {
        if (is_array($name)) {
            $args = $name;
        }

        return new Enlight_Event_EventArgs($args);
    }
}
