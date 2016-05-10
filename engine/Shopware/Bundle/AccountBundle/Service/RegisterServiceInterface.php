<?php

namespace Shopware\Bundle\AccountBundle\Service;

use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;

interface RegisterServiceInterface
{
    /**
     * @param Shop $shop
     * @param Customer $customer
     * @param Address $billing
     * @param Address|null $shipping
     */
    public function register(Shop $shop, Customer $customer, Address $billing, Address $shipping = null);
}
