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

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ShopContext extends Extendable implements ShopContextInterface, \JsonSerializable
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
     * @var TranslationContext
     */
    protected $translationContext;

    /**
     * @param string       $baseUrl
     * @param Shop         $shop
     * @param Currency     $currency
     * @param Group        $currentCustomerGroup
     * @param Group        $fallbackCustomerGroup
     * @param Tax[]        $taxRules
     * @param PriceGroup[] $priceGroups
     * @param Area|null    $area
     * @param Country|null $country
     * @param State|null   $state
     */
    public function __construct(
        $baseUrl,
        Shop $shop,
        Currency $currency,
        Group $currentCustomerGroup,
        Group $fallbackCustomerGroup,
        array $taxRules,
        array $priceGroups,
        ?Area $area,
        ?Country $country,
        ?State $state
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
        $this->translationContext = TranslationContext::createFromShop($this->shop);
    }

    public function getShop(): Shop
    {
        return $this->shop;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getCurrentCustomerGroup(): Group
    {
        return $this->currentCustomerGroup;
    }

    public function getFallbackCustomerGroup(): Group
    {
        return $this->fallbackCustomerGroup;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getTaxRules(): array
    {
        return $this->taxRules;
    }

    public function getPriceGroups(): array
    {
        return $this->priceGroups;
    }

    public function getArea(): ? Area
    {
        return $this->area;
    }

    public function getCountry(): ? Country
    {
        return $this->country;
    }

    public function getState(): ? State
    {
        return $this->state;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxRule(int $taxId): Tax
    {
        $key = 'tax_' . $taxId;

        return $this->taxRules[$key];
    }

    public function getTranslationContext(): TranslationContext
    {
        return $this->translationContext;
    }
}
