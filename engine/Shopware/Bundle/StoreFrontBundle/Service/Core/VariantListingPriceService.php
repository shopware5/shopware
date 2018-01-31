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

use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\VariantCheapestPriceGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\PriceCalculationServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Product\PriceRule;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class VariantListingPriceService
{
    /**
     * @var VariantHelperInterface
     */
    private $helper;

    /**
     * @var QueryBuilderFactoryInterface
     */
    private $factory;

    /**
     * @var VariantCheapestPriceGatewayInterface
     */
    private $variantCheapestPriceGateway;

    /**
     * @var PriceCalculationServiceInterface
     */
    private $priceCalculationService;

    public function __construct(
        QueryBuilderFactoryInterface $factory,
        VariantHelperInterface $helper,
        VariantCheapestPriceGatewayInterface $variantCheapestPriceGateway,
        PriceCalculationServiceInterface $priceCalculationService
    ) {
        $this->helper = $helper;
        $this->factory = $factory;
        $this->variantCheapestPriceGateway = $variantCheapestPriceGateway;
        $this->priceCalculationService = $priceCalculationService;
    }

    public function updatePrices(Criteria $criteria, ProductSearchResult $result, ShopContextInterface $context)
    {
        $conditions = $criteria->getConditionsByClass(VariantCondition::class);

        $conditions = array_filter($conditions, function (VariantCondition $condition) {
            return $condition->expandVariants();
        });

        if (empty($conditions)) {
            return;
        }

        //executed if no price condition included in search request
        $this->loadPrices($criteria, $result, $context);
    }

    /**
     * @param Criteria             $criteria
     * @param ProductSearchResult  $result
     * @param ShopContextInterface $context
     */
    private function loadPrices(Criteria $criteria, ProductSearchResult $result, ShopContextInterface $context)
    {
        $cheapestPriceData = $this->variantCheapestPriceGateway->getList($result->getProducts(), $context, $context->getCurrentCustomerGroup(), $criteria);

        foreach ($result->getProducts() as $product) {
            $number = $product->getNumber();

            if (!array_key_exists($number, $cheapestPriceData)) {
                continue;
            }

            /** @var $cheapestPriceRule PriceRule */
            $cheapestPriceRule = $cheapestPriceData[$number]['price'];
            $cheapestPriceRule->setCustomerGroup($context->getCurrentCustomerGroup());

            $product->setPriceRules([$cheapestPriceRule]);

            $this->priceCalculationService->calculateProduct($product, $context);

            $product->setListingPrice($product->getPrices()[0]);

            $product->setDisplayFromPrice($cheapestPriceData[$number]['different_price_count'] > 1);
        }
    }
}
