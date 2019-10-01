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

namespace Shopware\Models\Analytics;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Shopware\Components\Model\DBAL\Result;
use Shopware\Models\Shop\Shop;

class Repository
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    public function __construct(Connection $connection, \Enlight_Event_EventManager $eventManager)
    {
        $this->connection = $connection;
        $this->eventManager = $eventManager;
    }

    /**
     * Returns a dbal result object which displays the total visits of each day
     * for the passed date range.
     *
     * The data array is indexed by the order date. To remove the useless array level
     * execute the following code:
     *      $visitors = $repository->getDailyVisitors(... , ...);
     *      $visitors = array_map('reset', $visitors->getData());
     *
     * @return Result
     */
    public function getDailyVisitors(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->createDailyVisitorsBuilder($from, $to);

        $builder = $this->eventManager->filter('Shopware_Analytics_DailyVisitors', $builder, [
            'subject' => $this,
        ]);

        return new Result(
            $builder,
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC,
            false
        );
    }

    /**
     * Returns a dbal result object which displays the total visits of each
     * day for the passed date range.
     * If shop ids passed, the data result contains additionally for each shop id
     * a data result column like "visits1" for "visits" + shop id.
     *
     * The data array is indexed by the order date. To remove the useless array level
     * execute the following code:
     *      $visitors = $repository->getDailyShopVisitors(... , ...);
     *      $visitors = array_map('reset', $visitors->getData());
     *
     * @param int[] $shopIds
     *
     * @return Result
     */
    public function getDailyShopVisitors(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $builder = $this->createDailyVisitorsBuilder($from, $to);

        foreach ($shopIds as $shopId) {
            $shopId = (int) $shopId;
            $builder->addSelect(
                'SUM(IF(visitor.shopID = ' . $shopId . ', visitor.uniquevisits, 0)) as visits' . $shopId
            );
        }

        $builder = $this->eventManager->filter('Shopware_Analytics_DailyShopVisitors', $builder, [
            'subject' => $this,
        ]);

        return new Result(
            $builder,
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC,
            false
        );
    }

    /**
     * Returns a dbal result object which displays the total orders of each
     * day for the passed date range.
     * If shop ids passed, the data result contains additionally for each shop id
     * a data result column like "orderCount1" for "orderCount" + shop id.
     *
     * The data array is indexed by the order date. To remove the useless array level
     * execute the following code:
     *      $orders = $repository->getDailyShopOrders(... , ...);
     *      $orders = array_map('reset', $orders->getData());
     *
     * @param int[] $shopIds
     *
     * @return Result
     */
    public function getDailyShopOrders(\DateTimeInterface $from, \DateTimeInterface $to, array $shopIds)
    {
        $builder = $this->createDailyShopOrderBuilder($from, $to, $shopIds);

        $builder = $this->eventManager->filter('Shopware_Analytics_DailyShopOrders', $builder, [
            'subject' => $this,
        ]);

        return new Result(
            $builder,
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC,
            false
        );
    }

    /**
     * Returns a dbal result object which displays the total visits of each day
     * for the passed date range.
     *
     * The data array is indexed by the order date. To remove the useless array level
     * execute the following code:
     *      $registrations = $repository->getDailyRegistrations(... , ...);
     *      $registrations = array_map('reset', $registrations->getData());
     *
     * @return Result
     */
    public function getDailyRegistrations(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->createDailyRegistrationsBuilder($from, $to);

        $builder = $this->eventManager->filter('Shopware_Analytics_DailyRegistrations', $builder, [
            'subject' => $this,
        ]);

        return new Result(
            $builder,
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC,
            false
        );
    }

    /**
     * Returns a dbal result object which displays the turnover and the total orders of each day
     * for the passed date range.
     *
     * The data array is indexed by the order date. To remove the useless array level
     * execute the following code:
     *      $turnover = $repository->getDailyTurnover(... , ...);
     *      $turnover = array_map('reset', $turnover->getData());
     *
     * @return Result
     */
    public function getDailyTurnover(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->createDailyTurnoverBuilder($from, $to);

        $builder = $this->eventManager->filter('Shopware_Analytics_ShopStatisticTurnover', $builder, [
            'subject' => $this,
        ]);

        return new Result(
            $builder,
            \PDO::FETCH_GROUP | \PDO::FETCH_ASSOC,
            false
        );
    }

    /**
     * Returns a result object which displays all referrers url and the call count.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return Result
     *                array (
     *                'count' => '3',
     *                'referrer' => 'https://www.google.de/',
     *                )
     */
    public function getVisitedReferrer($offset, $limit, \DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->createVisitedReferrerBuilder($from, $to);

        $this->addPagination($builder, $offset, $limit);

        $builder = $this->eventManager->filter('Shopware_Analytics_VisitedReferrer', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns a result which displays the revenue of each referrer.
     *
     * @return Result
     */
    public function getReferrerRevenue(Shop $shop, \DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->createReferrerRevenueBuilder($shop, $from, $to);

        $builder = $this->eventManager->filter('Shopware_Analytics_ReferrerRevenue', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns a result which displays the revenue of each partner
     *
     * @param int $offset
     * @param int $limit
     *
     * @return Result
     */
    public function getPartnerRevenue($offset, $limit, \DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->createPartnerRevenueBuilder($from, $to);

        $this->addPagination($builder, $offset, $limit);

        $builder = $this->eventManager->filter('Shopware_Analytics_PartnerRevenue', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns a result which displays the sell count of each product.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return Result
     *                array (
     *                'sellCount' => '243',
     *                'name' => 'ESD Download Artikel',
     *                'ordernumber' => 'SW10196',
     *                ),
     *
     *      array (
     *          'sellCount' => '121',
     *          'name' => 'Aufschlag bei Zahlungsarten',
     *          'ordernumber' => 'SW10002841',
     *      ),
     */
    public function getProductSales($offset, $limit, \DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->createProductSalesBuilder($from, $to);

        $this->addPagination($builder, $offset, $limit);

        $builder = $this->eventManager->filter('Shopware_Analytics_ProductSales', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns a result which displays which kind of user created at which time orders.
     *
     * @return Result
     *                array (
     *                'firstLogin' => '2012-08-30',
     *                'orderTime' => '2013-12-18 14:46:24',
     *                'count' => '1',
     *                'salutation' => 'company',
     *                ),
     *
     *      array (
     *          'firstLogin' => '2011-11-23',
     *          'orderTime' => '2013-11-15 14:46:24',
     *          'count' => '121',
     *          'salutation' => 'mr',
     *      ),
     */
    public function getOrdersOfCustomers(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->createOrdersOfCustomersBuilder($from, $to);

        $builder = $this->eventManager->filter('Shopware_Analytics_OrdersOfCustomers', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns a result object which displays the customer age.
     *
     * @param int[] $shopIds
     *
     * @return Result
     */
    public function getAgeOfCustomers(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $builder = $this->createAgeOfCustomersBuilder($from, $to, $shopIds);

        $builder = $this->eventManager->filter('Shopware_Analytics_AgeOfCustomers', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns an array representing the product sales per category.
     * The number of orders and the entire order is returned.
     * The "from" and "to" parameter allows to restrict the result to a specify
     * date range.
     * The "categoryId" allows to restrict the result to a specify category level.
     *
     * @param int $categoryId
     *
     * @return Result
     *                array (
     *                'count' => '122',
     *                'amount' => '24656.19400000029',
     *                'name' => 'Deutsch',
     *                'node' => '3',
     *                ),
     *                array (
     *                'count' => '122',
     *                'amount' => '24656.19400000029',
     *                'name' => 'English',
     *                'node' => '39',
     *                ),
     */
    public function getProductAmountPerCategory($categoryId, \DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->createProductAmountBuilder($from, $to)
            ->addSelect('categories.description as name')
            ->addSelect('( SELECT parent FROM s_categories WHERE categories.id = parent LIMIT 1 ) as node')
            ->innerJoin('articles', 's_articles_categories_ro', 'articleCategories', 'articles.id = articleCategories.articleID')
            ->innerJoin('articleCategories', 's_categories', 'categories', 'articleCategories.categoryID = categories.id')
            ->andWhere('categories.active = 1')
            ->groupBy('categories.id');

        if ($categoryId) {
            $builder->andWhere('categories.parent = :parent')
                ->setParameter('parent', $categoryId);
        }

        $builder = $this->eventManager->filter('Shopware_Analytics_ProductAmountPerCategory', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns a result which displays which the order count of each manufacturer product.
     *
     * @param int|null $offset
     * @param int|null $limit
     *
     * @return Result
     *                array (
     *                'count' => '122',
     *                'amount' => '9303.713999999969',
     *                'name' => 'Beachdreams Clothes',
     *                ),
     *                array (
     *                'count' => '121',
     *                'amount' => '15352.479999999925',
     *                'name' => 'Example',
     *                )
     */
    public function getProductAmountPerManufacturer($offset = null, $limit = null, \DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->createProductAmountBuilder($from, $to)
            ->addSelect('suppliers.name')
            ->leftJoin('articles', 's_articles_supplier', 'suppliers', 'articles.supplierID = suppliers.id')
            ->groupBy('articles.supplierID')
            ->orderBy('turnover', 'DESC');

        $this->addPagination($builder, $offset, $limit);

        $builder = $this->eventManager->filter('Shopware_Analytics_ProductAmountPerManufacturer', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns a result which displays count and purchase amount of order for each device type.
     *
     * @param int[] $shopIds
     *
     * @return Result
     *                array (
     *                'count' => '122',
     *                'amount' => '9303.713999999969',
     *                'deviceType' => 'desktop',
     *                ),
     *                array (
     *                'count' => '121',
     *                'amount' => '15352.479999999925',
     *                'deviceType' => 'tablet',
     *                )
     */
    public function getProductAmountPerDevice(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('orders.deviceType')
            ->groupBy('orders.deviceType')
            ->orderBy('turnover', 'DESC');

        $builder = $this->eventManager->filter('Shopware_Analytics_ProductAmountPerDevice', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns an array which displays which search term executed in the shop.
     * The data result contains the executed search term, the count of request
     * which sends this search term and how many result are returned for this term.
     *
     * @param int   $offset  numeric value which defines the query start page
     * @param int   $limit   numeric value which defines the query limit
     * @param array $sort
     * @param int[] $shopIds
     *
     * @return Result
     *                array (
     *                'countRequests' => '90',
     *                'searchterm' => 'iphone',
     *                'countResults' => '1401',
     *                ),
     *                array (
     *                'countRequests' => '63',
     *                'searchterm' => 'ipho',
     *                'countResults' => '1390',
     *                )
     */
    public function getSearchTerms($offset, $limit, \DateTimeInterface $from = null, \DateTimeInterface $to = null, $sort = [], array $shopIds = [])
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select([
            'COUNT(search.searchterm) AS countRequests',
            'search.searchterm',
            'MAX(search.results) as countResults',
            'GROUP_CONCAT(DISTINCT shops.name SEPARATOR ", ") as shop',
        ])
            ->from('s_statistics_search', 'search')
            ->leftJoin('search', 's_core_shops', 'shops', 'search.shop_id = shops.id')
            ->groupBy('search.searchterm')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if (!empty($sort)) {
            foreach ($sort as $condition) {
                $builder->addOrderBy(
                    $condition['property'],
                    $condition['direction']
                );
            }
        }
        if (!empty($shopIds)) {
            $builder->andWhere('search.shop_id IN (:shopIds)')
                ->setParameter('shopIds', $shopIds, Connection::PARAM_INT_ARRAY);
        }

        $this->addDateRangeCondition($builder, $from, $to, 'datum');

        $builder = $this->eventManager->filter('Shopware_Analytics_SearchTerms', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns a result object which displays all referrers url and the call count.
     *
     * @param string $referrer
     * @param int    $offset
     * @param int    $limit
     *
     * @return Result
     *                array (
     *                'count' => '3',
     *                'referrer' => 'https://www.google.de/',
     *                )
     */
    public function getReferrerUrls($referrer, $offset, $limit)
    {
        $builder = $this->createVisitedReferrerBuilder()
            ->where('referrers.referer LIKE :selectedReferrer')
            ->setParameter('selectedReferrer', '%' . $referrer . '%');

        $this->addPagination($builder, $offset, $limit);

        $builder = $this->eventManager->filter('Shopware_Analytics_ReferrerUrls', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns a result object which displays all referrers url and the call count.
     *
     * @param string $referrer
     *
     * @return Result
     *                array (
     *                'count' => '3',
     *                'referrer' => 'https://www.google.de/',
     *                )
     */
    public function getReferrerSearchTerms($referrer)
    {
        $builder = $this->createVisitedReferrerBuilder()
            ->where('referrers.referer LIKE :selectedReferrer')
            ->setParameter('selectedReferrer', '%' . $referrer . '%');

        $builder = $this->eventManager->filter('Shopware_Analytics_ReferrerSearchTerms', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns an array which displays how much impressions and visits done
     * in the passed date range.
     * For each passed shop id within the shopIds parameter, the function returns two
     * additionally fields per line:
     *  1. impressions[shopId]
     *  2. visits[shopId]
     *
     * The described [shopId] placeholder, will be replaced with the passed shop id.
     * The sort parameter allows to sort the data result by different conditions.
     *
     * @param int   $offset
     * @param int   $limit
     * @param array $sort
     * @param int[] $shopIds
     *
     * @return Result
     *                array (
     *                'datum' => '2013-06-19',
     *                'totalImpressions' => '11043',
     *                'totalVisits' => '1633',
     *                'impressions1' => '11043',
     *                'visits1' => '1633',
     *                'impressions9' => '0',
     *                'visits9' => '0',
     *                ),
     *                array (
     *                'datum' => '2013-06-18',
     *                'totalImpressions' => '37328',
     *                'totalVisits' => '5149',
     *                'impressions1' => '37328',
     *                'visits1' => '5149',
     *                'impressions9' => '0',
     *                'visits9' => '0',
     *                )
     */
    public function getVisitorImpressions($offset, $limit, \DateTimeInterface $from = null, \DateTimeInterface $to = null, $sort = [], array $shopIds = [])
    {
        $builder = $this->createVisitorImpressionBuilder(
            $offset, $limit, $from, $to, $sort
        );

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $shopId = (int) $shopId;

                $builder->addSelect(
                    'SUM(IF(IF(shops.main_id is null, shops.id, shops.main_id)=' . $shopId . ", (CASE WHEN deviceType = 'desktop' THEN pageimpressions ELSE 0 END), 0)) as desktopImpressions" . $shopId
                );
                $builder->addSelect(
                    'SUM(IF(IF(shops.main_id is null, shops.id, shops.main_id)=' . $shopId . ", (CASE WHEN deviceType = 'tablet' THEN pageimpressions ELSE 0 END), 0)) as tabletImpressions" . $shopId
                );
                $builder->addSelect(
                    'SUM(IF(IF(shops.main_id is null, shops.id, shops.main_id)=' . $shopId . ", (CASE WHEN deviceType = 'mobile' THEN pageimpressions ELSE 0 END), 0)) as mobileImpressions" . $shopId
                );
                $builder->addSelect(
                    'SUM(IF(IF(shops.main_id is null, shops.id, shops.main_id)=' . $shopId . ', visitors.pageimpressions, 0)) as totalImpressions' . $shopId
                );

                $builder->addSelect(
                    'SUM(IF(IF(shops.main_id is null, shops.id, shops.main_id)=' . $shopId . ", (CASE WHEN deviceType = 'desktop' THEN uniquevisits ELSE 0 END), 0)) as desktopVisits" . $shopId
                );
                $builder->addSelect(
                    'SUM(IF(IF(shops.main_id is null, shops.id, shops.main_id)=' . $shopId . ", (CASE WHEN deviceType = 'tablet' THEN uniquevisits ELSE 0 END), 0)) as tabletVisits" . $shopId
                );
                $builder->addSelect(
                    'SUM(IF(IF(shops.main_id is null, shops.id, shops.main_id)=' . $shopId . ", (CASE WHEN deviceType = 'mobile' THEN uniquevisits ELSE 0 END), 0)) as mobileVisits" . $shopId
                );
                $builder->addSelect(
                    'SUM(IF(IF(shops.main_id is null, shops.id, shops.main_id)=' . $shopId . ', visitors.uniquevisits, 0)) as  totalVisits' . $shopId
                );
            }
        }

        $builder = $this->eventManager->filter('Shopware_Analytics_VisitorImpressions', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Selects the total amount for the passed date range.
     * The data will be grouped per country.
     *
     * For each passed shop id the query builder selects additionally the amount for the passed shop id
     * under the array key "amount[shopId]". The described [shopId] suffix will be replaced with the id of
     * the shop.
     *
     * @param int[] $shopIds
     *
     * @return Result
     *                array (
     *                'count' => '122',
     *                'amount' => '25423.620000000043',
     *                'displayDate' => 'Wednesday',
     *                'amount1' => '25423.620000000043',
     *                'amount2' => '0',
     *                'name' => 'Deutschland',
     *                ),
     */
    public function getAmountPerCountry(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('country.countryname AS name')
            ->groupBy('billing.countryID')
            ->orderBy('turnover', 'DESC');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerCountry', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Selects the total amount for the passed date range.
     * The data will be grouped per payment.
     *
     * For each passed shop id the query builder selects additionally the amount for the passed shop id
     * under the array key "amount[shopId]". The described [shopId] suffix will be replaced with the id of
     * the shop.
     *
     * @param int[] $shopIds
     *
     * @return Result
     *                array (
     *                'count' => '122',
     *                'amount' => '25423.620000000043',
     *                'displayDate' => 'Wednesday',
     *                'amount1' => '25423.620000000043',
     *                'amount2' => '0',
     *                'name' => 'Rechnung',
     *                ),
     */
    public function getAmountPerPayment(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('payment.description AS name')
            ->groupBy('orders.paymentID')
            ->orderBy('turnover', 'DESC');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerPayment', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Selects the total amount for the passed date range.
     * The data will be grouped per payment.
     *
     * For each passed shop id the query builder selects additionally the amount for the passed shop id
     * under the array key "amount[shopId]". The described [shopId] suffix will be replaced with the id of
     * the shop.
     *
     * @param int[] $shopIds
     *
     * @return Result
     *                array (
     *                'count' => '122',
     *                'amount' => '25423.620000000043',
     *                'displayDate' => 'Wednesday',
     *                'amount1' => '25423.620000000043',
     *                'amount2' => '0',
     *                'name' => 'Standard Versand',
     *                ),
     */
    public function getAmountPerShipping(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('dispatch.name AS name')
            ->groupBy('orders.dispatchID')
            ->orderBy('turnover', 'DESC');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerShipping', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Selects the total amount for the passed date range.
     * The data will be grouped per month.
     *
     * For each passed shop id the query builder selects additionally the amount for the passed shop id
     * under the array key "amount[shopId]". The described [shopId] suffix will be replaced with the id of
     * the shop.
     *
     * @param int[] $shopIds
     *
     * @return Result
     *                array (
     *                'count' => '2',
     *                'amount' => '403.72',
     *                'displayDate' => 'Saturday',
     *                'amount1' => '403.72',
     *                'amount2' => '0',
     *                'date' => '2000-07-01',
     *                ),
     *
     *      array (
     *          'count' => '1',
     *          'amount' => '201.86',
     *          'displayDate' => 'Saturday',
     *          'amount1' => '201.86',
     *          'amount2' => '0',
     *          'date' => '2001-10-01',
     *      ),
     */
    public function getAmountPerMonth(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $dateCondition = 'DATE_FORMAT(ordertime, \'%Y-%m-04\')';
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect($dateCondition . ' AS date')
            ->groupBy($dateCondition)
            ->orderBy('date', 'DESC');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerMonth', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Selects the total amount for the passed date range.
     * The data will be grouped per calender week.
     *
     * For each passed shop id the query builder selects additionally the amount for the passed shop id
     * under the array key "amount[shopId]". The described [shopId] suffix will be replaced with the id of
     * the shop.
     *
     * @param int[] $shopIds
     *
     * @return Result
     *                array (
     *                'count' => '1',
     *                'amount' => '201.86',
     *                'displayDate' => 'Saturday',
     *                'amount1' => '201.86',
     *                'amount2' => '0',
     *                'date' => '2000-07-06',
     *                ),
     *
     *      array (
     *          'count' => '1',
     *          'amount' => '201.86',
     *          'displayDate' => 'Monday',
     *          'amount1' => '201.86',
     *          'amount2' => '0',
     *          'date' => '2000-07-27',
     *      ),
     */
    public function getAmountPerCalendarWeek(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $dateCondition = 'DATE_SUB(DATE(ordertime), INTERVAL WEEKDAY(ordertime)-3 DAY)';
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect($dateCondition . ' AS date')
            ->groupBy($dateCondition)
            ->orderBy('date', 'DESC');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerWeek', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Selects the total amount for the passed date range.
     * The data will be grouped per week day.
     *
     * For each passed shop id the query builder selects additionally the amount for the passed shop id
     * under the array key "amount[shopId]". The described [shopId] suffix will be replaced with the id of
     * the shop.
     *
     * @param int[] $shopIds
     *
     * @return Result
     *                array (
     *                'count' => '8',
     *                'amount' => '1614.88',
     *                'displayDate' => 'Saturday',
     *                'amount1' => '1614.88',
     *                'amount2' => '0',
     *                'date' => '2000-07-08',
     *                ),
     *
     *      array (
     *          'count' => '9',
     *          'amount' => '1816.7400000000002',
     *          'displayDate' => 'Monday',
     *          'amount1' => '1816.7400000000002',
     *          'amount2' => '0',
     *          'date' => '2002-07-15',
     *      ),
     */
    public function getAmountPerWeekday(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('DATE_FORMAT(ordertime, \'%Y-%m-%d\') AS date')
            ->groupBy('WEEKDAY(ordertime)')
            ->orderBy('WEEKDAY(ordertime)', 'ASC');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerWeekday', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Selects the total amount for the passed date range.
     * The data will be grouped per hour.
     *
     * For each passed shop id the query builder selects additionally the amount for the passed shop id
     * under the array key "amount[shopId]". The described [shopId] suffix will be replaced with the id of
     * the shop.
     *
     * @param int[] $shopIds
     *
     * @return Result
     *                array (
     *                'count' => '2',
     *                'amount' => '403.72',
     *                'displayDate' => 'Saturday',
     *                'amount1' => '403.72',
     *                'amount2' => '0',
     *                'date' => '1970-01-01 00:00:00',
     *                ),
     *
     *      array (
     *          'count' => '2',
     *          'amount' => '403.72',
     *          'displayDate' => 'Thursday',
     *          'amount1' => '403.72',
     *          'amount2' => '0',
     *          'date' => '1970-01-01 01:00:00',
     *      ),
     */
    public function getAmountPerHour(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $dateCondition = 'DATE_FORMAT(ordertime, \'1970-01-01 %H:00:00\')';

        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect($dateCondition . ' AS date')
            ->groupBy($dateCondition)
            ->orderBy('date');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerHour', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * For each passed shop id the query builder selects additionally the article impression for the passed shop id
     * under the array key "amount[shopId]". The described [shopId] suffix will be replaced with the id of
     * the shop.
     *
     * @param int   $offset
     * @param int   $limit
     * @param int[] $shopIds
     *
     * @return Result
     *                array (
     *                'articleId' => '213',
     *                'articleName' => 'Surfbrett',
     *                'date' => '1355353200',
     *                'totalAmount' => '1',
     *                'amount1' => '1',
     *                'amount2' => '0',
     *                ),
     *
     *      array (
     *          'articleId' => '162',
     *          'articleName' => 'Sommer-Sandale Pink',
     *          'date' => '1355612400',
     *          'totalAmount' => '1',
     *          'amount1' => '1',
     *          'amount2' => '0',
     *      ),
     */
    public function getProductImpressions($offset, $limit, \DateTimeInterface $from = null, \DateTimeInterface $to = null, array $sort = [], array $shopIds = [])
    {
        $builder = $this->createProductImpressionBuilder($offset, $limit);

        if ($from) {
            $builder->andWhere('articleImpression.date >= :fromDate')
                ->setParameter(':fromDate', $from->format('Y-m-d H:i:s'));
        }
        if ($to) {
            $builder->andWhere('articleImpression.date <= :toDate')
                ->setParameter(':toDate', $to->format('Y-m-d H:i:s'));
        }
        if ($sort) {
            $this->addSort($builder, $sort);
        }
        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $shopId = (int) $shopId;
                $builder->addSelect(
                    'SUM(IF(articleImpression.shopId = ' . $shopId . ', articleImpression.impressions, 0)) as totalImpressions' . $shopId
                );
            }
        }

        $builder = $this->eventManager->filter('Shopware_Analytics_ProductImpressions', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns a result which displays the total order amount per customer gorup.
     *
     * @param int[] $shopIds
     *
     * @return Result
     */
    public function getCustomerGroupAmount(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $builder = $this->createCustomerGroupAmountBuilder($from, $to, $shopIds);

        $builder = $this->eventManager->filter('Shopware_Analytics_CustomerGroupAmount', $builder, [
            'subject' => $this,
        ]);

        return new Result($builder);
    }

    /**
     * Returns a dbal query builder which displays the total orders of each
     * day for the passed date range.
     * If shop ids passed, the data result contains additionally for each shop id
     * a data result column like "orderCount1" for "orderCount" + shop id.
     *
     * The data array is indexed by the order date. To remove the useless array level
     * execute the following code:
     *      $orders = $repository->getDailyShopOrders(... , ...);
     *      $orders = array_map('reset', $orders->getData());
     *
     * @param int[] $shopIds
     *
     * @return DBALQueryBuilder
     */
    protected function createDailyShopOrderBuilder(\DateTimeInterface $from, \DateTimeInterface $to, array $shopIds)
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select([
            'DATE(orders.ordertime) as orderTime',

            'SUM( IF(
                orders.status NOT IN (-1, 4),
                1, 0
            )) as orderCount',

            'SUM( IF(
                orders.status = -1,
                1, 0
            )) as cancelledOrders',
        ]);

        foreach ($shopIds as $shopId) {
            $shopId = (int) $shopId;
            $builder->addSelect(
                'SUM( IF(
                    orders.language = ' . $shopId . ' AND orders.status NOT IN (-1, 4),
                    1, 0
                )) as orderCount' . $shopId
            );

            $builder->addSelect(
                'SUM( IF(
                    orders.language = ' . $shopId . ' AND orders.status = -1,
                    1, 0
                )) cancelledOrders' . $shopId
            );
        }

        $builder->from('s_order', 'orders')
            ->orderBy('orders.ordertime', 'DESC')
            ->groupBy('DATE(orders.ordertime)');

        $this->addDateRangeCondition($builder, $from, $to, 'DATE(orders.ordertime)');

        return $builder;
    }

    /**
     * Creates a query builder which selects the turnover and order count of each
     * day for the passed date range.
     *
     * The data array is indexed by the order date. To remove the useless array level
     * execute the following code:
     *      $turnover = $repository->getDailyTurnover(... , ...);
     *      $turnover = array_map('reset', $turnover->getData());
     *
     * @return DBALQueryBuilder
     */
    protected function createDailyTurnoverBuilder(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select([
            'DATE(orders.ordertime) as orderTime',
            'COUNT(orders.id) as orderCount',
            'SUM(orders.invoice_amount / orders.currencyFactor) as turnover',
        ]);

        $builder->from('s_order', 'orders')
            ->where('orders.status NOT IN (-1, 4)')
            ->orderBy('DATE(orders.ordertime)', 'DESC')
            ->groupBy('DATE(orders.ordertime)');

        $this->addDateRangeCondition($builder, $from, $to, 'orderTime');

        return $builder;
    }

    /**
     * Creates a query builder which selects the total visits for each day of the
     * passed date range.
     *
     * The data array is indexed by the order date. To remove the useless array level
     * execute the following code:
     *      $visitors = $repository->getDailyVisitors(... , ...);
     *      $visitors = array_map('reset', $visitors->getData());
     *
     * @return DBALQueryBuilder
     */
    protected function createDailyVisitorsBuilder(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select([
            'visitor.datum AS date',
            'SUM(visitor.pageimpressions) AS clicks',
            'SUM(visitor.uniquevisits) as visits',
        ]);

        $builder->from('s_statistics_visitors', 'visitor')
            ->orderBy('visitor.datum', 'DESC')
            ->groupBy('visitor.datum');

        $this->addDateRangeCondition($builder, $from, $to, 'visitor.datum');

        return $builder;
    }

    /**
     * Creates a query builder which selects the total registrations for each
     * day of the passed date range.
     *
     * The data array is indexed by the order date. To remove the useless array level
     * execute the following code:
     *      $registrations = $repository->getDailyRegistrations(... , ...);
     *      $registrations = array_map('reset', $registrations->getData());
     *
     * @return DBALQueryBuilder
     */
    protected function createDailyRegistrationsBuilder(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select([
            'firstlogin as firstLogin',
            'COUNT(users.id) as registrations',
            'COUNT(orders.id) as customers',
        ]);

        $builder->from('s_user', 'users')
            ->leftJoin('users', 's_order', 'orders',
                'orders.userID = users.id AND (DATE(orders.ordertime) = DATE(users.firstlogin)) AND orders.status NOT IN (-1, 4)'
            )
            ->orderBy('users.firstlogin', 'DESC')
            ->groupBy('users.firstlogin');

        $this->addDateRangeCondition($builder, $from, $to, 'users.firstlogin');

        return $builder;
    }

    /**
     * Returns a result which displays the impressions of each product.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return DBALQueryBuilder
     */
    protected function createProductImpressionBuilder($offset, $limit, array $sort = [])
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select([
            'articleImpression.articleId',
            'article.name as articleName',
            'SUM(CASE WHEN deviceType = "desktop" THEN impressions ELSE 0 END) as desktopImpressions',
            'SUM(CASE WHEN deviceType = "tablet" THEN impressions ELSE 0 END) as tabletImpressions',
            'SUM(CASE WHEN deviceType = "mobile" THEN impressions ELSE 0 END) as mobileImpressions',
            'SUM(articleImpression.impressions) as totalImpressions',
        ]);

        $builder->from('s_statistics_article_impression', 'articleImpression')
            ->leftJoin('articleImpression', 's_articles', 'article', 'articleImpression.articleId = article.id')
            ->addGroupBy('articleImpression.articleId');

        $this->addSort($builder, $sort)
            ->addPagination($builder, $offset, $limit);

        return $builder;
    }

    /**
     * This function creates a DBAL query builder, which used to determine the product sale value per order.
     * This is used to display, for example, how much revenue bring the products to a category or a manufacturer.
     *
     * @return DBALQueryBuilder
     */
    protected function createProductAmountBuilder(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select([
            'COUNT(DISTINCT orders.id) AS orderCount',
            'SUM((details.price * details.quantity)/currencyFactor) AS turnover',
        ])
            ->from('s_order', 'orders')
            ->innerJoin('orders', 's_order_details', 'details', 'orders.id = details.orderID AND details.modus=0')
            ->innerJoin('details', 's_articles', 'articles', 'details.articleID = articles.id')
            ->where('orders.status NOT IN (4, -1)')
            ->orderBy('name');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        return $builder;
    }

    /**
     * Creates a query that selects the number of orders and their total sales for the passed date range.
     * It is used to display how much revenue per month, week or day is received.
     *
     * For each passed shop id the query builder selects additionally the amount for the passed shop id
     * under the array key "amount[shopId]". The described [shopId] suffix will be replaced with the id of
     * the shop.
     *
     * @param int[] $shopIds
     *
     * @return DBALQueryBuilder
     *                          array (
     *                          'count' => '386109',
     *                          'amount' => '22637520.4061901',
     *                          'displayDate' => 'Monday',
     *                          ),
     */
    protected function createAmountBuilder(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select([
            'COUNT(orders.id) AS orderCount',
            'SUM(orders.invoice_amount / orders.currencyFactor) AS turnover',
            'Date_Format(orders.ordertime, \'%W\') as displayDate',
        ]);

        $builder->from('s_order', 'orders')
            ->leftJoin('orders', 's_premium_dispatch', 'dispatch', 'orders.dispatchID = dispatch.id')
            ->leftJoin('orders', 's_core_paymentmeans', 'payment', 'orders.paymentID = payment.id')
            ->leftJoin('orders', 's_order_billingaddress', 'billing', 'orders.id = billing.orderID')
            ->leftJoin('billing', 's_core_countries', 'country', 'billing.countryID = country.id')
            ->where('orders.status NOT IN (4, -1)');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $shopId = (int) $shopId;
                $builder->addSelect(
                    'SUM(IF(orders.language=' . $shopId . ', invoice_amount / currencyFactor, 0)) as turnover' . $shopId
                );
                $builder->addSelect(
                    'SUM(orders.language=' . $shopId . ') as orderCount' . $shopId
                );
            }
        }

        return $builder;
    }

    /**
     * Returns an query builder, which selects how much impressions and visits done
     * in the passed date range.
     * The sort parameter allows to sort the data result by different conditions.
     *
     * @param int                $offset
     * @param int                $limit
     * @param \DateTimeInterface $from
     * @param \DateTimeInterface $to
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function createVisitorImpressionBuilder($offset, $limit, \DateTimeInterface $from = null, \DateTimeInterface $to = null, array $sort = [])
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select([
            'visitors.datum',
            'SUM(CASE WHEN deviceType = "desktop" THEN pageimpressions ELSE 0 END) as desktopImpressions',
            'SUM(CASE WHEN deviceType = "tablet" THEN pageimpressions ELSE 0 END) as tabletImpressions',
            'SUM(CASE WHEN deviceType = "mobile" THEN pageimpressions ELSE 0 END) as mobileImpressions',
            'SUM(visitors.pageimpressions) AS totalImpressions',
            'SUM(CASE WHEN deviceType = "desktop" THEN uniquevisits ELSE 0 END) as desktopVisits',
            'SUM(CASE WHEN deviceType = "tablet" THEN uniquevisits ELSE 0 END) as tabletVisits',
            'SUM(CASE WHEN deviceType = "mobile" THEN uniquevisits ELSE 0 END) as mobileVisits',
            'SUM(visitors.uniquevisits) AS totalVisits',
        ]);

        $builder->from('s_statistics_visitors', 'visitors')
            ->leftJoin('visitors', 's_core_shops', 'shops', 'visitors.shopID = shops.id')
            ->groupBy('visitors.datum');

        $this->addSort($builder, $sort)
            ->addDateRangeCondition($builder, $from, $to, 'datum')
            ->addPagination($builder, $offset, $limit);

        return $builder;
    }

    /**
     * Returns a query builder which selects the age of each customer.
     *
     * @param int[] $shopIds
     *
     * @return DBALQueryBuilder
     */
    protected function createAgeOfCustomersBuilder(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $builder = $builder = $this->connection->createQueryBuilder();
        $builder->select([
            'users.firstlogin as firstLogin',
            'users.birthday',
        ])
            ->from('s_user', 'users')
            ->andWhere('users.birthday IS NOT NULL')
            ->andWhere("users.birthday != '0000-00-00'")
            ->orderBy('users.birthday', 'DESC');

        $this->addDateRangeCondition($builder, $from, $to, 'users.firstlogin');

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $shopId = (int) $shopId;
                $builder->addSelect(
                    "IF(users.subshopID = {$shopId}, users.birthday, NULL) as birthday" . $shopId
                );
            }
        }

        return $builder;
    }

    /**
     * Returns a query which displays how many orders are each customer done.
     *
     * @return DBALQueryBuilder
     */
    protected function createOrdersOfCustomersBuilder(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select([
            'DATE(orders.ordertime) as orderTime',
            '(DATE(orders.ordertime) = DATE(users.firstlogin)) as isNewCustomerOrder',
            'billing.salutation',
            'orders.userID as userId',
        ]);
        $builder->from('s_order', 'orders')
            ->innerJoin('orders', 's_user', 'users', 'orders.userID = users.id')
            ->innerJoin('users', 's_user_addresses', 'billing', 'billing.id = users.default_billing_address_id and billing.user_id = users.id')
            ->andWhere('orders.status NOT IN (-1, 4)')
            ->orderBy('orderTime', 'ASC');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        return $builder;
    }

    /**
     * Returns a query which selects the sell count of each product.
     *
     * @return DBALQueryBuilder
     */
    protected function createProductSalesBuilder(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $builder = $this->connection->createQueryBuilder();
        $builder->select([
            'SUM(details.quantity) AS sales',
            'articles.name',
            'details.articleordernumber as ordernumber',
        ])
            ->from('s_order_details', 'details')
            ->innerJoin('details', 's_articles', 'articles', 'articles.id = details.articleID')
            ->innerJoin('details', 's_order', 'orders', 'orders.id = details.orderID')
            ->andWhere('orders.status NOT IN (-1, 4)')
            ->andWhere('details.modus = 0')
            ->groupBy('articles.id')
            ->orderBy('sales', 'DESC');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        return $builder;
    }

    /**
     * Returns a query which selects the revenue of each partner.
     *
     * @return DBALQueryBuilder
     */
    protected function createPartnerRevenueBuilder(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $builder = $this->connection->createQueryBuilder();
        $builder->select([
            'SUM(orders.invoice_amount / orders.currencyFactor) AS turnover',
            'partners.company AS partner',
            'orders.partnerID as trackingCode',
            'partners.id as partnerId',
        ])
            ->from('s_order', 'orders')
            ->leftJoin('orders', 's_emarketing_partner', 'partners', 'partners.idcode = orders.partnerID')
            ->where('orders.status NOT IN (-1, 4)')
            ->andWhere("orders.partnerID != ''")
            ->groupBy('orders.partnerID')
            ->orderBy('turnover', 'DESC');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        return $builder;
    }

    /**
     * Returns a query which selects the revenue of each referrer.
     *
     * @return DBALQueryBuilder
     */
    protected function createReferrerRevenueBuilder(Shop $shop = null, \DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $builder = $this->connection->createQueryBuilder();
        $builder->select([
            'ROUND(orders.invoice_amount / orders.currencyFactor, 2) AS turnover',
            'users.id as userID',
            'orders.referer AS referrer',
            'DATE(users.firstlogin) as firstLogin',
            'DATE(orders.ordertime) as orderTime',
        ])
            ->from('s_order', 'orders')
            ->innerJoin('orders', 's_user', 'users', 'orders.userID = users.id')
            ->where('orders.status != 4 AND orders.status != -1')
            ->andWhere("orders.referer LIKE 'http%//%'")
            ->orderBy('turnover');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        if ($shop instanceof Shop && $shop->getHost()) {
            $builder->andWhere('orders.referer NOT LIKE :hostname')
                ->setParameter(':hostname', '%' . $shop->getHost() . '%');
        }

        return $builder;
    }

    /**
     * Returns a query which displays how many visits comes from each referrer.
     *
     * @return DBALQueryBuilder
     */
    protected function createVisitedReferrerBuilder(\DateTimeInterface $from = null, \DateTimeInterface $to = null)
    {
        $builder = $builder = $this->connection->createQueryBuilder();
        $builder->select([
            'COUNT(referrers.referer) as count',
            'referrers.referer as referrer',
        ])
            ->from('s_statistics_referer', 'referrers')
            ->groupBy('referer')
            ->orderBy('count', 'DESC');

        $this->addDateRangeCondition($builder, $from, $to, 'referrers.datum');

        return $builder;
    }

    /**
     * Returns a query which selects the total order amount of each customer group.
     *
     * @param int[] $shopIds
     *
     * @return DBALQueryBuilder
     *                          array (
     *                          'count' => '386109',
     *                          'amount' => '22637520.4061901',
     *                          'displayDate' => 'Monday',
     *                          ),
     */
    protected function createCustomerGroupAmountBuilder(\DateTimeInterface $from = null, \DateTimeInterface $to = null, array $shopIds = [])
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('customerGroups.description as customerGroup')
            ->innerJoin('orders', 's_user', 'users', 'users.id = orders.userID')
            ->innerJoin('users', 's_core_customergroups', 'customerGroups', 'users.customergroup = customerGroups.groupkey')
            ->orderBy('turnover', 'DESC')
            ->groupBy('users.customergroup');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        return $builder;
    }

    /**
     * Helper function which adds the date range condition to an aggregate order query.
     *
     * @param string|null $column
     *
     * @return $this
     */
    private function addDateRangeCondition(DBALQueryBuilder $builder, \DateTimeInterface $from = null, \DateTimeInterface $to = null, $column = null)
    {
        if ($from instanceof \DateTimeInterface) {
            $builder->andWhere($column . ' >= :fromDate')
                ->setParameter('fromDate', $from->format('Y-m-d H:i:s'));
        }
        if ($to instanceof \DateTimeInterface) {
            $builder->andWhere($column . ' <= :toDate')
                ->setParameter('toDate', $to->format('Y-m-d H:i:s'));
        }

        return $this;
    }

    /**
     * Helper function which iterates all sort arrays and at them as order by condition.
     *
     * @param array $sort
     *
     * @return Repository
     */
    private function addSort(DBALQueryBuilder $builder, $sort)
    {
        if (empty($sort)) {
            return $this;
        }

        foreach ($sort as $condition) {
            $builder->addOrderBy(
                $condition['property'],
                $condition['direction']
            );
        }

        return $this;
    }

    /**
     * Small helper function which adds the first and max result to the query builder.
     *
     * @param int $offset
     * @param int $limit
     *
     * @return Repository
     */
    private function addPagination(DBALQueryBuilder $builder, $offset, $limit)
    {
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $this;
    }
}
