<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Components\LegacyRequestWrapper;

use Enlight_Components_Test_Controller_TestCase;
use Enlight_Controller_Request_Request;

class GetWrapperTest extends Enlight_Components_Test_Controller_TestCase
{
    private static array $resources = [
        'Admin',
        'Articles',
        'Basket',
        'Categories',
        'Export',
        'Marketing',
        'Order',
        'RewriteTable',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->dispatch('/');
    }

    /**
     * Tests that setting a value inside any core class is equivalent to setting it in the
     * global $_GET
     */
    public function testSetQuery(): array
    {
        static::assertInstanceOf(Enlight_Controller_Request_Request::class, Shopware()->Front()->Request());
        $previousGetData = Shopware()->Front()->Request()->getQuery();

        foreach (self::$resources as $name) {
            Shopware()->Front()->Request()->setQuery($name, $name . 'Value');
        }

        $getData = Shopware()->Front()->Request()->getQuery();
        static::assertNotEquals($previousGetData, $getData);

        foreach (self::$resources as $name) {
            if (property_exists($name, 'sSYSTEM')) {
                static::assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_GET->toArray());
            }
        }

        return $getData;
    }

    /**
     * Tests that reseting GET data inside any core class is equivalent to resetting it in the
     * global $_GET
     *
     * @depends testSetQuery
     */
    public function testOverwriteAndClearQuery(array $getData): void
    {
        static::assertInstanceOf(Enlight_Controller_Request_Request::class, Shopware()->Front()->Request());
        static::assertNotEquals($getData, Shopware()->Front()->Request()->getQuery());

        foreach (self::$resources as $name) {
            if (property_exists($name, 'sSYSTEM')) {
                Shopware()->Front()->Request()->setQuery($getData);
                static::assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_GET->toArray());
                Shopware()->Modules()->getModule($name)->sSYSTEM->_GET = [];
                static::assertNotEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_GET->toArray());
            }
        }
    }

    /**
     * Tests that getting GET data inside any core class is equivalent to getting it from the
     * global $_GET
     *
     * @depends testSetQuery
     */
    public function testGetQuery(): void
    {
        static::assertInstanceOf(Enlight_Controller_Request_Request::class, Shopware()->Front()->Request());
        $previousGetData = Shopware()->Front()->Request()->getQuery();

        foreach (self::$resources as $name) {
            Shopware()->Modules()->getModule($name)->sSYSTEM->_GET[$name] = $name . 'Value';
        }

        $getData = Shopware()->Front()->Request()->getQuery();
        static::assertNotEquals($previousGetData, $getData);

        foreach (self::$resources as $name) {
            if (property_exists($name, 'sSYSTEM')) {
                static::assertEquals($getData, Shopware()->Modules()->getModule($name)->sSYSTEM->_GET->toArray());
            }
        }
    }
}
