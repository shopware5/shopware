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
    public function getProductAmountPerCategory($categoryId, \DateTime $from, \DateTime $to)
    {
        $builder = $this->createProductAmountBuilder($from, $to)
            ->addSelect('categories.description as name')
            ->addSelect('( SELECT parent FROM s_categories WHERE categories.id=parent LIMIT 1 ) as node')
            ->innerJoin('articles', 's_articles_categories_ro', 'articleCategories', 'articles.id = articleCategories.articleID')
            ->innerJoin('articleCategories', 's_categories', 'categories', 'articleCategories.categoryID = categories.id')
            ->andWhere('categories.active = 1')
            ->groupBy('categories.id');

        if ($categoryId) {
            $builder->andWhere('categories.parent = :parent')
                ->setParameter('parent', $categoryId);
        }

        return new Result($builder);
    }


    /**
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return Result
     *   array (
     *      'count' => '122',
     *      'amount' => '9303.713999999969',
     *      'name' => 'Beachdreams Clothes',
     *   ),
     *   array (
     *      'count' => '121',
     *      'amount' => '15352.479999999925',
     *      'name' => 'Example',
     *   )
     */
    public function getProductAmountPerManufacturer(\DateTime $from, \DateTime $to)
    {
        $builder = $this->createProductAmountBuilder($from, $to)
            ->addSelect('suppliers.name')
            ->leftJoin('articles', 's_articles_supplier', 'suppliers', 'articles.supplierID = suppliers.id')
            ->groupBy('articles.supplierID')
            ->orderBy('suppliers.name');

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
            'COUNT(s.searchterm) AS countRequests',
            's.searchterm',
            'MAX(s.results) as countResults'
        ))
            ->from('s_statistics_search', 's')
            ->groupBy('s.searchterm')
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

        $builder = $this->eventManager->filter('Shopware_Analytics_GetSearchTerms', $builder, array(
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
     * array (
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
    public function getVisitorsInRange(\DateTime $from, \DateTime $to, $offset, $limit, $sort = array(), array $shopIds = array())
    {
        $builder = $this->createVisitorsBuilder(
            $offset, $limit, $sort, array(
                array(
                    'property' => 'datum',
                    'operator' => '>=',
                    'value' => $from->format("Y-m-d H:i:s")
                ),
                array(
                    'property' => 'datum',
                    'operator' => '<=',
                    'value' => $to->format("Y-m-d H:i:s")
                ),
            )
        );

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $shopId = (int)$shopId;

                $builder->addSelect(
                    "SUM(IF(IF(cs.main_id is null, cs.id, cs.main_id)=" . $shopId . ", s.pageimpressions, 0)) as impressions" . $shopId
                );

                $builder->addSelect(
                    "SUM(IF(IF(cs.main_id is null, cs.id, cs.main_id)=" . $shopId . ", s.uniquevisits, 0)) as  visits" . $shopId
                );
            }
        }

        $builder = $this->eventManager->filter('Shopware_Analytics_GetVisitorsInRange', $builder, array(
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
     */
    public function getAmountPerCountry(\DateTime $from, \DateTime $to, array $shopIds = array())
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('country.countryname AS name')
            ->groupBy('billing.countryID')
            ->orderBy('name');

        $builder = $this->eventManager->filter('Shopware_Analytics_GetAmountPerCountry', $builder, array(
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
     */
    public function getAmountPerPayment(\DateTime $from, \DateTime $to, array $shopIds = array())
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('payment.description AS name')
            ->groupBy('invoice.paymentID')
            ->orderBy('name');

        $builder = $this->eventManager->filter('Shopware_Analytics_GetAmountPerPayment', $builder, array(
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
     */
    public function getAmountPerShipping(\DateTime $from, \DateTime $to, array $shopIds = array())
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('dispatch.name AS name')
            ->groupBy('invoice.dispatchID')
            ->orderBy('dispatch.name');

        $builder = $this->eventManager->filter('Shopware_Analytics_GetAmountPerShipping', $builder, array(
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
     */
    public function getAmountPerMonth(\DateTime $from, \DateTime $to, array $shopIds = array())
    {
        $dateCondition = 'DATE_FORMAT(ordertime, \'%Y-%m-01\')';
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect($dateCondition . ' AS date')
            ->groupBy($dateCondition)
            ->orderBy('date');

        $builder = $this->eventManager->filter('Shopware_Analytics_GetAmountPerMonth', $builder, array(
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
     */
    public function getAmountPerCalendarWeek(\DateTime $from, \DateTime $to, array $shopIds = array())
    {
        $dateCondition = 'DATE_SUB(DATE(ordertime), INTERVAL WEEKDAY(ordertime)-3 DAY)';
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect($dateCondition . ' AS date')
            ->groupBy($dateCondition)
            ->orderBy('date');

        $builder = $this->eventManager->filter('Shopware_Analytics_GetAmountPerWeek', $builder, array(
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
     */
    public function getAmountPerWeekday(\DateTime $from, \DateTime $to, array $shopIds = array())
    {
        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect('DATE_FORMAT(ordertime, \'%Y-%m-%d\') AS date')
            ->groupBy('WEEKDAY(ordertime)')
            ->orderBy('date');

        $builder = $this->eventManager->filter('Shopware_Analytics_GetAmountPerWeekday', $builder, array(
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
     */
    public function getAmountPerHour(\DateTime $from, \DateTime $to, array $shopIds = array())
    {
        $dateCondition = 'DATE_FORMAT(ordertime, \'1970-01-01 %H:00:00\')';

        $builder = $this->createAmountBuilder($from, $to, $shopIds)
            ->addSelect($dateCondition .' AS date')
            ->groupBy($dateCondition)
            ->orderBy('date');

        $builder = $this->eventManager->filter('Shopware_Analytics_GetAmountPerHour', $builder, array(
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
     */
    public function getProductImpressionOfRange(\DateTime $from, \DateTime $to, $offset, $limit, array $sort = array(), array $shopIds = array())
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
            $builder = $this->addSort($builder, $sort);
        }
        if (!empty($shopIds)) {
            foreach($shopIds as $shopId) {
                $shopId = (int) $shopId;
                $builder->addSelect(
                    'SUM(IF(articleImpression.shopId = ' . $shopId . ', articleImpression.impressions, 0)) as amount' . $shopId
                );
            }
        }

        $builder = $this->eventManager->filter('Shopware_Analytics_getProductImpressionOfRange', $builder, array(
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

        $builder = $this->addSort($builder, $sort);
        return $this->addPagination($builder, $offset, $limit);
    }


    /**
     * This function creates a DBAL query builder, which used to determine the product sale value per order.
     * This is used to display, for example, how much revenue bring the products to a category or a manufacturer.
     *
     * @param \DateTime $from
     * @param \DateTime $to
     * @return DBALQueryBuilder
     */
    protected function createProductAmountBuilder(\DateTime $from, \DateTime $to)
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

        if ($from instanceof \DateTime) {
            $builder->andWhere('orders.ordertime >= :fromTime')
                ->setParameter('fromTime', $from->format("Y-m-d H:i:s"));
        }
        if ($to instanceof \DateTime) {
            $builder->andWhere('orders.ordertime <= :toTime')
                ->setParameter('toTime', $to->format("Y-m-d H:i:s"));

        }
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
     *  array (
     *      'count' => '386109',
     *      'amount' => '22637520.4061901',
     *      'displayDate' => 'Monday',
     *  ),
     */
    protected function createAmountBuilder(\DateTime $from, \DateTime $to, array $shopIds = array())
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'COUNT(invoice.id) AS count',
            'SUM((invoice.invoice_amount - invoice.invoice_shipping) / invoice.currencyFactor) AS amount',
            'Date_Format(invoice.ordertime, \'%W\') as displayDate'
        ));

        $builder->from('s_order', 'invoice')
            ->leftJoin('invoice', 's_premium_dispatch', 'dispatch', 'invoice.dispatchID = dispatch.id')
            ->leftJoin('invoice', 's_core_paymentmeans', 'payment', 'invoice.paymentID = payment.id')
            ->innerJoin('invoice', 's_order_billingaddress', 'billing', 'invoice.id = billing.orderID')
            ->innerJoin('billing', 's_core_countries', 'country', 'billing.countryID = country.id')
            ->where('invoice.status NOT IN (4, -1)');

        if ($from instanceof \DateTime) {
            $builder->andWhere('invoice.ordertime >= :fromDate')
                ->setParameter('fromDate', $from->format("Y-m-d H:i:s"));
        }
        if ($to instanceof \DateTime) {
            $builder->andWhere('invoice.ordertime <= :toDate')
                ->setParameter('toDate', $to->format("Y-m-d H:i:s"));
        }

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $shopId = (int)$shopId;
                $builder->addSelect(
                    "SUM(IF(invoice.subshopID=" . $shopId . ", invoice_amount - invoice_shipping, 0)) as amount" . $shopId
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
     * @param $sort
     * @param $filter
     * @return \Doctrine\DBAL\Query\QueryBuilder
     * @internal param $shopIds
     */
    protected function createVisitorsBuilder($offset, $limit, array $sort = array(), array $filter = array())
    {
        $builder = $this->connection->createQueryBuilder();
        $builder->select(array(
            'datum',
            'SUM(pageimpressions) AS totalImpressions',
            'SUM(uniquevisits) AS totalVisits'
        ));

        $builder->from('s_statistics_visitors', 's')
            ->leftJoin('s', 's_core_shops', 'cs', 's.shopID = cs.id')
            ->groupBy('s.datum');

        $builder = $this->addSort($builder, $sort);
        $builder = $this->addFilter($builder, $filter);
        $builder = $this->addPagination($builder, $offset, $limit);

        return $builder;
    }

    /**
     * Helper function which adds multiple filter conditions to the passed DBAL query builder.
     * Adds each filter with an AND condition.
     *
     * array(
     *      array('property' => 'active', 'operator' => '=', 'value' => true),
     *      array('property' => 'active', 'operator' => '=', 'value' => true),
     * )
     *
     *
     * @param DBALQueryBuilder $builder
     * @param array $filter
     * @return DBALQueryBuilder
     */
    private function addFilter(DBALQueryBuilder $builder, array $filter)
    {
        if (empty($filter)) {
            return $builder;
        }

        foreach ($filter as $key => $condition) {
            $alias = ':' . $condition['property'] . $key;

            $comparison = $builder->expr()->comparison(
                $condition['property'],
                $condition['operator'],
                $alias
            );

            $builder->andWhere($comparison);
            $builder->setParameter($alias, $condition['value']);
        }

        return $builder;
    }


    /**
     * Helper function which iterates all sort arrays and at them as order by condition.
     * @param DBALQueryBuilder $builder
     * @param $sort
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function addSort(DBALQueryBuilder $builder, $sort)
    {
        if (empty($sort)) {
            return $builder;
        }

        foreach ($sort as $condition) {
            $builder->addOrderBy(
                $condition['property'],
                $condition['direction']
            );
        }
        return $builder;
    }

    /**
     * Small helper function which adds the first and max result to the query builder.
     *
     * @param DBALQueryBuilder $builder
     * @param $offset
     * @param $limit
     * @return DBALQueryBuilder
     */
    private function addPagination(DBALQueryBuilder $builder, $offset, $limit)
    {
        $builder->setFirstResult($offset)
            ->setMaxResults($limit);

        return $builder;
    }


}
