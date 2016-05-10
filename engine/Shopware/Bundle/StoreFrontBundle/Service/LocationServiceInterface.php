<?php

namespace Shopware\Bundle\StoreFrontBundle\Service;

use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface LocationServiceInterface
{
    /**
     * Returns a list of all available countries and their states for the provided shop context.
     * Countries are sorted first at the position field and as fallback by the name property
     * @param ShopContextInterface $context
     * @return Country[] indexed by country id
     */
    public function getAvailableCountries(ShopContextInterface $context);
}
