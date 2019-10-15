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

namespace Shopware\Tests\Functional\Models\Customer;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;

class CustomerTest extends TestCase
{
    /**
     * @var \Shopware\Bundle\AccountBundle\Service\AddressServiceInterface
     */
    protected static $addressService;

    /**
     * @var ModelManager
     */
    protected static $modelManager;

    /**
     * @var Connection
     */
    protected static $connection;

    /**
     * @var ContextServiceInterface
     */
    protected static $contextService;

    /**
     * @var RegisterServiceInterface
     */
    protected static $registerService;

    /**
     * @var array
     */
    protected static $_cleanup = [];

    /**
     * Set up fixtures
     */
    public static function setUpBeforeClass(): void
    {
        self::$addressService = Shopware()->Container()->get('shopware_account.address_service');
        self::$modelManager = Shopware()->Container()->get('models');
        self::$connection = Shopware()->Container()->get('dbal_connection');
        self::$contextService = Shopware()->Container()->get('shopware_storefront.context_service');
        self::$registerService = Shopware()->Container()->get('shopware_account.register_service');

        self::$modelManager->clear();
    }

    /**
     * Clean up created entities and database entries
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        foreach (self::$_cleanup as $entityName => $ids) {
            foreach ($ids as $id) {
                self::$modelManager->remove(self::$modelManager->find($entityName, $id));
            }
        }

        self::$modelManager->flush();
        self::$modelManager->clear();
    }

    public function testCustomerNormalEdit(): void
    {
        $customer = $this->createCustomer();

        $customer->setActive(false);
        $currentPasswordChange = $customer->getPasswordChangeDate()->format('Y-m-d H:i:s');

        self::$modelManager->persist($customer);
        self::$modelManager->flush($customer);

        static::assertSame($currentPasswordChange, $customer->getPasswordChangeDate()->format('Y-m-d H:i:s'));
    }

    public function testCustomerPasswordEdit(): void
    {
        $customer = $this->createCustomer();

        $customer->setRawPassword(uniqid('sw', true));
        $currentPasswordChange = $customer->getPasswordChangeDate()->format('Y-m-d H:i:s');

        self::$modelManager->persist($customer);
        self::$modelManager->flush($customer);

        static::assertNotSame($currentPasswordChange, $customer->getPasswordChangeDate()->format('Y-m-d H:i:s'));
    }

    private function createCustomer(): Customer
    {
        $customer = new Customer();

        $customer->setEmail(uniqid(mt_rand(), true) . 'test@foo.bar');
        $customer->setActive(true);
        $customer->setLastLogin(date('Y-m-d', strtotime('-8 days')));
        $customer->setPassword(uniqid(mt_rand(), true) . uniqid(mt_rand(), true));

        $customer->setSalutation('mr');
        $customer->setFirstname('Max');
        $customer->setLastname('Mustermann');

        // Set password change to past, otherwise the test will fail, cause the time is the same
        $closure = \Closure::bind(static function (Customer $class) {
            $class->passwordChangeDate = new \DateTime('2000-01-01 00:00:00');
        }, null, Customer::class);
        $closure($customer);

        $billing = $this->createBillingEntity();
        $shipping = $this->createShippingEntity();

        $shop = self::$contextService->getShopContext()->getShop();

        self::$registerService->register($shop, $customer, $billing, $shipping);

        self::$_cleanup[Customer::class][] = $customer->getId();

        return $customer;
    }

    private function createBillingEntity(): Address
    {
        $billing = new Address();

        $country = $this->createCountry();

        $billing->setSalutation('mr');
        $billing->setFirstname('Nathan');
        $billing->setLastname('Davis');
        $billing->setZipcode('92123');
        $billing->setCity('San Diego');
        $billing->setCountry($country);
        $billing->setStreet('4193 Pike Street');

        return $billing;
    }

    private function createShippingEntity(): Address
    {
        $shipping = new Address();

        $country = $this->createCountry();

        $shipping->setSalutation('mr');
        $shipping->setFirstname('Michael');
        $shipping->setLastname('Crosby');
        $shipping->setZipcode('36542');
        $shipping->setCity('Gulf Shores');
        $shipping->setCountry($country);
        $shipping->setStreet('4267 Lonely Oak Drive');

        return $shipping;
    }

    private function createCountry(): Country
    {
        $country = new Country();

        $country->setName('ShopwareLand' . uniqid(rand(1, 999)));
        $country->setActive(true);
        $country->setDisplayStateInRegistration(0);
        $country->setForceStateInRegistration(0);

        self::$modelManager->persist($country);
        self::$modelManager->flush($country);

        self::$_cleanup[Country::class][] = $country->getId();

        return self::$modelManager->merge($country);
    }
}
