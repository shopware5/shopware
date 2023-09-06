<?php
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

namespace Shopware\Tests\Functional\Controllers\Widgets;

use Enlight_Components_Test_Controller_TestCase;

class IndexTest extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * @ticket SW-8127
     */
    public function testShopMenu(): void
    {
        $this->dispatch('/Widgets/Index/shopMenu');
        static::assertEquals(200, $this->Response()->getHttpResponseCode());

        Shopware()->Models()->flush();
    }
}
