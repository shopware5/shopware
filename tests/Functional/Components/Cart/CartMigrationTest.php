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

use Doctrine\DBAL\Connection;
use Enlight_Controller_Request_RequestTestCase;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware\Tests\Functional\Traits\FixtureBehaviour;

class CartMigrationTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;
    use FixtureBehaviour;

    public function testMigrateOnLoginWithEmptyCart(): void
    {
        self::executeFixture(__DIR__ . '/fixture/cart_migration_1.sql');

        $this->setFrontRequest();
        $this->getContainer()->get('modules')->Basket()->sRefreshBasket();
        static::assertEquals(0, $this->getContainer()->get('session')->get('sBasketAmount'));

        $this->loginCustomer();

        static::assertGreaterThan(0, $this->getContainer()->get('session')->get('sBasketAmount'));
    }

    public function testMigrateOnLoginWithFilledCart(): void
    {
        self::executeFixture(__DIR__ . '/fixture/cart_migration_1.sql');

        $this->setFrontRequest();
        $this->getContainer()->get('modules')->Basket()->sAddArticle('SW10001');
        $this->getContainer()->get('modules')->Basket()->sRefreshBasket();

        $currentBasketAmount = $this->getContainer()->get('session')->get('sBasketAmount');

        $this->loginCustomer();

        static::assertEquals($currentBasketAmount, $this->getContainer()->get('session')->get('sBasketAmount'));
    }

    private function setFrontRequest(): void
    {
        $this->getContainer()->get('front')->setRequest(new Enlight_Controller_Request_RequestTestCase());
    }

    private function loginCustomer(): void
    {
        $customer = $this->getContainer()->get(Connection::class)->fetchAssociative(
            'SELECT `email`, `password`, `language` FROM s_user WHERE `id` = 1'
        );
        static::assertIsArray($customer);

        $shop = $this->getContainer()->get(ModelManager::class)->getRepository(Shop::class)->getActiveById($customer['language']);
        static::assertInstanceOf(Shop::class, $shop);
        $this->getContainer()->get(ShopRegistrationServiceInterface::class)->registerResources($shop);

        $this->getContainer()->get('front')->ensureRequest()->setPost([
            'email' => $customer['email'],
            'passwordMD5' => $customer['password'],
        ]);
        $this->getContainer()->get('session')->set('Admin', true);
        static::assertIsArray($this->getContainer()->get('modules')->Admin()->sLogin(true));
    }
}
