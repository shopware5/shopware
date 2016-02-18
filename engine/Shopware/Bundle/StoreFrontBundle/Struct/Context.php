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
 * @package   Shopware\Bundle\StoreFrontBundle\Struct
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Context extends Extendable implements \JsonSerializable, LocationContextInterface, ProductContextInterface
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
     * @var Area
     */
    protected $area;

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var State
     */
    protected $state;

    /**
     * @param string       $baseUrl
     * @param Shop         $shop
     * @param Currency     $currency
     * @param Group        $currentCustomerGroup
     * @param Group        $fallbackCustomerGroup
     * @param Tax[]        $taxRules
     * @param PriceGroup[] $priceGroups
     * @param Area         $area
     * @param Country      $country
     * @param State        $state
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
        State $state = null
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
    }

    /**
     * @param ProductContext  $productContext
     * @param LocationContext $locationContext
     * @return Context
     */
    public static function createFromContexts(
        ProductContext $productContext,
        LocationContext $locationContext
    ) {
        return new self(
            $productContext->getBaseUrl(),
            $productContext->getShop(),
            $productContext->getCurrency(),
            $productContext->getCurrentCustomerGroup(),
            $productContext->getFallbackCustomerGroup(),
            $productContext->getTaxRules(),
            $productContext->getPriceGroups(),
            $locationContext->getArea(),
            $locationContext->getCountry(),
            $locationContext->getState()
        );
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
     * @return Customer\Group
     */
    public function getCurrentCustomerGroup()
    {
        return $this->currentCustomerGroup;
    }

    /**
     * @return Customer\Group
     */
    public function getFallbackCustomerGroup()
    {
        return $this->fallbackCustomerGroup;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @return Tax[]
     */
    public function getTaxRules()
    {
        return $this->taxRules;
    }

    /**
     * @param $taxId
     * @return Tax
     */
    public function getTaxRule($taxId)
    {
        $key = 'tax_' . $taxId;

        return $this->taxRules[$key];
    }

    /**
     * @return PriceGroup[]
     */
    public function getPriceGroups()
    {
        return $this->priceGroups;
    }

    /**
     * @return Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
