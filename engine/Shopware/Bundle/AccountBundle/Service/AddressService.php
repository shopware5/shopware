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

namespace Shopware\Bundle\AccountBundle\Service;

use Shopware\Bundle\AccountBundle\Service\Validator\AddressValidatorInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;

class AddressService implements AddressServiceInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var AddressValidatorInterface
     */
    private $validator;

    public function __construct(ModelManager $modelManager, AddressValidatorInterface $validator)
    {
        $this->modelManager = $modelManager;
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Address $address, Customer $customer)
    {
        $address->setCustomer($customer);

        $this->validator->validate($address);
        $this->modelManager->persist($address);

        if (!$customer->getDefaultBillingAddress()) {
            $customer->setDefaultBillingAddress($address);
        }

        if (!$customer->getDefaultShippingAddress()) {
            $customer->setDefaultShippingAddress($address);
        }

        $this->modelManager->flush([$address, $customer]);

        $this->modelManager->refresh($address);
        $this->modelManager->refresh($customer);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Address $address)
    {
        $this->validator->validate($address);
        $this->modelManager->flush();
        $this->modelManager->refresh($address);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Address $address)
    {
        $preventDeletionOf = [
            $address->getCustomer()->getDefaultShippingAddress()->getId(),
            $address->getCustomer()->getDefaultBillingAddress()->getId(),
        ];

        if (in_array($address->getId(), $preventDeletionOf)) {
            throw new \RuntimeException('The address is defined as default billing or shipping address and cannot be removed.');
        }

        $this->modelManager->remove($address);
        $this->modelManager->flush($address);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultBillingAddress(Address $address)
    {
        $customer = $address->getCustomer();
        $customer->setDefaultBillingAddress($address);

        $this->update($address);

        $this->modelManager->flush([$customer]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultShippingAddress(Address $address)
    {
        $customer = $address->getCustomer();

        $customer->setDefaultShippingAddress($address);

        $this->update($address);

        $this->modelManager->flush([$customer]);
    }
}
