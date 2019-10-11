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

namespace Shopware\Tests\Functional\Bundle\AccountBundle\Service;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;

class RegisterServiceTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var RegisterServiceInterface
     */
    protected static $registerService;

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
     * @var array
     */
    protected static $_cleanup = [];

    /**
     * Set up fixtures
     */
    public static function setUpBeforeClass(): void
    {
        self::$registerService = Shopware()->Container()->get('shopware_account.register_service');
        self::$modelManager = Shopware()->Container()->get('models');
        self::$connection = Shopware()->Container()->get('dbal_connection');
        self::$contextService = Shopware()->Container()->get('shopware_storefront.context_service');

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

        Shopware()->Container()->reset('router');
    }

    public function testRegisterWithEmptyData()
    {
        $this->expectException('Doctrine\ORM\ORMException');
        $this->expectExceptionMessage('The identifier id is missing for a query of Shopware\Models\Shop\Shop');
        $shop = new Shop();
        $customer = new Customer();
        $billing = new Address();

        self::$registerService->register($shop, $customer, $billing);
    }

    public function testRegisterWithEmptyShop()
    {
        $this->expectException('Doctrine\ORM\ORMException');
        $this->expectExceptionMessage('The identifier id is missing for a query of Shopware\Models\Shop\Shop');
        $shop = new Shop();

        $customer = new Customer();
        $customer->fromArray($this->getCustomerDemoData());

        $billing = new Address();
        $billing->fromArray($this->getBillingDemoData());

        self::$registerService->register($shop, $customer, $billing);
    }

    public function testRegisterWithEmptyCustomer()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
        $shop = $this->getShop();

        $customer = new Customer();

        $billing = new Address();
        $billing->fromArray($this->getBillingDemoData());

        self::$registerService->register($shop, $customer, $billing);
    }

    public function testRegisterWithEmptyAddress()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
        $shop = $this->getShop();

        $customer = new Customer();
        $customer->fromArray($this->getCustomerDemoData());

        $billing = new Address();

        self::$registerService->register($shop, $customer, $billing);
    }

    public function testRegister()
    {
        $demoData = $this->getCustomerDemoData();
        $billingDemoData = $this->getBillingDemoData();

        $shop = $this->getShop();

        $customer = new Customer();
        $customer->fromArray($demoData);

        $billing = new Address();
        $billing->fromArray($billingDemoData);

        self::$registerService->register($shop, $customer, $billing);

        static::assertGreaterThan(0, $customer->getId());

        self::$modelManager->refresh($customer);

        self::$_cleanup[Customer::class][] = $customer->getId();
        self::$_cleanup[Address::class][] = $billing->getId();

        $this->assertCustomer($demoData, $customer);

        // assert data sync
        $this->assertAddress($billingDemoData, $customer);
        $this->assertAddress($billingDemoData, $customer, true);

        return $customer->getId();
    }

    public function testRegisterWithDifferentShipping()
    {
        $demoData = $this->getCustomerDemoData(true);
        $billingDemoData = $this->getBillingDemoData();
        $shippingDemoData = $this->getShippingDemoData();

        $shop = $this->getShop();

        $customer = new Customer();
        $customer->fromArray($demoData);

        $billing = new Address();
        $billing->fromArray($billingDemoData);

        $shipping = new Address();
        $shipping->fromArray($shippingDemoData);

        self::$registerService->register($shop, $customer, $billing, $shipping);

        static::assertGreaterThan(0, $customer->getId());

        self::$modelManager->refresh($customer);

        self::$_cleanup[Customer::class][] = $customer->getId();
        self::$_cleanup[Address::class][] = $billing->getId();
        self::$_cleanup[Address::class][] = $shipping->getId();

        $this->assertCustomer($demoData, $customer);

        // assert data sync
        $this->assertAddress($billingDemoData, $customer);
        $this->assertAddress($shippingDemoData, $customer, true);
    }

    /**
     * @depends testRegister
     */
    public function testRegisterWithExistingEmail()
    {
        $this->expectException('Shopware\Components\Api\Exception\ValidationException');
        $demoData = $this->getCustomerDemoData();

        $shop = $this->getShop();

        $customer = new Customer();
        $customer->fromArray($demoData);

        $billing = new Address();
        $billing->fromArray($this->getBillingDemoData());

        self::$registerService->register($shop, $customer, $billing);
    }

    /**
     * Helper method for creating a valid customer
     *
     * @param bool $randomEmail
     *
     * @return array
     */
    private function getCustomerDemoData($randomEmail = false)
    {
        $emailPrefix = $randomEmail ? uniqid(rand()) : '';

        $data = [
            'salutation' => 'mr',
            'firstname' => 'Albert',
            'lastname' => 'McTaggart',
            'email' => $emailPrefix . 'albert.mctaggart@shopware.test',
            'password' => uniqid(rand()),
        ];

        return $data;
    }

    private function getBillingDemoData()
    {
        $country = $this->createCountry();

        $data = [
            'salutation' => 'mr',
            'firstname' => 'Sherman',
            'lastname' => 'Horton',
            'street' => '1117 Washington Street',
            'zipcode' => '78372',
            'city' => 'Orange Grove',
            'country' => $country,
            'state' => $this->createState($country),
        ];

        return $data;
    }

    private function getShippingDemoData()
    {
        $data = [
            'salutation' => 'mr',
            'firstname' => 'Nathaniel',
            'lastname' => 'Fajardo',
            'street' => '3844 Euclid Avenue',
            'zipcode' => '93101',
            'city' => 'Santa Barbara',
            'country' => $this->createCountry(),
        ];

        return $data;
    }

    /**
     * @return Country
     */
    private function createCountry()
    {
        $country = new Country();

        $country->setName('ShopwareLand ' . uniqid(rand()));
        $country->setActive(true);
        $country->setDisplayStateInRegistration(1);
        $country->setForceStateInRegistration(0);

        self::$modelManager->persist($country);
        self::$modelManager->flush($country);

        self::$_cleanup[Country::class][] = $country->getId();

        return self::$modelManager->merge($country);
    }

    /**
     * @return Shop
     */
    private function getShop()
    {
        return self::$contextService->createShopContext(1)->getShop();
    }

    /**
     * @return State
     */
    private function createState(Country $country)
    {
        $state = new State();

        $state->setName('Shopware State ' . uniqid(rand()));
        $state->setActive(1);
        $state->setCountry($country);
        $state->setShortCode(uniqid(rand()));

        self::$modelManager->persist($state);
        self::$modelManager->flush($state);

        self::$_cleanup[State::class][] = $state->getId();

        return self::$modelManager->merge($state);
    }

    private function assertCustomer(array $demoData, Customer $customer)
    {
        static::assertEquals($demoData['salutation'], $customer->getSalutation());
        static::assertEquals($demoData['firstname'], $customer->getFirstname());
        static::assertEquals($demoData['lastname'], $customer->getLastname());
        static::assertEquals($demoData['email'], $customer->getEmail());
        static::assertEquals('EK', $customer->getGroup()->getKey());
        static::assertNotEmpty($customer->getPassword());

        static::assertNotNull($customer->getDefaultBillingAddress());
        static::assertNotNull($customer->getDefaultShippingAddress());
    }

    /**
     * @param bool $shipping
     */
    private function assertAddress(array $demoData, Customer $customer, $shipping = false)
    {
        $legacyAddress = $shipping ? $customer->getDefaultShippingAddress() : $customer->getDefaultBillingAddress();
        $address = $shipping ? $customer->getDefaultShippingAddress() : $customer->getDefaultBillingAddress();

        static::assertEquals($demoData['firstname'], $legacyAddress->getFirstName());
        static::assertEquals($demoData['firstname'], $address->getFirstname());

        static::assertEquals($demoData['lastname'], $legacyAddress->getLastName());
        static::assertEquals($demoData['lastname'], $address->getLastname());

        static::assertEquals($demoData['country']->getId(), $legacyAddress->getCountry()->getId());
        static::assertEquals($demoData['country']->getId(), $address->getCountry()->getId());

        if (!empty($demoData['state'])) {
            static::assertEquals($demoData['state']->getId(), $legacyAddress->getState()->getId());
            static::assertEquals($demoData['state']->getId(), $address->getState()->getId());
        }
    }
}
