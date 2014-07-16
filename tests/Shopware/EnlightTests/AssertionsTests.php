<?php

/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * SW-7789
 *
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class AssertionsTests extends Enlight_Components_Test_TestCase
{
    public function testAssertArrayCount()
    {
        try {
            $this->assertArrayCount(1, array());
            self::fail("assertArrayCount should fail");
        }
        catch (PHPUnit_Framework_ExpectationFailedException $e) { /** All OK */ }
        try {
            $this->assertArrayCount(1, array(1, '2', true));
            self::fail("assertArrayCount should fail");
        }
        catch (PHPUnit_Framework_ExpectationFailedException $e) { /** All OK */ }

        $this->assertArrayCount(0, array());
        $this->assertArrayCount(1, array(1));
    }

    public function testAssertArrayNotCount()
    {
        $this->assertArrayNotCount(1, array());
        $this->assertArrayNotCount(1, array(1, '2', true));

        try {
            $this->assertArrayNotCount(0, array());
            self::fail("assertArrayCount should fail");
        }
        catch (PHPUnit_Framework_ExpectationFailedException $e) { /** All OK */ }
        try {
            $this->assertArrayNotCount(1, array(1));
            self::fail("assertArrayCount should fail");
        }
        catch (PHPUnit_Framework_ExpectationFailedException $e) { /** All OK */ }
    }

    public function testAssertLinkExists()
    {
        $this->assertLinkExists('http://www.shopware.com');
        $this->assertLinkExists('http://www.bepado.de');

        try {
            $this->assertLinkExists('http://www.somebogusinternetadress.com');
            self::fail("assertLinkExists should fail");
        }
        catch (PHPUnit_Framework_ExpectationFailedException $e) { /** All OK */ }
        try {
            $this->assertLinkExists('invalid url');
            self::fail("assertLinkExists should fail");
        }
        catch (PHPUnit_Framework_ExpectationFailedException $e) { /** All OK */ }
    }

    public function testAssertLinkNotExists()
    {
        $this->assertLinkNotExists('http://www.somebogusinternetadress.com');
        $this->assertLinkNotExists('invalid url');

        try {
            $this->assertLinkNotExists('http://www.shopware.com');
            self::fail("assertLinkExists should fail");
        }
        catch (PHPUnit_Framework_ExpectationFailedException $e) { /** All OK */ }
        try {
            $this->assertLinkNotExists('http://www.bepado.de');
            self::fail("assertLinkExists should fail");
        }
        catch (PHPUnit_Framework_ExpectationFailedException $e) { /** All OK */ }
    }
}
