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

namespace Shopware\Bundle\BenchmarkBundle\Provider;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\BenchmarkBundle\BenchmarkProviderInterface;
use Shopware\Bundle\BenchmarkBundle\Service\MatcherService;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ShipmentsProvider implements BenchmarkProviderInterface
{
    private const NAME = 'shipments';

    /**
     * @var Connection
     */
    private $dbalConnection;

    /**
     * @var ShopContextInterface
     */
    private $shopContext;

    /**
     * @var array
     */
    private $shipmentIds = [];

    /**
     * @var MatcherService
     */
    private $matcher;

    public function __construct(Connection $dbalConnection, MatcherService $matcherService)
    {
        $this->dbalConnection = $dbalConnection;
        $this->matcher = $matcherService;
    }

    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getBenchmarkData(ShopContextInterface $shopContext)
    {
        $this->shopContext = $shopContext;
        $this->shipmentIds = [];

        return [
            'list' => $this->getMatchedShipmentList(),
            'usages' => $this->getMatchedShipmentUsages(),
        ];
    }

    /**
     * @return array
     */
    private function getMatchedShipmentList()
    {
        $shipmentList = $this->getShipmentList();

        $matches = [];
        $others = [];
        foreach ($shipmentList as $shipmentName => $prices) {
            $matchedName = $this->matcher->matchString($shipmentName);

            if ($matchedName === 'others') {
                $others[] = $prices;
                continue;
            }

            $matches[$matchedName] = ['name' => $matchedName] + $prices;
        }

        $matches['others']['name'] = 'others';
        $matches['others']['minPrice'] = (float) min(array_column($others, 'minPrice'));
        $matches['others']['maxPrice'] = (float) max(array_column($others, 'maxPrice'));

        return array_values($matches);
    }

    /**
     * @return array
     */
    private function getMatchedShipmentUsages()
    {
        $shipments = $this->getShipmentUsages();

        $matches = [];
        foreach ($shipments as $shipmentName => $usages) {
            $match = $this->matcher->matchString($shipmentName);

            if (!isset($matches[$match])) {
                $matches[$match] = [
                    'name' => $match,
                    'usages' => 0,
                ];
            }

            $matches[$match]['usages'] += $usages;
        }

        return array_values($matches);
    }

    /**
     * @return array
     */
    private function getShipmentList()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $shippingCosts = $queryBuilder->select('dispatch.name, MIN(costs.value) as minPrice, MAX(costs.value) as maxPrice')
            ->from('s_premium_dispatch', 'dispatch')
            ->where('dispatch.id IN (:dispatchIds)')
            ->innerJoin('dispatch', 's_premium_shippingcosts', 'costs', 'dispatch.id = costs.dispatchID')
            ->groupBy('dispatch.id')
            ->setParameter(':dispatchIds', $this->getShipmentIds(), Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_UNIQUE | \PDO::FETCH_ASSOC);

        return array_map(function ($shippingCost) {
            $shippingCost['minPrice'] = (float) $shippingCost['minPrice'];
            $shippingCost['maxPrice'] = (float) $shippingCost['maxPrice'];

            return $shippingCost;
        }, $shippingCosts);
    }

    /**
     * @return array
     */
    private function getShipmentUsages()
    {
        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('dispatches.name, COUNT(orders.id) as usages')
            ->from('s_order', 'orders')
            ->where('dispatches.id IN (:dispatchIds)')
            ->andWhere('orders.status != -1')
            ->leftJoin('orders', 's_premium_dispatch', 'dispatches', 'dispatches.id = orders.dispatchID')
            ->groupBy('orders.dispatchID')
            ->setParameter(':dispatchIds', $this->getShipmentIds(), Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    /**
     * @return array
     */
    private function getShipmentIds()
    {
        $shopId = $this->shopContext->getShop()->getId();
        if (array_key_exists($shopId, $this->shipmentIds)) {
            return $this->shipmentIds[$shopId];
        }

        $categoryIds = $this->getPossibleCategoryIds();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        $dispatchIds = $queryBuilder->select('dispatch.id')
            ->from('s_premium_dispatch', 'dispatch')
            ->where('dispatch.type != 3')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $dispatchIds = array_combine($dispatchIds, $dispatchIds);

        $forbiddenCategoriesBuilder = $this->dbalConnection->createQueryBuilder();

        $forbiddenCategoriesByDispatchId = $forbiddenCategoriesBuilder->select('dispatch.dispatchID, dispatch.categoryID')
            ->from('s_premium_dispatch_categories', 'dispatch')
            ->execute()
            ->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN);

        // Figure out all dispatches, that forbid ALL categories being available to a shop
        foreach ($forbiddenCategoriesByDispatchId as $dispatchId => $forbiddenCategories) {
            $availableCategoryIds = array_combine($categoryIds, $categoryIds);
            foreach ($forbiddenCategories as $forbiddenCategory) {
                if (array_key_exists($forbiddenCategory, $availableCategoryIds)) {
                    unset($availableCategoryIds[$forbiddenCategory]);
                }
            }

            if (!$availableCategoryIds) {
                unset($dispatchIds[$dispatchId]);
            }
        }

        $this->shipmentIds[$shopId] = $dispatchIds;

        return $this->shipmentIds[$shopId];
    }

    /**
     * @return array
     */
    private function getPossibleCategoryIds()
    {
        $categoryId = $this->shopContext->getShop()->getCategory()->getId();

        $queryBuilder = $this->dbalConnection->createQueryBuilder();

        return $queryBuilder->select('category.id')
            ->from('s_categories', 'category')
            ->where('category.path LIKE :categoryIdPath')
            ->orWhere('category.id = :categoryId')
            ->setParameter(':categoryId', $categoryId)
            ->setParameter(':categoryIdPath', '%|' . $categoryId . '|%')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }
}
