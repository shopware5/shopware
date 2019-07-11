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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\CurrencyGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\CustomerGroupGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\PriceGroupDiscountGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\ShopGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\TaxGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ShopContextFactory implements ShopContextFactoryInterface
{
    const FALLBACK_CUSTOMER_GROUP = 'EK';

    /**
     * @var CustomerGroupGatewayInterface
     */
    protected $customerGroupGateway;

    /**
     * @var TaxGatewayInterface
     */
    protected $taxGateway;

    /**
     * @var PriceGroupDiscountGatewayInterface
     */
    protected $priceGroupDiscountGateway;

    /**
     * @var ShopGatewayInterface
     */
    protected $shopGateway;

    /**
     * @var CurrencyGatewayInterface
     */
    protected $currencyGateway;

    /**
     * @var CountryGatewayInterface
     */
    protected $countryGateway;

    public function __construct(
        CustomerGroupGatewayInterface $customerGroupGateway,
        TaxGatewayInterface $taxGateway,
        CountryGatewayInterface $countryGateway,
        PriceGroupDiscountGatewayInterface $priceGroupDiscountGateway,
        ShopGatewayInterface $shopGateway,
        CurrencyGatewayInterface $currencyGateway
    ) {
        $this->customerGroupGateway = $customerGroupGateway;
        $this->taxGateway = $taxGateway;
        $this->countryGateway = $countryGateway;
        $this->priceGroupDiscountGateway = $priceGroupDiscountGateway;
        $this->shopGateway = $shopGateway;
        $this->currencyGateway = $currencyGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        string $baseUrl,
        int $shopId,
        ?int $currencyId = null,
        ?string $currentCustomerGroupKey = null,
        ?int $areaId = null,
        ?int $countryId = null,
        ?int $stateId = null,
        array $streamIds = []
    ): ShopContextInterface {
        $shop = $this->shopGateway->get($shopId);
        $fallbackCustomerGroupKey = self::FALLBACK_CUSTOMER_GROUP;

        if ($currentCustomerGroupKey == null) {
            $currentCustomerGroupKey = $fallbackCustomerGroupKey;
        }

        $groups = $this->customerGroupGateway->getList([$currentCustomerGroupKey, $fallbackCustomerGroupKey]);

        $currentCustomerGroup = $groups[$currentCustomerGroupKey];
        $fallbackCustomerGroup = $groups[$fallbackCustomerGroupKey];

        $currency = null;
        if ($currencyId != null) {
            $currency = $this->currencyGateway->getList([$currencyId]);
            $currency = array_shift($currency);
        }
        if (!$currency) {
            $currency = $shop->getCurrency();
        }

        $context = new ShopContext($baseUrl, $shop, $currency, $currentCustomerGroup, $fallbackCustomerGroup, [], []);

        $area = null;
        if ($areaId !== null) {
            $area = $this->countryGateway->getArea($areaId, $context);
        }

        $country = null;
        if ($countryId !== null) {
            $country = $this->countryGateway->getCountry($countryId, $context);
        }

        $state = null;
        if ($stateId !== null) {
            $state = $this->countryGateway->getState($stateId, $context);
        }

        $taxRules = $this->taxGateway->getRules($currentCustomerGroup, $area, $country, $state);
        $priceGroups = $this->priceGroupDiscountGateway->getPriceGroups($currentCustomerGroup, $context);

        return new ShopContext(
            $baseUrl,
            $shop,
            $currency,
            $currentCustomerGroup,
            $fallbackCustomerGroup,
            $taxRules,
            $priceGroups,
            $area,
            $country,
            $state,
            $streamIds
        );
    }
}
