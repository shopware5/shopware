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

use Enlight_Controller_Request_RequestHttp;
use Shopware\Tests\Functional\Components\CheckoutTest;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class CalculationOnLogoutTest extends CheckoutTest
{
    use DatabaseTransactionBehaviour;

    public $clearBasketOnReset = false;

    public function setUp(): void
    {
        parent::setUp();
        Shopware()->Session()->offsetSet('sessionId', null);
    }

    public function testLogout(): void
    {
        Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());

        $this->setConfig('clearBasketAfterLogout', false);

        $productNumber = $this->createArticle(100, 19.00);
        Shopware()->Modules()->Basket()->sAddArticle($productNumber);
        $this->visitCart();

        $currentAmount = Shopware()->Session()->get('sBasketAmount');

        $this->loginFrontendUser('H');

        Shopware()->Modules()->Admin()->logout();

        static::assertEquals($currentAmount, Shopware()->Session()->get('sBasketAmount'));
    }
}
