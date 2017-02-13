<?php

namespace Shopware\Bundle\CustomerSearchBundle\Gateway;

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
    public function __construct(AttributeHydrator $attributeHydrator, CustomerGroupHydrator $customerGroupHydrator)
    {
        $this->attributeHydrator = $attributeHydrator;
        $this->customerGroupHydrator = $customerGroupHydrator;
    }

    public function hydrate(array $data)
    {
        $customer = new CustomerStruct();
        $customer->setId($data['__customer_id']);
        $customer->setEncoder($data['__customer_encoder']);
        $customer->setEmail($data['__customer_email']);
        $customer->setActive($data['__customer_active']);
        $customer->setAccountMode($data['__customer_accountmode']);
        $customer->setConfirmationKey($data['__customer_confirmationkey']);
        $customer->setPaymentId($data['__customer_paymentID']);
        $customer->setNewsletter($data['__customer_newsletter']);
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
