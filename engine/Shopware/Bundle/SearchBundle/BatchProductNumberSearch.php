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

namespace Shopware\Bundle\SearchBundle;

use Shopware\Bundle\StoreFrontBundle\Service\BaseProductFactoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class BatchProductNumberSearch
{
    /**
     * @var ProductNumberSearchInterface
     */
    private $productNumberSearch;

    /**
     * @var BaseProductFactoryServiceInterface
     */
    private $baseProductFactoryService;

    /**
     * @var array
     */
    private $pointer = [];

    public function __construct(ProductNumberSearchInterface $productNumberSearch, BaseProductFactoryServiceInterface $baseProductFactoryService)
    {
        $this->productNumberSearch = $productNumberSearch;
        $this->baseProductFactoryService = $baseProductFactoryService;
    }

    /**
     * @return BatchProductNumberSearchResult
     */
    public function search(BatchProductNumberSearchRequest $request, ShopContextInterface $context)
    {
        // resolve product numbers of criteria objects and add them to the request
        $criteriaListWithBaseProducts = $this->getBaseProductsByCriteriaList($request->getCriteriaList(), $context);
        $baseProductList = $this->getBaseProductsByProductNumberRequest($request);

        $result = [];

        foreach ($request->getProductNumbers() as $key => $productNumbers) {
            $baseProducts = array_intersect_key($baseProductList, array_flip($productNumbers));
            $result[$key] = $baseProducts;
        }

        foreach ($criteriaListWithBaseProducts as $key => $baseProducts) {
            $result[$key] = $baseProducts;
        }

        return new BatchProductNumberSearchResult($result);
    }

    /**
     * @param string        $key
     * @param BaseProduct[] $baseProducts
     * @param int           $numberOfProducts
     *
     * @return BaseProduct[]
     */
    private function getBaseProductsRange($key, array $baseProducts, $numberOfProducts = 0)
    {
        // cancel on empty results to prevent infinite loop
        if (count($baseProducts) === 0) {
            return [];
        }

        if (!array_key_exists($key, $this->pointer)) {
            $this->pointer[$key] = 0;
        }

        // use internal pointer to return different products to each request with the same criteria/context
        $items = array_slice($baseProducts, $this->pointer[$key], $numberOfProducts);
        $missingItems = $numberOfProducts - count($items);
        $this->pointer[$key] += count($items);

        if ($missingItems === 0) {
            return $items;
        }

        $this->pointer[$key] = 0;

        return array_merge($items, $this->getBaseProductsRange($key, $baseProducts, $missingItems));
    }

    /**
     * @return array
     */
    private function getBaseProductsByCriteriaList(array $criteriaList, ShopContextInterface $context)
    {
        $products = [];
        $optimizedCriteriaList = $this->getOptimizedCriteriaList($criteriaList);

        foreach ($optimizedCriteriaList as $key => $criteriaMeta) {
            /** @var ProductNumberSearchResult $searchResult */
            $searchResult = $this->productNumberSearch->search($criteriaMeta['criteria'], $context);
            $baseProducts = $searchResult->getProducts();

            $this->pointer[$key] = 0;
            foreach ($criteriaMeta['requests'] as $request) {
                $products[$request['key']] = [];
                $productRange = $this->getBaseProductsRange($key, $baseProducts, $request['criteria']->getLimit());

                foreach ($productRange as $product) {
                    $products[$request['key']][$product->getNumber()] = $product;
                }
            }
        }

        return $products;
    }

    /**
     * @param Criteria[] $criteriaList
     *
     * @return array
     */
    private function getOptimizedCriteriaList(array $criteriaList)
    {
        $optimizedCriteriaList = [];

        foreach ($criteriaList as $key => $originalCriteria) {
            /** @var int $criteriaPosition */
            $criteriaPosition = $this->getOptimizedCriteriaListPosition($originalCriteria, $optimizedCriteriaList);

            if ($criteriaPosition !== false) {
                /** @var Criteria $existingCriteria */
                $existingCriteria = $optimizedCriteriaList[$criteriaPosition]['criteria'];

                // search requests already exists, increase limit to select more products and satisfy all requests
                $existingCriteria->limit($existingCriteria->getLimit() + $originalCriteria->getLimit());

                $optimizedCriteriaList[$criteriaPosition]['requests'][] = ['criteria' => $originalCriteria, 'key' => $key];
                continue;
            }

            $criteria = $this->getComparableCriteria($originalCriteria);
            $criteria->limit($originalCriteria->getLimit());

            $optimizedCriteriaList[] = [
                'criteria' => $criteria,
                'requests' => [
                    ['criteria' => $originalCriteria, 'key' => $key],
                ],
            ];
        }

        return array_values($optimizedCriteriaList);
    }

    /**
     * @return int|false
     */
    private function getOptimizedCriteriaListPosition(Criteria $criteria, array $criteriaList)
    {
        $comparableCriteria = $this->getComparableCriteria($criteria);

        foreach ($criteriaList as $index => $existingCriteria) {
            $existingCriteria = $this->getComparableCriteria($existingCriteria['criteria']);

            /* @noinspection TypeUnsafeComparisonInspection */
            if ($comparableCriteria == $existingCriteria) {
                return $index;
            }
        }

        return false;
    }

    /**
     * @return Criteria
     */
    private function getComparableCriteria(Criteria $criteria)
    {
        $conditions = $criteria->getConditions();
        $sortings = $criteria->getSortings();

        usort($conditions, function (ConditionInterface $a, ConditionInterface $b) {
            return strnatcmp($a->getName(), $b->getName());
        });

        usort($sortings, function (SortingInterface $a, SortingInterface $b) {
            return strnatcmp($a->getName(), $b->getName());
        });

        $criteria = new Criteria();

        array_walk($conditions, [$criteria, 'addCondition']);
        array_walk($sortings, [$criteria, 'addSorting']);

        return $criteria;
    }

    /**
     * @return BaseProduct[]
     */
    private function getBaseProductsByProductNumberRequest(BatchProductNumberSearchRequest $request)
    {
        $baseProductList = [];

        if (count($request->getProductNumbers())) {
            $productNumbers = array_merge(...array_values($request->getProductNumbers()));
            $baseProductList = $this->baseProductFactoryService->createBaseProducts($productNumbers);
        }

        return $baseProductList;
    }
}
