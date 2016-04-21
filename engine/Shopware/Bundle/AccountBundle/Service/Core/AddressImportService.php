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

namespace Shopware\Bundle\AccountBundle\Service\Core;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\AccountBundle\Service\AddressImportServiceInterface;
use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;

class AddressImportService implements AddressImportServiceInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var AddressServiceInterface
     */
    private $addressService;

    /**
     * AddressService constructor.
     * @param Connection $connection
     * @param ModelManager $modelManager
     * @param AddressServiceInterface $addressService
     */
    public function __construct(Connection $connection, ModelManager $modelManager, AddressServiceInterface $addressService)
    {
        $this->connection = $connection;
        $this->modelManager = $modelManager;
        $this->addressService = $addressService;
    }

    /**
     * @inheritdoc
     */
    public function importCustomerBilling($customerId)
    {
        return $this->importLegacyAddress('s_user_billingaddress', $customerId);
    }

    /**
     * @inheritdoc
     */
    public function importOrderBilling($customerId)
    {
        return $this->importLegacyAddress('s_order_billingaddress', $customerId);
    }

    /**
     * @inheritdoc
     */
    public function importCustomerShipping($customerId)
    {
        return $this->importLegacyAddress('s_user_shippingaddress', $customerId);
    }

    /**
     * @inheritdoc
     */
    public function importOrderShipping($customerId)
    {
        return $this->importLegacyAddress('s_order_shippingaddress', $customerId);
    }

    /**
     * Create address book item based on legacy billing or shipping addresses
     *
     * @param string $table
     * @param int $customerId
     * @return Address
     * @throws \RuntimeException
     */
    private function importLegacyAddress($table, $customerId)
    {
        $builder = $this->connection->createQueryBuilder();
        $fields = strpos($table, 'shipping') ? $this->getShippingAddressFields() : $this->getBillingAddressFields();

        $data = $builder
            ->select($fields)
            ->from($table)
            ->where('userID = :customerId')
            ->setParameter('customerId', $customerId)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (empty($data)) {
            throw new \RuntimeException('No address to import found in '.$table.'.');
        }

        if ($this->isDuplicate($data, $customerId)) {
            throw new \RuntimeException('The address in '.$table.' seems to be a duplicate of an existing address.');
        }

        $this->modelManager->clear(Customer::class);

        /** @var Customer $customer */
        $customer = $this->modelManager->find(Customer::class, $customerId);

        $data['country'] = $this->modelManager->find(Country::class, $data['country']);
        if (!empty($data['state'])) {
            $data['state'] = $this->modelManager->find(State::class, $data['state']);
        } else {
            $data['state'] = null;
        }

        $address = new Address();
        $address->fromArray($data);
        $address = $this->addressService->create($address, $customer);
        $this->importLegacyAddressAttributes($table, $address);

        return $address;
    }

    /**
     * @return string[]
     */
    private function getBillingAddressFields()
    {
        return array_merge(
            $this->getShippingAddressFields(),
            [
                'ustid as vatId',
                'phone'
            ]
        );
    }

    /**
     * @return string[]
     */
    private function getShippingAddressFields()
    {
        return [
            'company',
            'department',
            'salutation',
            'title',
            'firstname',
            'lastname',
            'street',
            'zipcode',
            'city',
            'countryID as country',
            'stateID as state',
            'additional_address_line1',
            'additional_address_line2'
        ];
    }

    /**
     * @param string $table
     * @param Address $address
     */
    private function importLegacyAddressAttributes($table, Address $address)
    {
        $attributeTable = $table . '_attributes';
        $attributeForeignKey = strpos($table, 'shipping') ? 'shippingID' : 'billingID';

        $builder = $this->connection->createQueryBuilder();

        $data = $builder
            ->select('*')
            ->from($table, 'address')
            ->innerJoin('address', $attributeTable, 'attribute', 'address.id = attribute.' . $attributeForeignKey)
            ->where('address.userID = :addressId')
            ->setParameter('addressId', $address->getCustomer()->getId())
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);

        if (empty($data)) {
            return;
        }

        unset($data['id'], $data['address_id'], $data['shippingID'], $data['billingID']);
        $this->saveAttribute($address, $data);
    }


    /**
     * Searches all customer addresses for the given data
     *
     * @param array $data
     * @param int $customerId
     * @return bool
     */
    private function isDuplicate(array $data, $customerId)
    {
        $existing = $this->modelManager->getRepository(Address::class)->getListArray($customerId);

        foreach ($existing as $row) {
            $row['country'] = $row['country']['id'];
            $row['state'] = $row['state'] ? $row['state']['id'] : null;
            unset($row['id']);

            $diff = array_diff($row, $data);
            if (empty($diff)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Address $address
     * @param array $data
     * @return \Shopware\Models\Attribute\CustomerAddress
     */
    private function saveAttribute(Address $address, array $data = [])
    {
        $attribute = $address->getAttribute();
        if (!$attribute) {
            $attribute = new \Shopware\Models\Attribute\CustomerAddress();
            $attribute->setCustomerAddress($address);
            $this->modelManager->persist($attribute);
        }

        $attribute->fromArray($data);
        $this->modelManager->flush($attribute);

        return $attribute;
    }
}
