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

namespace Shopware\Bundle\StoreFrontBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Country\Area;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\Customer\Group;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceGroup;

class ShopContext extends Extendable implements ShopContextInterface, ProductContextInterface
{
    /**
     * @var Group
     */
    protected $currentCustomerGroup;

    /**
     * @var Group
     */
    protected $fallbackCustomerGroup;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @var Tax[]
     */
    protected $taxRules;

    /**
     * @var PriceGroup[]
     */
    protected $priceGroups;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var Area|null
     */
    protected $area;

    /**
     * @var Country|null
     */
    protected $country;

    /**
     * @var State|null
     */
    protected $state;

    /**
     * @var int[]
     */
    protected $customerStreamIds;

    /**
     * @param string       $baseUrl
     * @param Tax[]        $taxRules
     * @param PriceGroup[] $priceGroups
     * @param int[]        $customerStreamIds
     */
    public function __construct(
        $baseUrl,
        Shop $shop,
        Currency $currency,
        Group $currentCustomerGroup,
        Group $fallbackCustomerGroup,
        array $taxRules,
        array $priceGroups,
        Area $area = null,
        Country $country = null,
        State $state = null,
        $customerStreamIds = []
    ) {
        $this->baseUrl = $baseUrl;
        $this->shop = $shop;
        $this->currency = $currency;
        $this->currentCustomerGroup = $currentCustomerGroup;
        $this->fallbackCustomerGroup = $fallbackCustomerGroup;
        $this->taxRules = $taxRules;
        $this->priceGroups = $priceGroups;
        $this->area = $area;
        $this->country = $country;
        $this->state = $state;
        $this->customerStreamIds = $customerStreamIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentCustomerGroup()
    {
        return $this->currentCustomerGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackCustomerGroup()
    {
        return $this->fallbackCustomerGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxRules()
    {
        return $this->taxRules;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxRule($taxId)
    {
        $key = 'tax_' . $taxId;

        return $this->taxRules[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceGroups()
    {
        return $this->priceGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->state;
    }

    public function getActiveCustomerStreamIds()
    {
        return $this->customerStreamIds;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
