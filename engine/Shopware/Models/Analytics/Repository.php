<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Models
 * @subpackage Article
 */

namespace Shopware\Models\Analytics;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Shopware\Components\Model\DBAL\Result;
use Shopware\Models\Shop\Shop;

/**
 * Class Repository
 * @package Shopware\Models\Analytics
 */
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

    /**
     * Class constructor which allows to inject all dependencies of this class.
     *
     * @param Connection $connection
     * @param \Enlight_Event_EventManager $eventManager
     */
    function __construct(Connection $connection, \Enlight_Event_EventManager $eventManager)
    {
        $this->connection = $connection;
        $this->eventManager = $eventManager;
    }

    public function getCustomerGroupAmount(\DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->createCustomerGroupAmountBuilder($from, $to);

        $builder = $this->eventManager->filter('Shopware_Analytics_CustomerGroupAmount', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     * Returns a statistic array for the whole shop data.
     *
     * @param $offset
     * @param $limit
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Result
     *      array (
     *          'date' => '2012-08-28',
     *          'visitors' => '6',
     *          'orderCount' => '0',
     *          'cancelledOrders' => '0',
     *          'clicks' => '473',
     *          'totalVisits' => '6',
     *          'revenue' => NULL,
     *          'totalOrders' => '0',
     *          'newCustomers' => '0',
     *      ),
     *      array (
     *          'date' => '2012-08-29',
     *          'visitors' => '6',
     *          'orderCount' => '0',
     *          'cancelledOrders' => '0',
     *          'clicks' => '1279',
     *          'totalVisits' => '6',
     *          'revenue' => NULL,
     *          'totalOrders' => '0',
     *          'newCustomers' => '0',
     *      )
     */
    public function getShopStatistic($offset, $limit, \DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->createShopStatisticBuilder($from, $to);

        $this->addPagination($builder, $offset, $limit);

        $builder = $this->eventManager->filter('Shopware_Analytics_ShopStatistic', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Result
     *      array (
     *          'date' => '2012-08-28',
     *          'visitors' => '6',
     *          'orderCount' => '0',
     *          'cancelledOrders' => '0',
     *      ),
     *
     *      array (
     *          'date' => '2012-08-29',
     *          'visitors' => '6',
     *          'orderCount' => '0',
     *          'cancelledOrders' => '0',
     *      ),
     */
    public function getOrdersOfVisitors(\DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->createOrdersOfVisitorsBuilder($from, $to);

        $builder = $this->eventManager->filter('Shopware_Analytics_OrdersOfVisitors', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     * @param $offset
     * @param $limit
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Result
     */
    public function getVisitedReferrer($offset, $limit, \DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->createVisitedReferrerBuilder($from, $to);

        $this->addPagination($builder, $offset, $limit);

        $builder = $this->eventManager->filter('Shopware_Analytics_VisitedReferrer', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     * @param Shop $shop
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Result
     */
    public function getReferrerRevenue(Shop $shop, \DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->createReferrerRevenueBuilder($shop, $from, $to);

        $builder = $this->eventManager->filter('Shopware_Analytics_ReferrerRevenue', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     * @param $offset
     * @param $limit
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Result
     */
    public function getPartnerRevenue($offset, $limit, \DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->createPartnerRevenueBuilder($from, $to);

        $this->addPagination($builder, $offset, $limit);

        $builder = $this->eventManager->filter('Shopware_Analytics_PartnerRevenue', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     * @param $offset
     * @param $limit
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Result
     *      array (
     *          'sellCount' => '243',
     *          'name' => 'ESD Download Artikel',
     *          'ordernumber' => 'SW10196',
     *      ),
     *
     *      array (
     *          'sellCount' => '121',
     *          'name' => 'Aufschlag bei Zahlungsarten',
     *          'ordernumber' => 'SW10002841',
     *      ),
     */
    public function getProductSells($offset, $limit, \DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->createProductSellsBuilder($from, $to);

        $this->addPagination($builder, $offset, $limit);

        $builder = $this->eventManager->filter('Shopware_Analytics_ProductSells', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Result
     *      array (
     *          'firstLogin' => '2012-08-30',
     *          'orderTime' => '2013-12-18 14:46:24',
     *          'count' => '1',
     *          'salutation' => 'company',
     *      ),
     *
     *      array (
     *          'firstLogin' => '2011-11-23',
     *          'orderTime' => '2013-11-15 14:46:24',
     *          'count' => '121',
     *          'salutation' => 'mr',
     *      ),
     */
    public function getOrdersOfCustomers(\DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->createOrdersOfCustomersBuilder($from, $to);

        $builder = $this->eventManager->filter('Shopware_Analytics_OrdersOfCustomers', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $shopIds
     * @return Result
     */
    public function getAgeOfCustomers(\DateTime $from = null, \DateTime $to = null, array $shopIds = array())
    {
        $builder = $this->createAgeOfCustomersBuilder($from, $to, $shopIds);

        $builder = $this->eventManager->filter('Shopware_Analytics_AgeOfCustomers', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     * Returns an array representing the product sales per category.
     * The number of orders and the entire order is returned.
     * The "from" and "to" parameter allows to restrict the result to a specify
     * date range.
     * The "categoryId" allows to restrict the result to a specify category level.
     *
     * @param $categoryId
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Result
     *   array (
     *       'count' => '122',
     *       'amount' => '24656.19400000029',
     *       'name' => 'Deutsch',
     *       'node' => '3',
     *   ),
     *   array (
     *       'count' => '122',
     *       'amount' => '24656.19400000029',
     *       'name' => 'English',
     *       'node' => '39',
     *   ),
     *
     */
    public function getProductAmountPerCategory($categoryId, \DateTime $from = null, \DateTime $to = null)
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

        $builder = $this->eventManager->filter('Shopware_Analytics_ProductAmountPerCategory', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Result
     *      array (
     *         'count' => '122',
     *         'amount' => '9303.713999999969',
     *         'name' => 'Beachdreams Clothes',
     *      ),
     *      array (
     *         'count' => '121',
     *         'amount' => '15352.479999999925',
     *         'name' => 'Example',
     *      )
     */
    public function getProductAmountPerManufacturer(\DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->createProductAmountBuilder($from, $to)
            ->addSelect('suppliers.name')
            ->leftJoin('articles', 's_articles_supplier', 'suppliers', 'articles.supplierID = suppliers.id')
            ->groupBy('articles.supplierID')
            ->orderBy('suppliers.name');

        $builder = $this->eventManager->filter('Shopware_Analytics_ProductAmountPerManufacturer', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     * Returns an array which displays which search term executed in the shop.
     * The data result contains the executed search term, the count of request
     * which sends this search term and how many result are returned for this term.
     *
     * @param int $offset Numeric value which defines the query start page.
     * @param int $limit Numeric value which defines the query limit.
     * @param array $sort
     * @internal param array $orderBy Expects a two dimensional array with additionally order by conditions
     * @return Result
     *      array (
     *          'countRequests' => '90',
     *          'searchterm' => 'iphone',
     *          'countResults' => '1401',
     *      ),
     *      array (
     *          'countRequests' => '63',
     *          'searchterm' => 'ipho',
     *          'countResults' => '1390',
     *      )
     */
    public function getSearchTerms($offset, $limit, $sort = array())
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select(array(
            'COUNT(search.searchterm) AS countRequests',
            'search.searchterm',
            'MAX(search.results) as countResults'
        ))
            ->from('s_statistics_search', 'search')
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

        $builder = $this->eventManager->filter('Shopware_Analytics_SearchTerms', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    public function getReferrerUrls($referrer, $offset, $limit)
    {
        $builder = $this->createVisitedReferrerBuilder()
            ->where('referrers.referer LIKE :selectedReferrer')
            ->setParameter('selectedReferrer', '%' . $referrer . '%');

        $this->addPagination($builder, $offset, $limit);

        $builder = $this->eventManager->filter('Shopware_Analytics_ReferrerUrls', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    public function getReferrerSearchTerms($referrer)
    {
        $builder = $this->createVisitedReferrerBuilder()
            ->where('referrers.referer LIKE :selectedReferrer')
            ->setParameter('selectedReferrer', '%' . $referrer . '%');

        $builder = $this->eventManager->filter('Shopware_Analytics_ReferrerSearchTerms', $builder, array(
            'subject' => $this
        ));

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
     * @param \DateTime $from
     * @param \DateTime $to
     * @param $offset
     * @param $limit
     * @param array $sort
     * @param array $shopIds
     *
     * @return Result
     *      array (
     *          'datum' => '2013-06-19',
     *          'totalImpressions' => '11043',
     *          'totalVisits' => '1633',
     *          'impressions1' => '11043',
     *          'visits1' => '1633',
     *          'impressions9' => '0',
     *          'visits9' => '0',
     *      ),
     *      array (
     *          'datum' => '2013-06-18',
     *          'totalImpressions' => '37328',
     *          'totalVisits' => '5149',
     *          'impressions1' => '37328',
     *          'visits1' => '5149',
     *          'impressions9' => '0',
     *          'visits9' => '0',
     *      )
     */
    public function getVisitorImpressions($offset, $limit, \DateTime $from = null, \DateTime $to = null, $sort = array(), array $shopIds = array())
    {
        $builder = $this->createVisitorImpressionBuilder(
            $offset, $limit, $from, $to, $sort
        );

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $shopId = (int)$shopId;

                $builder->addSelect(
                    "SUM(IF(IF(shops.main_id is null, shops.id, shops.main_id)=" . $shopId . ", visitors.pageimpressions, 0)) as impressions" . $shopId
                );

                $builder->addSelect(
                    "SUM(IF(IF(shops.main_id is null, shops.id, shops.main_id)=" . $shopId . ", visitors.uniquevisits, 0)) as  visits" . $shopId
                );
            }
        }

        $builder = $this->eventManager->filter('Shopware_Analytics_VisitorImpressions', $builder, array(
            'subject' => $this
        ));

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
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $shopIds
     * @return Result
     *      array (
     *          'count' => '122',
     *          'amount' => '25423.620000000043',
     *          'displayDate' => 'Wednesday',
     *          'amount1' => '25423.620000000043',
     *          'amount2' => '0',
     *          'name' => 'Deutschland',
     *      ),
     */
    public function getAmountPerCountry(\DateTime $from = null, \DateTime $to = null, array $shopIds = array())
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('country.countryname AS name')
            ->groupBy('billing.countryID')
            ->orderBy('name');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerCountry', $builder, array(
            'subject' => $this
        ));

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
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $shopIds
     * @return Result
     *      array (
     *          'count' => '122',
     *          'amount' => '25423.620000000043',
     *          'displayDate' => 'Wednesday',
     *          'amount1' => '25423.620000000043',
     *          'amount2' => '0',
     *          'name' => 'Rechnung',
     *      ),
     */
    public function getAmountPerPayment(\DateTime $from = null, \DateTime $to = null, array $shopIds = array())
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('payment.description AS name')
            ->groupBy('orders.paymentID')
            ->orderBy('name');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerPayment', $builder, array(
            'subject' => $this
        ));

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
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $shopIds
     * @return Result
     *      array (
     *          'count' => '122',
     *          'amount' => '25423.620000000043',
     *          'displayDate' => 'Wednesday',
     *          'amount1' => '25423.620000000043',
     *          'amount2' => '0',
     *          'name' => 'Standard Versand',
     *      ),
     */
    public function getAmountPerShipping(\DateTime $from = null, \DateTime $to = null, array $shopIds = array())
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('dispatch.name AS name')
            ->groupBy('orders.dispatchID')
            ->orderBy('dispatch.name');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerShipping', $builder, array(
            'subject' => $this
        ));

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
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $shopIds
     * @return Result
     *      array (
     *          'count' => '2',
     *          'amount' => '403.72',
     *          'displayDate' => 'Saturday',
     *          'amount1' => '403.72',
     *          'amount2' => '0',
     *          'date' => '2000-07-01',
     *      ),
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
    public function getAmountPerMonth(\DateTime $from = null, \DateTime $to = null, array $shopIds = array())
    {
        $dateCondition = 'DATE_FORMAT(ordertime, \'%Y-%m-01\')';
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect($dateCondition . ' AS date')
            ->groupBy($dateCondition)
            ->orderBy('date');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerMonth', $builder, array(
            'subject' => $this
        ));

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
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $shopIds
     * @return Result
     *      array (
     *          'count' => '1',
     *          'amount' => '201.86',
     *          'displayDate' => 'Saturday',
     *          'amount1' => '201.86',
     *          'amount2' => '0',
     *          'date' => '2000-07-06',
     *      ),
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
    public function getAmountPerCalendarWeek(\DateTime $from = null, \DateTime $to = null, array $shopIds = array())
    {
        $dateCondition = 'DATE_SUB(DATE(ordertime), INTERVAL WEEKDAY(ordertime)-3 DAY)';
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect($dateCondition . ' AS date')
            ->groupBy($dateCondition)
            ->orderBy('date');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerWeek', $builder, array(
            'subject' => $this
        ));

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
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $shopIds
     * @return Result
     *      array (
     *          'count' => '8',
     *          'amount' => '1614.88',
     *          'displayDate' => 'Saturday',
     *          'amount1' => '1614.88',
     *          'amount2' => '0',
     *          'date' => '2000-07-08',
     *      ),
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
    public function getAmountPerWeekday(\DateTime $from = null, \DateTime $to = null, array $shopIds = array())
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('DATE_FORMAT(ordertime, \'%Y-%m-%d\') AS date')
            ->groupBy('WEEKDAY(ordertime)')
            ->orderBy('date');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerWeekday', $builder, array(
            'subject' => $this
        ));

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
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $shopIds
     * @return Result
     *      array (
     *          'count' => '2',
     *          'amount' => '403.72',
     *          'displayDate' => 'Saturday',
     *          'amount1' => '403.72',
     *          'amount2' => '0',
     *          'date' => '1970-01-01 00:00:00',
     *      ),
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
    public function getAmountPerHour(\DateTime $from = null, \DateTime $to = null, array $shopIds = array())
    {
        $dateCondition = 'DATE_FORMAT(ordertime, \'1970-01-01 %H:00:00\')';

        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect($dateCondition . ' AS date')
            ->groupBy($dateCondition)
            ->orderBy('date');

        $builder = $this->eventManager->filter('Shopware_Analytics_AmountPerHour', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }

    /**
     *
     * For each passed shop id the query builder selects additionally the article impression for the passed shop id
     * under the array key "amount[shopId]". The described [shopId] suffix will be replaced with the id of
     * the shop.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @param $offset
     * @param $limit
     * @param array $sort
     * @param array $shopIds
     * @return Result
     *      array (
     *          'articleId' => '213',
     *          'articleName' => 'Surfbrett',
     *          'date' => '1355353200',
     *          'totalAmount' => '1',
     *          'amount1' => '1',
     *          'amount2' => '0',
     *      ),
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
    public function getProductImpressions($offset, $limit, \DateTime $from = null, \DateTime $to = null, array $sort = array(), array $shopIds = array())
    {
        $builder = $this->createProductImpressionBuilder($offset, $limit);

        if ($from) {
            $builder->andWhere('articleImpression.date >= :fromDate')
                ->setParameter(':fromDate', $from->format("Y-m-d H:i:s"));
        }
        if ($to) {
            $builder->andWhere('articleImpression.date <= :toDate')
                ->setParameter(':toDate', $to->format("Y-m-d H:i:s"));
        }
        if ($sort) {
            $this->addSort($builder, $sort);
        }
        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $shopId = (int)$shopId;
                $builder->addSelect(
                    'SUM(IF(articleImpression.shopId = ' . $shopId . ', articleImpression.impressions, 0)) as amount' . $shopId
                );
            }
        }

        $builder = $this->eventManager->filter('Shopware_Analytics_ProductImpressions', $builder, array(
            'subject' => $this
        ));

        return new Result($builder);
    }


    /**
     * @param $offset
     * @param $limit
     * @param array $sort
     * @return DBALQueryBuilder
     */
    protected function createProductImpressionBuilder($offset, $limit, array $sort = array())
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'articleImpression.articleId',
            'article.name as articleName',
            'UNIX_TIMESTAMP(articleImpression.date) as date',
            'SUM(articleImpression.impressions) as totalAmount'
        ));

        $builder->from('s_statistics_article_impression', 'articleImpression')
            ->leftJoin('articleImpression', 's_articles', 'article', 'articleImpression.articleId = article.id')
            ->addGroupBy('articleImpression.date');

        $this->addSort($builder, $sort)
            ->addPagination($builder, $offset, $limit);

        return $builder;
    }

    /**
     * This function creates a DBAL query builder, which used to determine the product sale value per order.
     * This is used to display, for example, how much revenue bring the products to a category or a manufacturer.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return DBALQueryBuilder
     */
    protected function createProductAmountBuilder(\DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'COUNT(DISTINCT orders.id) AS count',
            'SUM((details.price * details.quantity)/currencyFactor) AS amount'
        ))
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
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $shopIds
     * @return DBALQueryBuilder
     *      array (
     *          'count' => '386109',
     *          'amount' => '22637520.4061901',
     *          'displayDate' => 'Monday',
     *      ),
     */
    protected function createAmountBuilder(\DateTime $from = null, \DateTime $to = null, array $shopIds = array())
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'COUNT(orders.id) AS count',
            'SUM((orders.invoice_amount - orders.invoice_shipping) / orders.currencyFactor) AS amount',
            'Date_Format(orders.ordertime, \'%W\') as displayDate'
        ));

        $builder->from('s_order', 'orders')
            ->leftJoin('orders', 's_premium_dispatch', 'dispatch', 'orders.dispatchID = dispatch.id')
            ->leftJoin('orders', 's_core_paymentmeans', 'payment', 'orders.paymentID = payment.id')
            ->innerJoin('orders', 's_order_billingaddress', 'billing', 'orders.id = billing.orderID')
            ->innerJoin('billing', 's_core_countries', 'country', 'billing.countryID = country.id')
            ->where('orders.status NOT IN (4, -1)');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $shopId = (int)$shopId;
                $builder->addSelect(
                    "SUM(IF(orders.subshopID=" . $shopId . ", invoice_amount - invoice_shipping, 0)) as amount" . $shopId
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
     * @param $offset
     * @param $limit
     * @param array $sort
     * @param \DateTime $from
     * @param \DateTime $to
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function createVisitorImpressionBuilder($offset, $limit, \DateTime $from = null, \DateTime $to = null, array $sort = array())
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'datum',
            'SUM(visitors.pageimpressions) AS totalImpressions',
            'SUM(visitors.uniquevisits) AS totalVisits'
        ));

        $builder->from('s_statistics_visitors', 'visitors')
            ->leftJoin('visitors', 's_core_shops', 'shops', 'visitors.shopID = shops.id')
            ->groupBy('visitors.datum');

        $this->addSort($builder, $sort)
            ->addDateRangeCondition($builder, $from, $to, 'datum')
            ->addPagination($builder, $offset, $limit);

        return $builder;
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @param array $shopIds
     * @return DBALQueryBuilder
     */
    protected function createAgeOfCustomersBuilder(\DateTime $from = null, \DateTime $to = null, array $shopIds = array())
    {
        $builder = $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'users.firstlogin',
            'billing.birthday'
        ))
            ->from('s_user', 'users')
            ->innerJoin('users', 's_user_billingaddress', 'billing', 'billing.userID = users.id')
            ->andWhere('billing.birthday IS NOT NULL')
            ->andWhere("billing.birthday != '0000-00-00'")
            ->orderBy('birthday', 'DESC');

        $this->addDateRangeCondition($builder, $from, $to, 'users.firstlogin');

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $shopId = (int)$shopId;
                $builder->addSelect(
                    "IF(users.subshopID = {$shopId}, billing.birthday, NULL) as birthday" . $shopId
                );
            }
        }

        return $builder;
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return DBALQueryBuilder
     */
    protected function createOrdersOfCustomersBuilder(\DateTime $from = null, \DateTime $to = null)
    {
        $builder = $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'users.firstlogin AS firstLogin',
            'orders.ordertime AS orderTime',
            'COUNT(orders.id) AS count',
            'billing.salutation'
        ))
            ->from('s_user', 'users')
            ->innerJoin('users', 's_order', 'orders', 'orders.userID = users.id')
            ->innerJoin('users', 's_user_billingaddress', 'billing', 'billing.userID = users.id')
            ->andWhere('orders.status NOT IN (-1, 4)')
            ->groupBy('users.id')
            ->orderBy('orderTime', 'DESC');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        return $builder;
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return DBALQueryBuilder
     */
    protected function createProductSellsBuilder(\DateTime $from = null, \DateTime $to = null)
    {
        $builder = $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'SUM(details.quantity) AS sellCount',
            'articles.name',
            'details.articleordernumber as ordernumber'
        ))
            ->from('s_order_details', 'details')
            ->innerJoin('details', 's_articles', 'articles', 'articles.id = details.articleID')
            ->innerJoin('details', 's_order', 'orders', 'orders.id = details.orderID')
            ->andWhere('orders.status NOT IN (-1, 4)')
            ->groupBy('articles.id')
            ->orderBy('sellCount', 'DESC');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        return $builder;
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return DBALQueryBuilder
     */
    protected function createPartnerRevenueBuilder(\DateTime $from = null, \DateTime $to = null)
    {
        $builder = $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'ROUND(SUM((orders.invoice_amount - orders.invoice_shipping) / orders.currencyFactor), 2) AS revenue',
            'partners.company AS partner',
            'orders.partnerID as trackingCode',
            'partners.id as partnerId'
        ))
            ->from('s_order', 'orders')
            ->leftJoin('orders', 's_emarketing_partner', 'partners', 'partners.idcode = orders.partnerID')
            ->where('orders.status NOT IN (-1, 4)')
            ->andWhere("orders.partnerID != ''")
            ->groupBy('orders.partnerID')
            ->orderBy('revenue', 'DESC');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        return $builder;
    }

    /**
     * @param Shop $shop
     * @param \DateTime $from
     * @param \DateTime $to
     * @return DBALQueryBuilder
     */
    protected function createReferrerRevenueBuilder(Shop $shop = null, \DateTime $from = null, \DateTime $to = null)
    {
        $builder = $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'ROUND(orders.invoice_amount / orders.currencyFactor, 2) AS revenue',
            'users.id as userID',
            'orders.referer AS referrer',
            'DATE(users.firstlogin) as firstLogin',
            'DATE(orders.ordertime) as orderTime',
            '(
                SELECT o2.ordertime
                FROM s_order o2
                WHERE o2.userID = users.id
                ORDER BY o2.ordertime DESC
                LIMIT 1
            ) as firstOrder',
            '(
                SELECT ROUND(SUM(o3.invoice_amount / o3.currencyFactor), 2)
                FROM s_order o3
                WHERE o3.userID = users.id
                AND o3.status != 4
                AND o3.status != -1
            ) as customerRevenue'
        ))
            ->from('s_order', 'orders')
            ->innerJoin('orders', 's_user', 'users', 'orders.userID = users.id')
            ->where('orders.status != 4 AND orders.status != -1')
            ->andWhere("orders.referer LIKE 'http%//%'")
            ->orderBy('revenue');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        if ($shop instanceof Shop && $shop->getHost()) {
            $builder->andWhere("orders.referer NOT LIKE :hostname")
                ->setParameter(':hostname', '%' . $shop->getHost() . '%');
        }

        return $builder;
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return DBALQueryBuilder
     */
    protected function createVisitedReferrerBuilder(\DateTime $from = null, \DateTime $to = null)
    {
        $builder = $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'COUNT(referrers.referer) as count',
            'referrers.referer as referrer'
        ))
            ->from('s_statistics_referer', 'referrers')
            ->groupBy('referer')
            ->orderBy('count', 'DESC');

        $this->addDateRangeCondition($builder, $from, $to, 'referrers.datum');

        return $builder;
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return DBALQueryBuilder
     *      array (
     *          'count' => '386109',
     *          'amount' => '22637520.4061901',
     *          'displayDate' => 'Monday',
     *      ),
     */
    protected function createCustomerGroupAmountBuilder(\DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->createAmountBuilder()
            ->addSelect('customerGroups.description as customerGroup')
            ->innerJoin('orders', 's_user', 'users', 'users.id = orders.userID')
            ->innerJoin('users', 's_core_customergroups', 'customerGroups', 'users.customergroup = customerGroups.groupkey')
            ->groupBy('users.customergroup');

        $this->addDateRangeCondition($builder, $from, $to, 'orders.ordertime');

        return $builder;
    }


    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @param int $shopId
     * @return DBALQueryBuilder
     */
    protected function createShopStatisticBuilder(\DateTime $from = null, \DateTime $to = null, $shopId = 0)
    {
        $builder = $this->createVisitorBuilder();

        $builder->addSelect(array(
            'visitor.pageimpressions AS clicks',
            'SUM(visitor.uniquevisits) as totalVisits',
            'SUM(orders.invoice_amount) AS revenue',
            'COUNT(DISTINCT orders.id) AS totalOrders',
            'COUNT(DISTINCT users.id) AS newCustomers',
        ));

        $builder->leftJoin('visitor', 's_user', 'users', 'users.firstlogin = visitor.datum')
            ->groupBy('visitor.datum');

        if(!empty($shopId)){
            $builder->andWhere('users.subshopID = :shopId')
                ->setParameter('shopId', $shopId);
        }

        $this->addDateRangeCondition($builder, $from, $to, 'visitor.datum');

        return $builder;
    }

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     * @return DBALQueryBuilder
     */
    protected function createOrdersOfVisitorsBuilder(\DateTime $from = null, \DateTime $to = null)
    {
        $builder = $this->createVisitorBuilder();

        $this->addDateRangeCondition($builder, $from, $to, 'visitor.datum');

        return $builder;
    }

    /**
     * Helper function which creates a dbal query builder which selects
     * all shop visitors and the count of orders and canceled orders for each
     * visitor.
     *
     * @return DBALQueryBuilder
     */
    protected function createVisitorBuilder()
    {
        $builder = $this->connection->createQueryBuilder();

        $builder->select(array(
            'visitor.datum AS date',
            'visitor.uniquevisits AS visitors',
            'COUNT(orders.id) AS orderCount',
            '(
                SELECT COUNT(o2.invoice_amount)
                FROM s_order o2
                WHERE o2.status=-1
                AND DATE(o2.ordertime) = visitor.datum
            ) AS cancelledOrders'
        ));

        $builder->from('s_statistics_visitors', 'visitor')
            ->leftJoin('visitor', 's_order', 'orders', 'visitor.datum = DATE(orders.ordertime) AND orders.status NOT IN (-1)')
            ->groupBy('visitor.datum');

        return $builder;
    }


    /**
     * Helper function which adds the date range condition to an aggregate order query.
     * @param DBALQueryBuilder $builder
     * @param \DateTime $from
     * @param \DateTime $to
     * @param $column
     * @return $this
     */
    private function addDateRangeCondition(DBALQueryBuilder $builder, \DateTime $from = null, \DateTime $to = null, $column)
    {
        if ($from instanceof \DateTime) {
            $builder->andWhere($column . ' >= :fromDate')
                ->setParameter('fromDate', $from->format("Y-m-d H:i:s"));
        }
        if ($to instanceof \DateTime) {
            $builder->andWhere($column . ' <= :toDate')
                ->setParameter('toDate', $to->format("Y-m-d H:i:s"));
        }

        return $this;
    }

    /**
     * Helper function which iterates all sort arrays and at them as order by condition.
     * @param DBALQueryBuilder $builder
     * @param $sort
     * @return $this
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
     * @param DBALQueryBuilder $builder
     * @param $offset
     * @param $limit
     * @return $this
     */
    private function addPagination(DBALQueryBuilder $builder, $offset, $limit)
    {
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $this;
    }


}
