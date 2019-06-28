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

namespace Shopware\Tests\Functional\Components\Cart;

use Enlight_Components_Test_Controller_TestCase;

class NetRoundingTest extends Enlight_Components_Test_Controller_TestCase
{
    public function setUp(): void
    {
        Shopware()->Db()->beginTransaction();
        array_map([Shopware()->Db(), 'exec'], [
            'UPDATE s_articles SET taxID = 1 WHERE id = 272',
            'UPDATE s_articles_prices SET price = 7.56 WHERE articleID = 272',
            'UPDATE s_core_customergroups SET tax = 0, taxinput = 0, minimumorder = 0, minimumordersurcharge = 0',
            'UPDATE s_core_customergroups SET tax = 0, taxinput = 0, minimumorder = 0, minimumordersurcharge = 0',
            'DELETE FROM s_premium_dispatch WHERE name NOT LIKE "%Standard%"',
            'UPDATE s_premium_shippingcosts SET value = 5.20',
        ]);

        Shopware()->Modules()->Basket()->sAddArticle('SW10239', 2);
        Shopware()->Container()->reset('shopware.cart.net_rounding');
    }

    public function tearDown(): void
    {
        Shopware()->Db()->rollBack();
    }

    public function testOldRounding(): void
    {
        $this->setConfig('roundNetAfterTax', false);
        $this->dispatch('/checkout/cart');

        static::assertEquals(23.189999999999998, $this->View()->getAssign('sBasket')['AmountWithTaxNumeric']);
    }

    public function testNewRounding(): void
    {
        $this->setConfig('roundNetAfterTax', true);
        $this->dispatch('/checkout/cart');

        static::assertEquals(23.2, $this->View()->getAssign('sBasket')['AmountWithTaxNumeric']);
    }
}
