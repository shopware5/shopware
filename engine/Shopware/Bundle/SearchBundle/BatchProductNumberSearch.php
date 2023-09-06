<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Bundle\SearchBundle;

use Shopware\Bundle\StoreFrontBundle\Service\BaseProductFactoryServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class BatchProductNumberSearch
{
    private ProductNumberSearchInterface $productNumberSearch;

    private BaseProductFactoryServiceInterface $baseProductFactoryService;

    /**
     * @var array<string, int>
     */
    private array $pointer = [];

    public function __construct(
        ProductNumberSearchInterface $productNumberSearch,
        BaseProductFactoryServiceInterface $baseProductFactoryService
    ) {
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
     * @param BaseProduct[] $baseProducts
     *
     * @return BaseProduct[]
     */
    private function getBaseProductsRange(string $key, array $baseProducts, int $numberOfProducts = 0): array
    {
        // cancel on empty results to prevent infinite loop
        if (\count($baseProducts) === 0) {
            return [];
        }

        if (!\array_key_exists($key, $this->pointer)) {
            $this->pointer[$key] = 0;
        }

        // use internal pointer to return different products to each request with the same criteria/context
        $items = \array_slice($baseProducts, $this->pointer[$key], $numberOfProducts);
        $missingItems = $numberOfProducts - \count($items);
        $this->pointer[$key] += \count($items);

        if ($missingItems === 0) {
            return $items;
        }

        $this->pointer[$key] = 0;

        return array_merge($items, $this->getBaseProductsRange($key, $baseProducts, $missingItems));
    }

    /**
     * @param array<string, Criteria> $criteriaList
     *
     * @return array<string, array<string, BaseProduct>>
     */
    private function getBaseProductsByCriteriaList(array $criteriaList, ShopContextInterface $context): array
    {
        $products = [];
        $optimizedCriteriaList = $this->getOptimizedCriteriaList($criteriaList);

        foreach ($optimizedCriteriaList as $key => $criteriaMeta) {
            $searchResult = $this->productNumberSearch->search($criteriaMeta['criteria'], $context);
            $baseProducts = $searchResult->getProducts();

            $this->pointer[$key] = 0;
            foreach ($criteriaMeta['requests'] as $request) {
                $products[$request['key']] = [];
                $productRange = $this->getBaseProductsRange($key, $baseProducts, (int) $request['criteria']->getLimit());

                foreach ($productRange as $product) {
                    $products[$request['key']][$product->getNumber()] = $product;
                }
            }
        }

        return $products;
    }

    /**
     * @param array<string, Criteria> $criteriaList
     *
     * @return array<array{criteria: Criteria, requests: array<array{criteria: Criteria, key: string}>}>
     */
    private function getOptimizedCriteriaList(array $criteriaList): array
    {
        $optimizedCriteriaList = [];

        foreach ($criteriaList as $key => $originalCriteria) {
            $criteriaPosition = $this->getOptimizedCriteriaListPosition($originalCriteria, $optimizedCriteriaList);

            if ($criteriaPosition !== false) {
                if (!isset($optimizedCriteriaList[$criteriaPosition]['criteria'])) {
                    continue;
                }
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
     * @param array<array{criteria: Criteria, requests: array<array{criteria: Criteria, key: string}>}> $criteriaList
     *
     * @return int|false
     */
    private function getOptimizedCriteriaListPosition(Criteria $criteria, array $criteriaList)
    {
        $comparableCriteria = $this->getComparableCriteria($criteria);

        foreach ($criteriaList as $index => $existingCriteria) {
            $existingCriteria = $this->getComparableCriteria($existingCriteria['criteria']);

            if ($comparableCriteria == $existingCriteria) {
                return $index;
            }
        }

        return false;
    }

    private function getComparableCriteria(Criteria $criteria): Criteria
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
     * @return array<string, BaseProduct>
     */
    private function getBaseProductsByProductNumberRequest(BatchProductNumberSearchRequest $request): array
    {
        $baseProductList = [];

        if (\count($request->getProductNumbers())) {
            $productNumbers = array_merge(...array_values($request->getProductNumbers()));
            $baseProductList = $this->baseProductFactoryService->createBaseProducts($productNumbers);
        }

        return $baseProductList;
    }
}
