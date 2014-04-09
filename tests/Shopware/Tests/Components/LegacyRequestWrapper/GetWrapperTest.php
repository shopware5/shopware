<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 * @covers \Shopware\Components\LegacyRequestWrapper\GetWrapper
 */
class Shopware_Tests_Components_LegacyRequestWrapper_GetWrapperTest extends Enlight_Components_Test_Controller_TestCase
{
    private static $resources = array(
        'Admin',
        'Articles',
        'Basket',
        'Categories',
        'cms',
        'Configurator',
        'Core',
        'Export',
        'Marketing',
        'Newsletter',
        'Order',
        'RewriteTable'
    );

    public function setUp()
    {
        parent::setUp();

        $this->dispatch('/');
    }

    public function testSetQuery()
    {
        $previousGetData = Shopware()->Front()->Request()->getQuery();

        foreach (self::$resources as $name) {
            Shopware()->Front()->Request()->setQuery($name, $name.'Value');
        }

        $getData = Shopware()->Front()->Request()->getQuery();
        $this->assertNotEquals($previousGetData, $getData);

        foreach (self::$resources as $name) {
            $this->assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_GET->toArray());
        }

        return $getData;
    }

    /**
     * @depends testSetQuery
     */
    public function testOverwriteAndClearQuery($getData)
    {
        $this->assertNotEquals($getData, Shopware()->Front()->Request()->getQuery());

        foreach (self::$resources as $name) {
            Shopware()->Front()->Request()->setQuery($getData);
            $this->assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_GET->toArray());
            Shopware()->Modules()->getModule($name)->sSYSTEM->_GET = array();
            $this->assertNotEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_GET->toArray());
        }

        return $getData;
    }

    public function testGetQuery()
    {
        $previousGetData = Shopware()->Front()->Request()->getQuery();

        foreach (self::$resources as $name) {
            Shopware()->Modules()->getModule($name)->sSYSTEM->_GET[$name] = $name.'Value';
        }

        $getData = Shopware()->Front()->Request()->getQuery();
        $this->assertNotEquals($previousGetData, $getData);

        foreach (self::$resources as $name) {
            $this->assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_GET->toArray());
        }
    }
}
