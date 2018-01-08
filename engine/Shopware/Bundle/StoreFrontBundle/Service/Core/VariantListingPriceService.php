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

use Shopware\Bundle\SearchBundle\Condition\OrdernumberCondition;
use Shopware\Bundle\SearchBundle\Condition\VariantCondition;
use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\ProductSearchResult;
use Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactoryInterface;
use Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Attribute;
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

    public function __construct(
        QueryBuilderFactoryInterface $factory,
        VariantHelperInterface $helper
    ) {
        $this->helper = $helper;
        $this->factory = $factory;
    }

    public function updatePrices(Criteria $criteria, ProductSearchResult $result, ShopContextInterface $context)
    {
        $conditions = $criteria->getConditionsByClass(VariantCondition::class);

        $conditions = array_filter($conditions, function(VariantCondition $condition) {
            return $condition->expandVariants();
        });

        if (empty($conditions)) {
            return;
        }

        //check if variant listing prices is already loaded by price condition or price sorting
        //in this case it is not necessary to reload variant listing prices
        $updated = $this->tryUpdateByAttribute($result);

        if ($updated) {
            return;
        }

        //executed if no price condition or price sorting included in search request
        $this->loadPrices($criteria, $result, $context);
    }

    /**
     * @param Criteria             $criteria
     * @param ProductSearchResult  $result
     * @param ShopContextInterface $context
     */
    private function loadPrices(Criteria $criteria, ProductSearchResult $result, ShopContextInterface $context)
    {
        $conditions = $criteria->getConditionsByClass(VariantCondition::class);

        $cleanCriteria = new Criteria();
        foreach ($conditions as $condition) {
            $cleanCriteria->addCondition($condition);
        }
        $numbers = array_keys($result->getProducts());
        $cleanCriteria->addCondition(new OrdernumberCondition($numbers));

        $query = $this->factory->createQuery($cleanCriteria, $context);
        $select = $query->getQueryPart('select');
        $select = array_merge(['variant.ordernumber as array_key'], $select);
        $query->select($select);

        $this->helper->joinPrices($query, $context, $cleanCriteria);
        $query->addGroupBy('product.id');

        $data = $query->execute()->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE);

        foreach ($result->getProducts() as $product) {
            $number = $product->getNumber();

            if (!array_key_exists($number, $data)) {
                continue;
            }

            $product->getListingPrice()->setCalculatedPrice(
                round((float) $data[$number]['cheapest_price'], 2)
            );

            $product->setDisplayFromPrice($data[$number]['different_price_count'] > 1);
        }
    }

    private function tryUpdateByAttribute(ProductSearchResult $result)
    {
        foreach ($result->getProducts() as $product) {
            /** @var Attribute $attribute */
            $attribute = $product->getAttribute('search');
            if (!$attribute) {
                return false;
            }

            if (!$attribute->exists('cheapest_price')) {
                return false;
            }

            $product->getListingPrice()->setCalculatedPrice(
                round((float) $attribute->get('cheapest_price'), 2)
            );
        }

        return true;
    }
}
