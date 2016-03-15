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

use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\AccountBundle\Service\AddressServiceInterface;
use Shopware\Bundle\AccountBundle\Form\Account\AddressFormType;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Billing;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Customer\Shipping;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

class AddressService implements AddressServiceInterface
{
    /**
     * @var ModelManager
     */
    private $modelManager;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * AddressService constructor.
     * @param ModelManager $modelManager
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(ModelManager $modelManager, FormFactoryInterface $formFactory)
    {
        $this->modelManager = $modelManager;
        $this->formFactory = $formFactory;
    }

    /**
     * @inheritdoc
     */
    public function create(Address $address, Customer $customer)
    {
        $address->setCustomer($customer);

        $this->validate($address);
        $this->modelManager->persist($address);

        if (!$customer->getDefaultBillingAddress()) {
            $customer->setDefaultBillingAddress($address);
        }

        if (!$customer->getDefaultShippingAddress()) {
            $customer->setDefaultShippingAddress($address);
        }

        $this->modelManager->flush([$address, $customer]);

        return $address;
    }

    /**
     * @inheritdoc
     */
    public function update(Address $address)
    {
        $this->validate($address);

        if ($address->getCustomer()->getDefaultBillingAddress()->getId() == $address->getId()) {
            $address->getCustomer()->getBilling()->fromAddress($address);
        }

        if ($address->getCustomer()->getDefaultShippingAddress()->getId() == $address->getId()) {
            $address->getCustomer()->getShipping()->fromAddress($address);
        }

        $this->modelManager->flush();

        return $address;
    }

    /**
     * @param Address $address
     * @throws ValidatorException
     */
    private function validate(Address $address)
    {
        $form = $this->formFactory->create(AddressFormType::class, $address, ['allow_extra_fields' => true]);
        $form->submit(null, false);

        if (!$form->isValid()) {
            throw new ValidatorException($form->getErrors(true, false));
        }
    }

    /**
     * @param Address $address
     * @param array $data
     * @return \Shopware\Models\Attribute\CustomerAddress
     */
    public function saveAttribute(Address $address, array $data = [])
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

    /**
     * @inheritdoc
     */
    public function isDuplicate(array $data, $customerId)
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
     * @inheritdoc
     */
    public function delete(Address $address)
    {
        $this->modelManager->remove($address);
        $this->modelManager->flush($address);
    }

    /**
     * @inheritdoc
     */
    public function setDefaultBillingAddress(Address $address)
    {
        $customer = $address->getCustomer();
        $customer->setDefaultBillingAddress($address);

        $billing = $customer->getBilling();
        $billing->fromAddress($address);

        $this->modelManager->flush([$customer, $billing]);
    }

    /**
     * @inheritdoc
     */
    public function setDefaultShippingAddress(Address $address)
    {
        $customer = $address->getCustomer();
        $customer->setDefaultShippingAddress($address);

        $shipping = $customer->getShipping();
        if (!$shipping) {
            $shipping = new Shipping();
            $shipping->setCustomer($customer);
            $this->modelManager->persist($shipping);
        }
        $shipping->fromAddress($address);

        $this->modelManager->flush([$customer, $shipping]);
    }
}
