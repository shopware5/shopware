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
        'Order',
        'RewriteTable'
    );

    public function setUp()
    {
        parent::setUp();

        $this->dispatch('/');
    }

    /**
     * @covers GetWrapper::offsetSet()
     */
    public function testSet()
    {
        Shopware()->Modules()->System()->_GET->offsetSet('foo', 'bar');
        $this->assertEquals('bar', Shopware()->Front()->Request()->getQuery('foo'));

        Shopware()->Modules()->System()->_GET->offsetSet('foo', null);
        $this->assertNull(Shopware()->Front()->Request()->getQuery('bar'));

        Shopware()->Modules()->System()->_GET->offsetSet('foo', array());
        $this->assertEmpty(Shopware()->Front()->Request()->getQuery('bar'));
        $this->assertInternalType('array', Shopware()->Front()->Request()->getQuery('foo'));
    }

    /**
     * @covers GetWrapper::offsetSet()
     */
    public function testGet()
    {
        Shopware()->Front()->Request()->setQuery('foo', 'bar');
        $this->assertEquals('bar', Shopware()->Modules()->System()->_GET->offsetGet('foo'));

        Shopware()->Front()->Request()->setQuery('foo', null);
        $this->assertNull(Shopware()->Modules()->System()->_GET->offsetGet('bar'));

        Shopware()->Front()->Request()->setQuery('foo', array());
        $this->assertEmpty(Shopware()->Modules()->System()->_GET->offsetGet('bar'));
        $this->assertInternalType('array', Shopware()->Modules()->System()->_GET->offsetGet('foo'));
    }

    /**
     * @covers GetWrapper::offsetUnset()
     */
    public function testUnset()
    {
        Shopware()->Modules()->System()->_GET->offsetSet('foo', 'bar');
        $this->assertEquals('bar', Shopware()->Front()->Request()->getQuery('foo'));
        unset(Shopware()->Modules()->System()->_GET['foo']);
        $this->assertNull(Shopware()->Front()->Request()->getQuery('foo'));
    }

    /**
     * @covers GetWrapper::setAll()
     */
    public function testSetAll()
    {
        Shopware()->Modules()->System()->_GET->offsetSet('foo', 'bar');
        $this->assertEquals('bar', Shopware()->Front()->Request()->getQuery('foo'));

        Shopware()->Modules()->System()->_GET = array('foo' => 'too');
        $this->assertNull(Shopware()->Front()->Request()->getQuery('bar'));
        $this->assertEquals('too', Shopware()->Front()->Request()->getQuery('foo'));
    }

    /**
     * @covers GetWrapper::toArray()
     */
    public function testToArray()
    {
        Shopware()->Front()->Request()->setQuery('foo', 'bar');
        $this->assertEquals(array('foo' => 'bar'), Shopware()->Modules()->System()->_GET->toArray());
    }

    /**
     * Tests that setting a value inside any core class is equivalent to setting it in the
     * global $_GET
     *
     * @return mixed
     */
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
     * Tests that reseting GET data inside any core class is equivalent to resetting it in the
     * global $_GET
     *
     * @param $getData
     * @return mixed
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

    /**
     * Tests that getting GET data inside any core class is equivalent to getting it from the
     * global $_GET
     *
     * @depends testSetQuery
     */
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
