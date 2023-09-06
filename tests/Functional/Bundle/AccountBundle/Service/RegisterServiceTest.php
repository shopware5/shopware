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

namespace Shopware\Tests\Functional\Bundle\AccountBundle\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\ORMException;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class RegisterServiceTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use ContainerTrait;

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
     * Set up fixtures
     */
    public static function setUpBeforeClass(): void
    {
        self::$registerService = Shopware()->Container()->get(RegisterServiceInterface::class);
        self::$modelManager = Shopware()->Container()->get(ModelManager::class);
        self::$connection = Shopware()->Container()->get(Connection::class);
        self::$contextService = Shopware()->Container()->get(ContextServiceInterface::class);
    }

    /**
     * Clean up created entities and database entries
     */
    public static function tearDownAfterClass(): void
    {
        self::$modelManager->clear();
        Shopware()->Container()->reset('router');
    }

    public function testRegisterWithEmptyData(): void
    {
        $this->expectException(ORMException::class);
        $this->expectExceptionMessage('The identifier id is missing for a query of Shopware\Models\Shop\Shop');
        $shop = new Shop();
        $customer = new Customer();
        $billing = new Address();

        self::$registerService->register($shop, $customer, $billing);
    }

    public function testRegisterWithEmptyShop(): void
    {
        $this->expectException(ORMException::class);
        $this->expectExceptionMessage('The identifier id is missing for a query of Shopware\Models\Shop\Shop');
        $shop = new Shop();

        $customer = new Customer();
        $customer->fromArray($this->getCustomerDemoData());

        $billing = new Address();
        $billing->fromArray($this->getBillingDemoData());

        self::$registerService->register($shop, $customer, $billing);
    }

    public function testRegisterWithEmptyCustomer(): void
    {
        $this->expectException(ValidationException::class);
        $shop = $this->getShop();

        $customer = new Customer();

        $billing = new Address();
        $billing->fromArray($this->getBillingDemoData());

        self::$registerService->register($shop, $customer, $billing);
    }

    public function testRegisterWithEmptyAddress(): void
    {
        $this->expectException(ValidationException::class);
        $shop = $this->getShop();

        $customer = new Customer();
        $customer->fromArray($this->getCustomerDemoData());

        $billing = new Address();

        self::$registerService->register($shop, $customer, $billing);
    }

    public function testRegister(): int
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

        $this->assertCustomer($demoData, $customer);

        // assert data sync
        $this->assertAddress($billingDemoData, $customer);
        $this->assertAddress($billingDemoData, $customer, true);

        return $customer->getId();
    }

    public function testRegisterWithoutSalutation(): void
    {
        $demoData = $this->getCustomerDemoData();
        $config = $this->getContainer()->get('config');

        $config->offsetSet('shopSalutationRequired', false);
        unset($demoData['salutation']);
        $billingDemoData = $this->getBillingDemoData();

        $shop = $this->getShop();

        $customer = new Customer();
        $customer->fromArray($demoData);

        $billing = new Address();
        $billing->fromArray($billingDemoData);

        self::$registerService->register($shop, $customer, $billing);

        static::assertGreaterThan(0, $customer->getId());

        self::$modelManager->refresh($customer);

        $demoData['salutation'] = 'not_defined';

        $this->assertCustomer($demoData, $customer);

        // assert data sync
        $this->assertAddress($billingDemoData, $customer);
        $this->assertAddress($billingDemoData, $customer, true);

        $config->offsetSet('shopSalutationRequired', true);
    }

    public function testRegisterWithDifferentShipping(): void
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

        $this->assertCustomer($demoData, $customer);

        // assert data sync
        $this->assertAddress($billingDemoData, $customer);
        $this->assertAddress($shippingDemoData, $customer, true);
    }

    public function testRegisterWithExistingEmail(): void
    {
        $this->testRegister();

        $this->expectException(ValidationException::class);
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
     * @return array<string, string>
     */
    private function getCustomerDemoData(bool $randomEmail = false): array
    {
        $emailPrefix = $randomEmail ? uniqid((string) rand()) : '';

        return [
            'salutation' => 'mr',
            'firstname' => 'Albert',
            'lastname' => 'McTaggart',
            'email' => $emailPrefix . 'albert.mctaggart@shopware.test',
            'password' => uniqid((string) rand()),
        ];
    }

    /**
     * @return array<string, string|Country|State>
     */
    private function getBillingDemoData(): array
    {
        $country = $this->createCountry();

        return [
            'salutation' => 'mr',
            'firstname' => 'Sherman',
            'lastname' => 'Horton',
            'street' => '1117 Washington Street',
            'zipcode' => '78372',
            'city' => 'Orange Grove',
            'country' => $country,
            'state' => $this->createState($country),
        ];
    }

    /**
     * @return array<string, string|Country>
     */
    private function getShippingDemoData(): array
    {
        return [
            'salutation' => 'mr',
            'firstname' => 'Nathaniel',
            'lastname' => 'Fajardo',
            'street' => '3844 Euclid Avenue',
            'zipcode' => '93101',
            'city' => 'Santa Barbara',
            'country' => $this->createCountry(),
        ];
    }

    private function createCountry(): Country
    {
        $country = new Country();

        $country->setName('ShopwareLand ' . uniqid((string) rand()));
        $country->setActive(true);
        $country->setDisplayStateInRegistration(1);
        $country->setForceStateInRegistration(0);

        self::$modelManager->persist($country);
        self::$modelManager->flush($country);

        return self::$modelManager->merge($country);
    }

    private function getShop(): Shop
    {
        return self::$contextService->createShopContext(1)->getShop();
    }

    private function createState(Country $country): State
    {
        $state = new State();

        $state->setName('Shopware State ' . uniqid((string) rand()));
        $state->setActive(1);
        $state->setCountry($country);
        $state->setShortCode(uniqid((string) rand()));

        self::$modelManager->persist($state);
        self::$modelManager->flush($state);

        return self::$modelManager->merge($state);
    }

    private function assertCustomer(array $demoData, Customer $customer): void
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

    private function assertAddress(array $demoData, Customer $customer, bool $shipping = false): void
    {
        $legacyAddress = $shipping ? $customer->getDefaultShippingAddress() : $customer->getDefaultBillingAddress();
        $address = $shipping ? $customer->getDefaultShippingAddress() : $customer->getDefaultBillingAddress();
        static::assertInstanceOf(Address::class, $legacyAddress);
        static::assertInstanceOf(Address::class, $address);

        static::assertEquals($demoData['firstname'], $legacyAddress->getFirstname());
        static::assertEquals($demoData['firstname'], $address->getFirstname());

        static::assertEquals($demoData['lastname'], $legacyAddress->getLastname());
        static::assertEquals($demoData['lastname'], $address->getLastname());

        static::assertEquals($demoData['country']->getId(), $legacyAddress->getCountry()->getId());
        static::assertEquals($demoData['country']->getId(), $address->getCountry()->getId());

        if (!empty($demoData['state'])) {
            static::assertEquals($demoData['state']->getId(), $legacyAddress->getState()->getId());
            static::assertEquals($demoData['state']->getId(), $address->getState()->getId());
        }
    }
}
