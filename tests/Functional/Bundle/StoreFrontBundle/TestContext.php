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

namespace Shopware\Tests\Functional\Bundle\StoreFrontBundle;

use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\Currency;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Struct\Tax;

class TestContext extends ShopContext
{
    public function setArea(Area $area): void
    {
        $this->area = $area;
    }

    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    public function setCountry(Country $country): void
    {
        $this->country = $country;
    }

    public function setCurrency(Currency $currency): void
    {
        $this->currency = $currency;
    }

    public function setCurrentCustomerGroup(
        Group $currentCustomerGroup): void
    {
        $this->currentCustomerGroup = $currentCustomerGroup;
    }

    public function setFallbackCustomerGroup(
        Group $fallbackCustomerGroup): void
    {
        $this->fallbackCustomerGroup = $fallbackCustomerGroup;
    }

    /**
     * @param PriceGroup[] $priceGroups
     */
    public function setPriceGroups(array $priceGroups): void
    {
        $this->priceGroups = $priceGroups;
    }

    public function setShop(Shop $shop): void
    {
        $this->shop = $shop;
    }

    public function setState(State $state): void
    {
        $this->state = $state;
    }

    /**
     * @param Tax[] $taxRules
     */
    public function setTaxRules(array $taxRules): void
    {
        $this->taxRules = $taxRules;
    }
}
