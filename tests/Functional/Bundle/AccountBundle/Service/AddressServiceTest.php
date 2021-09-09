<?php

declare(strict_types=1);
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
use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Api\Exception\ValidationException;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;

class AddressServiceTest extends \Enlight_Components_Test_TestCase
{
    /**
     * @var AddressServiceInterface
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
     * @var array<class-string, int[]>
     */
    protected static array $_cleanup = [];

    /**
     * Set up fixtures
     */
    public static function setUpBeforeClass(): void
    {
        self::$addressService = Shopware()->Container()->get(AddressServiceInterface::class);
        self::$modelManager = Shopware()->Container()->get(ModelManager::class);
        self::$connection = Shopware()->Container()->get(Connection::class);
        self::$contextService = Shopware()->Container()->get(ContextServiceInterface::class);
        self::$registerService = Shopware()->Container()->get(RegisterServiceInterface::class);

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
                $model = self::$modelManager->find($entityName, $id);
                static::assertNotNull($model);
                self::$modelManager->remove($model);
            }
        }

        self::$modelManager->flush();
        self::$modelManager->clear();
    }

    public function testCreateWithEmptyData(): void
    {
        $this->expectException(ValidationException::class);
        $address = new Address();
        $customer = new Customer();

        self::$addressService->create($address, $customer);
    }

    public function testCreateWithEmptyCustomer(): void
    {
        $this->expectException('InvalidArgumentException');
        $address = new Address();
        $address->setSalutation('mr');
        $address->setFirstname('Lars');
        $address->setLastname('Larsson');
        $address->setStreet('Mayerstreet 22');
        $address->setZipcode('4498');
        $address->setCity('Oslo');
        $address->setCountry($this->createCountry());

        $customer = new Customer();

        self::$addressService->create($address, $customer);
    }

    public function testCreateWithEmptyAddress(): void
    {
        $this->expectException(ValidationException::class);
        $address = new Address();
        $customer = $this->createCustomer();

        self::$addressService->create($address, $customer);
    }

    public function testCreateAddress(): int
    {
        $addressData = [
            'salutation' => 'mr',
            'firstname' => 'Lars',
            'lastname' => 'Larsson',
            'street' => 'Mayerstreet 22',
            'zipcode' => '4498',
            'city' => 'Oslo',
            'country' => $this->createCountry(),
        ];

        $address = new Address();
        $address->fromArray($addressData);

        $customer = $this->createCustomer();

        self::$addressService->create($address, $customer);

        static::assertInstanceOf(Address::class, $address);
        static::assertNotNull($address->getId());

        foreach ($addressData as $key => $value) {
            $getter = 'get' . ucfirst($key);
            static::assertEquals($value, $address->$getter());
        }

        return $address->getId();
    }

    /**
     * @depends testCreateAddress
     */
    public function testSetDefaultBilling(int $addressId): int
    {
        $address = self::$modelManager->find(Address::class, $addressId);

        self::$addressService->setDefaultBillingAddress($address);

        $billing = $address->getCustomer()->getDefaultBillingAddress();

        static::assertEquals($address->getId(), $billing->getId());
        static::assertEquals($address->getFirstname(), $billing->getFirstname());
        static::assertEquals($address->getLastname(), $billing->getLastname());

        return $addressId;
    }

    /**
     * @depends testSetDefaultBilling
     */
    public function testUpdateBilling(int $addressId): int
    {
        $address = self::$modelManager->find(Address::class, $addressId);

        $address->setFirstname('Zara');
        $address->setCity('Kopenhagen');

        self::$addressService->update($address);

        $this->testSetDefaultBilling($addressId);

        return $addressId;
    }

    /**
     * @depends testCreateAddress
     */
    public function testSetDefaultShipping(int $addressId): int
    {
        $address = self::$modelManager->find(Address::class, $addressId);

        self::$addressService->setDefaultShippingAddress($address);

        $shipping = $address->getCustomer()->getDefaultShippingAddress();

        static::assertEquals($address->getId(), $shipping->getId());
        static::assertEquals($address->getFirstname(), $shipping->getFirstname());
        static::assertEquals($address->getLastname(), $shipping->getLastname());

        return $addressId;
    }

    /**
     * @depends testSetDefaultBilling
     */
    public function testDeleteDefaultAddressShouldFail(int $addressId): void
    {
        $this->expectException('RuntimeException');
        $this->expectExceptionMessage('The address is defined as default billing or shipping address and cannot be removed.');
        $address = self::$modelManager->find(Address::class, $addressId);

        self::$addressService->delete($address);
    }

    /**
     * @depends testSetDefaultBilling
     * @depends testDeleteDefaultAddressShouldFail
     */
    public function testDeleteNonDefaultAddress(int $addressId): void
    {
        $address = self::$modelManager->find(Address::class, $addressId);

        $unusedAddressId = self::$connection->executeQuery('SELECT id FROM s_user_addresses WHERE user_id = ? AND id != ?', [$address->getCustomer()->getId(), $address->getId()])->fetch(\PDO::FETCH_COLUMN);
        static::assertGreaterThan(0, $unusedAddressId, 'No unused address found.');

        $unusedAddress = self::$modelManager->find(Address::class, $unusedAddressId);
        static::assertNotNull($unusedAddress, 'Unused address entity (' . $unusedAddressId . ') not found.');

        self::$addressService->setDefaultBillingAddress($unusedAddress);
        self::$addressService->setDefaultShippingAddress($unusedAddress);

        self::$addressService->delete($address);

        static::assertNull($address->getId());
        static::assertNotNull($unusedAddress->getId());
    }

    private function createCountry(): Country
    {
        $country = new Country();

        $country->setName('ShopwareLand' . uniqid((string) rand(1, 999)));
        $country->setActive(true);
        $country->setDisplayStateInRegistration(0);
        $country->setForceStateInRegistration(0);

        self::$modelManager->persist($country);
        self::$modelManager->flush($country);

        self::$_cleanup[Country::class][] = $country->getId();

        return self::$modelManager->merge($country);
    }

    private function createCustomer(): Customer
    {
        $customer = new Customer();

        $customer->setEmail(uniqid((string) rand()) . 'test@foo.bar');
        $customer->setActive(true);
        $customer->setLastLogin(date('Y-m-d', strtotime('-8 days')));
        $customer->setPassword(uniqid((string) rand()) . uniqid((string) rand()));

        $customer->setSalutation('mr');
        $customer->setFirstname('Max');
        $customer->setLastname('Mustermann');

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
}
