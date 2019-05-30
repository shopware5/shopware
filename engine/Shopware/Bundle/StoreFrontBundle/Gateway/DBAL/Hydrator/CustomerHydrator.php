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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator;

use Shopware\Bundle\StoreFrontBundle\Struct\Customer;

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

    public function __construct(AttributeHydrator $attributeHydrator, CustomerGroupHydrator $customerGroupHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
        $this->customerGroupHydrator = $customerGroupHydrator;
    }

    public function hydrate(array $data)
    {
        $customer = new Customer();
        $customer->setId($data['__customer_id']);
        $customer->setEncoder($data['__customer_encoder']);
        $customer->setEmail($data['__customer_email']);
        $customer->setActive($data['__customer_active']);
        $customer->setAccountMode($data['__customer_accountmode']);
        $customer->setConfirmationKey($data['__customer_confirmationkey']);
        $customer->setPaymentId($data['__customer_paymentID']);
        $customer->setValidation($data['__customer_validation']);
        $customer->setAffiliate($data['__customer_affiliate']);
        $customer->setPaymentPreset($data['__customer_paymentpreset']);
        $customer->setLanguageId($data['__customer_language']);
        $customer->setShopId($data['__customer_subshopID']);
        $customer->setReferer($data['__customer_referer']);
        $customer->setInternalComment($data['__customer_internalcomment']);
        $customer->setFailedLogins($data['__customer_failedlogins']);
        $customer->setDefaultBillingAddressId($data['__customer_default_billing_address_id']);
        $customer->setDefaultShippingAddressId($data['__customer_default_shipping_address_id']);
        $customer->setTitle($data['__customer_title']);
        $customer->setSalutation($data['__customer_salutation']);
        $customer->setFirstname($data['__customer_firstname']);
        $customer->setLastname($data['__customer_lastname']);
        $customer->setNumber($data['__customer_customernumber']);
        $customer->setNewsletter((bool) $data['__active_campaign']);

        if ($data['__customer_birthday']) {
            $customer->setBirthday(new \DateTime($data['__customer_birthday']));
        }

        if ($customer->getBirthday()) {
            $customer->setAge($customer->getBirthday()->diff(new \DateTime())->y);
        }

        if (!empty($data['__customer_lockeduntil'])) {
            $customer->setLockedUntil(new \DateTime($data['__customer_lockeduntil']));
        }
        if (!empty($data['__customer_firstlogin'])) {
            $customer->setFirstLogin(new \DateTime($data['__customer_firstlogin']));
        }
        if (!empty($data['__customer_lastlogin'])) {
            $customer->setLastLogin(new \DateTime($data['__customer_lastlogin']));
        }

        if ($data['__customer_customergroup']) {
            $customer->setCustomerGroup(
                $this->customerGroupHydrator->hydrate($data)
            );
        }

        if (isset($data['__customerAttribute_id'])) {
            $this->attributeHydrator->addAttribute($customer, $data, 'customerAttribute');
        }

        return $customer;
    }
}
