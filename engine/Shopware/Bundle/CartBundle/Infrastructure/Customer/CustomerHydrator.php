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

namespace Shopware\Bundle\CartBundle\Infrastructure\Customer;

use Shopware\Bundle\CartBundle\Domain\Customer\Customer;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\AttributeHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CustomerGroupHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\Hydrator;

class CustomerHydrator extends Hydrator
{
    /**
     * @var AttributeHydrator
     */
    private $attributeHydrator;

    /**
     * @var CustomerGroupHydrator
     */
    private $customerGroupHydrator;

    /**
     * @param AttributeHydrator $attributeHydrator
     * @param CustomerGroupHydrator $customerGroupHydrator
     */
    public function __construct(
        AttributeHydrator $attributeHydrator,
        CustomerGroupHydrator $customerGroupHydrator
    ) {
        $this->attributeHydrator = $attributeHydrator;
        $this->customerGroupHydrator = $customerGroupHydrator;
    }

    /**
     * @param array $data
     * @return Customer
     */
    public function hydrate(array $data)
    {
        $customer = new Customer();

        $customer->setId((int) $data['__customer_id']);
        $customer->setNumber($data['__customer_customernumber']);
        $customer->setEmail($data['__customer_email']);
        $customer->setActive((bool) $data['__customer_active']);
        $customer->setSalutation($data['__customer_salutation']);
        $customer->setTitle($data['__customer_title']);
        $customer->setFirstname($data['__customer_firstname']);
        $customer->setLastname($data['__customer_lastname']);

        if (!empty($data['__customer_firstlogin'])) {
            $customer->setFirstLogin(new \DateTime($data['__customer_firstlogin']));
        }
        if (!empty($data['__customer_lastlogin'])) {
            $customer->setLastLogin(new \DateTime($data['__customer_lastlogin']));
        }
        if (!empty($data['__customer_lockeduntil'])) {
            $customer->setLockedUntil(new \DateTime($data['__customer_lockeduntil']));
        }
        if (!empty($data['__customer_birthday'])) {
            $customer->setBirthday(new \DateTime($data['__customer_birthday']));
        }
        $customer->setFailedLogins((int) $data['__customer_failedlogins']);
        $customer->setAccountMode((int) $data['__customer_accountmode']);
        $customer->setCustomerType($data['__customer_']);
        $customer->setValidation($data['__customer_validation']);
        $customer->setConfirmationKey($data['__customer_confirmationkey']);
        $customer->setOrderedNewsletter((bool) $data['__customer_newsletter']);
        $customer->setIsPartner((bool) $data['__customer_affiliate']);
        $customer->setReferer($data['__customer_referer']);
        $customer->setInternalComment($data['__customer_internalcomment']);

        $customer->setHasNotifications((bool) $data['__customer_has_notifications']);
        $customer->setDefaultBillingAddressId((int) $data['__customer_default_billing_address_id']);
        $customer->setDefaultShippingAddressId((int) $data['__customer_default_shipping_address_id']);

        if ($data['__customerAttribute.id']) {
            $this->attributeHydrator->addAttribute($customer, $data, 'customerAttribute');
        }

        if ($data['__customerGroup_id']) {
            $customer->setCustomerGroup($this->customerGroupHydrator->hydrate($data));
        }

        $customer->setLastPaymentMethodId((int) $data['__customer_paymentID']);
        $customer->setPresetPaymentMethodId((int) $data['__customer_paymentpreset']);

        return $customer;
    }
}
