<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

/**
 * Statistics controller
 *
 * @category  Shopware
 * @package   Shopware\Controllers\Backend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_Analytics extends Shopware_Controllers_Backend_ExtJs
{

    /**
     * Entity Manager
     * @var null
     */
    protected $manager = null;

    /**
     * @var \Shopware\Models\Shop\Repository
     */
    protected $shopRepository;

    /**
     * @var \Shopware\Models\Tracking\Repository
     */
    protected $articleImpressionRepository;

	protected function initAcl()
	{
		// read
		$this->addAclPermission('shopList', 'read', 'Insufficient Permissions');
		$this->addAclPermission('sourceList', 'read', 'Insufficient Permissions');
		$this->addAclPermission('orderAnalytics', 'read', 'Insufficient Permissions');
		$this->addAclPermission('visits', 'read', 'Insufficient Permissions');
		$this->addAclPermission('orderDetailAnalytics', 'read', 'Insufficient Permissions');
		$this->addAclPermission('searchAnalytics', 'read', 'Insufficient Permissions');
		$this->addAclPermission('conversionRate', 'read', 'Insufficient Permissions');
	}

    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return null|\Shopware\Components\Model\ModelManager
     */
    private function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }
        return $this->manager;
    }
    /**
     * Helper Method to get access to the shop repository.
     *
     * @return Shopware\Models\Shop\Repository
     */
    public function getShopRepository()
    {
        if ($this->shopRepository === null) {
            $this->shopRepository = $this->getManager()->getRepository('Shopware\Models\Shop\Shop');
        }
        return $this->shopRepository;
    }

    /**
     * Helper Method to get access to the tracking repository.
     *
     * @return Shopware\Models\Tracking\Repository
     */
    public function getArticleImpressionRepository()
    {
        if ($this->articleImpressionRepository === null) {
            $this->articleImpressionRepository = $this->getManager()->getRepository('Shopware\Models\Tracking\ArticleImpression');
        }
        return $this->articleImpressionRepository;
    }

    /**
     * Returns the query builder to fetch all available stores
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getShopsQueryBuilder()
    {
        $builder = $this->getManager()->getDBALQueryBuilder();
        $builder->select(array(
            's.id',
            's.name',
            'c.currency',
            'c.name AS currencyName',
            'c.templateChar AS currencyChar'
        ))
        ->from('s_core_shops', 's')
        ->leftJoin('s', 's_core_currencies', 'c', 's.currency_id = c.id')
        ->orderBy('s.default', 'desc')
        ->orderBy('s.name');

        return $builder;
    }

    /**
     * Returns the query builder to fetch all visitors
     *
     * @param $property
     * @param $direction
     * @param $start
     * @param $limit
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getVisitorsQueryBuilder($property, $direction, $start, $limit)
    {
        $shopIds = $this->getSelectedShopIds();

        $builder = $this->getManager()->getDBALQueryBuilder();
        $builder->select(array(
            'datum',
            'SUM(pageimpressions) AS totalImpressions',
            'SUM(uniquevisits) AS totalVisits'
        ));

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $builder->addSelect("SUM(IF(IF(cs.main_id is null, cs.id, cs.main_id)={$shopId}, s.pageimpressions, 0)) as `impressions{$shopId}`");
                $builder->addSelect("SUM(IF(IF(cs.main_id is null, cs.id, cs.main_id)={$shopId}, s.uniquevisits, 0)) as `visits{$shopId}` ");
            }
        }

        $builder->from('s_statistics_visitors', 's')
        ->leftJoin('s', 's_core_shops', 'cs', 's.shopID = cs.id')
        ->where('datum >= :fromDate')
        ->andWhere('datum <= :toDate')
        ->groupBy('datum')
        ->orderBy(':parameter', ':direction')
        ->setFirstResult($start)
        ->setMaxResults($limit)
        ->setParameter('parameter', $property)
        ->setParameter('direction', $direction)
        ->setParameter('fromDate', $this->getFromDate())
        ->setParameter('toDate', $this->getToDate());

        return $builder;
    }

    /**
     * Returns the query builder to fetch all search terms which are filtered by the parameters
     *
     * @param $property
     * @param $direction
     * @param $start
     * @param $limit
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getSearchTermsQueryBuilder($property, $direction, $start, $limit)
    {
        $builder = $this->getManager()->getDBALQueryBuilder();
        $builder->select(array(
            'COUNT(s.searchterm) AS countRequests',
            's.searchterm',
            'MAX(s.results) as countResults'
        ))
        ->from('s_statistics_search', 's')
        ->groupBy('s.searchterm')
        ->orderBy(':parameter', ':direction')
        ->setFirstResult($start)
        ->setMaxResults($limit)
        ->setParameter('parameter', $property)
        ->setParameter('direction', $direction);

        return $builder;
    }

    /**
     * Returns the query builder to fetch all search terms which are filtered by the parameters
     *
     * @param $start
     * @param $limit
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getConversionRateQueryBuilder($start, $limit)
    {
        $shopIds = $this->getSelectedShopIds();
        $builder = $this->getManager()->getDBALQueryBuilder();

        $builder->select(array(
            'sv.datum as date',
            'SUM(sv.uniquevisits) as totalVisits',
            'COUNT(DISTINCT o.id) AS totalOrders',
        ))
        ->from('s_statistics_visitors', 'sv')
        ->leftJoin('sv', 's_order', 'o', 'DATE(o.ordertime) = sv.datum AND o.status NOT IN (4,-1)')
        ->where('datum >= :fromDate')
        ->andWhere('datum <= :toDate')
        ->groupBy('sv.datum')
        ->orderBy('sv.datum', 'DESC')
        ->setFirstResult($start)
        ->setMaxResults($limit)
        ->setParameter('fromDate', $this->getFromDate())
        ->setParameter('toDate', $this->getToDate());

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $builder->addSelect("(
                    SELECT uniquevisits
                    FROM   s_statistics_visitors
                    WHERE  shopID = {$shopId}
                    AND    datum = sv.datum
                ) as visits{$shopId}");

                $builder->addSelect("(
                    SELECT COUNT(DISTINCT o2.id)
                    FROM   s_order as o2
                    WHERE  o2.subshopID = {$shopId}
                    AND    DATE(o2.ordertime) = sv.datum
                    AND    o2.status NOT IN (4,-1)
                ) as orders{$shopId}");
            }
        }

        return $builder;
    }

    private function getOrderDetailQueryBuilder()
    {
        $builder = $this->getManager()->getDBALQueryBuilder();
        $builder->select(array(
            'COUNT(DISTINCT o.id) AS count',
            'SUM((od.price * od.quantity)/currencyFactor) AS amount'
        ))
        ->from('s_order', 'o')
        ->innerJoin('o', 's_order_details', 'od', 'o.id = od.orderID AND od.modus=0')
        ->innerJoin('od', 's_articles', 'a', 'od.articleID = a.id')
        ->where('o.status NOT IN (4, -1)')
        ->andWhere('o.ordertime >= :fromTime')
        ->andWhere('o.ordertime <= :toTime')
        ->setParameter('fromTime', $this->getFromDate())
        ->setParameter('toTime', $this->getToDate())
        ->orderBy('name');

        return $builder;
    }

    private function getOrderAnalyticsQueryBuilder()
    {
        $shopIds = $this->getSelectedShopIds();

        $builder = $this->getManager()->getDBALQueryBuilder();
        $builder->select(array(
            'COUNT(*) AS count',
            'SUM((invoice_amount - invoice_shipping)/currencyFactor) AS amount',
            'Date_Format(ordertime, \'%W\') as displayDate'
        ))
        ->from('s_order', 'o')
        ->leftJoin('o', 's_premium_dispatch', 'd', 'o.dispatchID = d.id')
        ->leftJoin('o', 's_core_paymentmeans', 'p', 'o.paymentID = p.id')
        ->innerJoin('o', 's_order_billingaddress', 'ob', 'o.id = ob.orderID')
        ->innerJoin('ob', 's_core_countries', 'c', 'ob.countryID = c.id')
        ->where('o.status NOT IN (4, -1)')
        ->andWhere('o.ordertime >= :fromTime')
        ->andWhere('o.ordertime <= :toTime')
        ->setParameter('fromTime', $this->getFromDate())
        ->setParameter('toTime', $this->getToDate());

        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $builder->addSelect("SUM(IF(o.subshopID={$shopId}, invoice_amount - invoice_shipping, 0)) as amount{$shopId}");
            }
        }

        return $builder;
    }

    private function formatOrderAnalyticsData($data)
    {
        $shopIds = $this->getSelectedShopIds();

        foreach ($data as &$row) {
            $row['count'] = (int)$row['count'];
            $row['amount'] = (float)$row['amount'];

            if(!empty($row['date'])){
                $row['date'] = strtotime($row['date']);
            }

            if (!empty($shopIds)) {
                foreach ($shopIds as $shopId) {
                    $row['amount' . $shopId] = (float)$row['amount' . $shopId];
                }
            }
        }

        return $data;
    }

    /**
     * Get a list of installed shops
     */
    public function shopListAction()
    {
        $builder = $this->getShopsQueryBuilder();
        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(array('data' => $data, 'success' => true));
    }

    public function getOverviewAction()
    {
        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array(
            'sv.datum AS date',
            'sv.pageimpressions AS clicks',
            'COUNT(o.id) AS orders',
            'o.invoice_amount AS revenue',
            'sv.uniquevisits AS visitors',
            '(
                SELECT COUNT(o2.invoice_amount)
                FROM s_order o2
                WHERE o2.status=-1
                AND DATE(o2.ordertime) = sv.datum
            ) AS cancelledOrders',
            'COUNT(DISTINCT u.id) AS newCustomers'
        ))
        ->from('s_statistics_visitors', 'sv')
        ->leftJoin('sv', 's_order', 'o', 'sv.datum = DATE(o.ordertime)')
        ->leftJoin('sv', 's_user', 'u', 'u.firstlogin = sv.datum')
        ->where('sv.datum >= :fromDate AND sv.datum <= :toDate')
        ->groupBy('sv.datum')
        ->setParameter(':fromDate', $this->getFromDate())
        ->setParameter(':toDate', $this->getToDate());

        $this->addLimitQuery($builder);
        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach($data as &$row){
            $row['date'] = strtotime($row['date']);
            $row['revenue'] = (float)($row['revenue']);
            $row['orders'] = (int)($row['orders']);
            $row['clicks'] = (int)($row['clicks']);
            $row['visitors'] = (int)($row['visitors']);
            $row['cancelledOrders'] = (int)($row['cancelledOrders']);
            $row['newCustomers'] = (int)($row['newCustomers']);
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'totalCount' =>  $statement->rowCount()));
    }

    public function getRatingAction()
    {
        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array(
            'sv.datum AS date',
            'COUNT(o.id) AS orders',
            'sv.uniquevisits AS visitors',
            '(
                SELECT COUNT(o2.invoice_amount)
                FROM s_order o2
                WHERE o2.status=-1
                AND DATE(o2.ordertime) = sv.datum
            ) AS cancelledOrders'
        ))
        ->from('s_statistics_visitors', 'sv')
        ->leftJoin('sv', 's_order', 'o', 'sv.datum = DATE(o.ordertime)')
        ->where('sv.datum >= :fromDate AND sv.datum <= :toDate')
        ->groupBy('sv.datum')
        ->setParameter(':fromDate', $this->getFromDate())
        ->setParameter(':toDate', $this->getToDate());

        $statement = $builder->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $data = array();

        foreach($results as $result){
            $orders = $result['orders'];
            $visitors = $result['visitors'];
            $cancelledOrders = $result['cancelledOrders'];

            $data[] = array(
                'date' => strtotime($result['date']),
                'basketConversion' => round($orders / ($cancelledOrders + $orders) * 100, 2),
                'orderConversion' => round($orders / $visitors * 100, 2),
                'basketVisitConversion' => round($cancelledOrders / $visitors * 100, 2)
            );
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'totalCount' =>  1));
    }

    public function getReferrerRevenueAction()
    {
        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array(
            'ROUND(o.invoice_amount / o.currencyFactor) AS revenue',
            'o.referer AS referrer',
            'sv.uniquevisits AS visitors',
            '(
                SELECT COUNT(o2.invoice_amount)
                FROM s_order o2
                WHERE o2.status=-1
                AND DATE(o2.ordertime) = sv.datum
            ) AS cancelledOrders'
        ))
        ->from('s_statistics_visitors', 'sv')
        ->leftJoin('sv', 's_order', 'o', 'sv.datum = DATE(o.ordertime)')
        ->where('sv.datum >= :fromDate AND sv.datum <= :toDate')
        ->groupBy('sv.datum')
        ->setParameter(':fromDate', $this->getFromDate())
        ->setParameter(':toDate', $this->getToDate());

        $data = array(
            'host' => 'google.de',
            'entireRevenue' => 10000,
            'lead' => 200,
            'customerValue' => 300,
            'entireNewRevenue' => 7500,
            'entireOldRevenue' => 2500,
            'orders' => 100,
            'newCustomers' => 75,
            'oldCustomers' => 25,
            'perNewRevenue' => 100,
            'perOldRevenue' => 100
        );

        $this->View()->assign(array('success' => true, 'data' => $data, 'totalCount' =>  1));
    }

    public function getMonthAction()
    {
        $builder = $this->getOrderAnalyticsQueryBuilder();
        $builder->addSelect('DATE_FORMAT(ordertime, \'%Y-%m-01\') AS date');
        $builder->groupBy('date');
        $builder->orderBy('date');

        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(array('success' => true, 'data' => $this->formatOrderAnalyticsData($data), 'total' => $statement->rowCount()));
    }

    public function getCalendarWeeksAction()
    {
        $builder = $this->getOrderAnalyticsQueryBuilder();
        $builder->addSelect('DATE_SUB(DATE(ordertime), INTERVAL WEEKDAY(ordertime)-3 DAY) AS date');
        $builder->groupBy('DATE_SUB(DATE(ordertime), INTERVAL WEEKDAY(ordertime)-3 DAY)');
        $builder->orderBy('date');

        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        $this->View()->assign(array('success' => true, 'data' => $this->formatOrderAnalyticsData($data), 'total' => $statement->rowCount()));
    }

    public function getWeekdaysAction()
    {
        $builder = $this->getOrderAnalyticsQueryBuilder();
        $builder->addSelect('DATE_FORMAT(ordertime, \'%Y-%m-%d\') AS date');
        $builder->groupBy('WEEKDAY(ordertime)');
        $builder->orderBy('date');

        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(array('success' => true, 'data' => $this->formatOrderAnalyticsData($data), 'total' => $statement->rowCount()));
    }

    public function getTimeAction()
    {
        $builder = $this->getOrderAnalyticsQueryBuilder();
        $builder->addSelect('DATE_FORMAT(ordertime, \'1970-01-01 %H:00:00\') AS date');
        $builder->orderBy('date');
        $builder->groupBy('date');

        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        $this->View()->assign(array('success' => true, 'data' => $this->formatOrderAnalyticsData($data), 'total' => $statement->rowCount()));
    }

    public function getCategoriesAction()
    {
        $node = $this->Request()->getParam('node', 'root');
        $node = $node === 'root' ? 1 : (int) $node;

        $builder = $this->getOrderDetailQueryBuilder();
        $builder->addSelect('c.description as name')
                ->addSelect('( SELECT parent FROM s_categories WHERE c.id=parent LIMIT 1 ) as node')
                ->innerJoin('a', 's_articles_categories_ro', 'ac', 'a.id = ac.articleID')
                ->innerJoin('ac', 's_categories', 'c', 'ac.categoryID = c.id AND c.active = 1 AND c.parent = :parent')
                ->groupBy('c.id')
                ->setParameter('parent', $node);

        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as &$row) {
            $row['count'] = (int)$row['count'];
            $row['amount'] = (float)$row['amount'];
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $statement->rowCount()));
    }

    public function getCountriesAction()
    {
        $builder = $this->getOrderAnalyticsQueryBuilder();
        $builder->addSelect('c.countryname AS name');
        $builder->groupBy('ob.countryID');
        $builder->orderBy('name');

        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(array('success' => true, 'data' => $this->formatOrderAnalyticsData($data), 'total' => $statement->rowCount()));
    }

    public function getPaymentAction()
    {
        $builder = $this->getOrderAnalyticsQueryBuilder();
        $builder->addSelect('p.description AS name');
        $builder->groupBy('o.paymentID');
        $builder->orderBy('name');

        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(array('success' => true, 'data' => $this->formatOrderAnalyticsData($data), 'total' => $statement->rowCount()));
    }

    public function getShippingMethodsAction()
    {
        $builder = $this->getOrderAnalyticsQueryBuilder();
        $builder->addSelect('d.name AS name');
        $builder->groupBy('o.dispatchID');
        $builder->orderBy('name');

        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(array('success' => true, 'data' => $this->formatOrderAnalyticsData($data), 'total' => $statement->rowCount()));
    }

    public function getConversionRateAction()
    {
        $start = (int) $this->Request()->getParam('start', 0);
        $limit = (int) $this->Request()->getParam('limit', 25);

        $shopIds= $this->getSelectedShopIds();

        $builder = $this->getConversionRateQueryBuilder($start, $limit);
        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach($data as &$row){
            $row['totalConversion'] = round($row['totalOrders'] / $row['totalVisits'] * 100, 2);

            if(!empty($shopIds)){
                foreach($shopIds as $shopId){
                    $row['conversion' . $shopId] =  round($row['orders' . $shopId] / $row['visits' . $shopId] * 100, 2);
                }
            }
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $statement->rowCount()));
    }

    public function getVendorsAction()
    {
        $builder = $this->getOrderDetailQueryBuilder()
                        ->addSelect('s.name')
                        ->leftJoin('a', 's_articles_supplier', 's', 'a.supplierID = s.id')
                        ->groupBy('a.supplierID')
                        ->orderBy('s.name');

        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($data as &$row) {
            $row['count'] = (int)$row['count'];
            $row['amount'] = (float)$row['amount'];
        }

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $statement->rowCount()));
    }

    public function getSearchTermsAction()
    {
        $start = (int) $this->Request()->getParam('start', 0);
        $limit = (int) $this->Request()->getParam('limit', 25);
        $sort = (array) $this->Request()->getParam('sort', array());

        if (empty($sort) || empty($sort[0])) {
            $sort[0] = array('property' => 'countRequests', 'direction' => 'DESC');
        }
        $sort = $sort[0];

        $builder = $this->getSearchTermsQueryBuilder($sort['property'], $sort['direction'], $start, $limit);
        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $data));
    }

    public function getVisitorsAction()
    {
        $start = (int) $this->Request()->getParam('start', 0);
        $limit = (int) $this->Request()->getParam('limit', 25);
        $sort = (array) $this->Request()->getParam('sort', array());

        if (empty($sort) || empty($sort[0])) {
            $sort[0] = array('property' => 'datum', 'direction' => 'DESC');
        }
        $sort = $sort[0];

        $builder = $this->getVisitorsQueryBuilder($sort['property'], $sort['direction'], $start, $limit);
        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => count($data)));
    }

    // todo refactor
    public function getArticleImpressionsAction()
    {
        /** @var $builder \Doctrine\DBAL\Query\QueryBuilder */
        $builder = $this->getManager()->getDBALQueryBuilder();
        $builder->select(array(
            'SQL_CALC_FOUND_ROWS i.articleId',
            'a.name as articleName',
            'UNIX_TIMESTAMP(i.date) as date',
            'SUM(i.impressions) as totalAmount'
        ))
        ->from('s_statistics_article_impression', 'i')
        ->leftJoin('i', 's_articles', 'a', 'i.articleId = a.id')
        ->where('i.date >= :fromDate')
        ->andWhere('i.date <= :toDate')
        ->groupBy('i.date')
        ->setParameter(':fromDate', $this->getFromDate())
        ->setParameter(':toDate', $this->getToDate());

        //add the sub query for all shops to calculate the amount shop specific
        $this->addShopSelectQuery($builder, 'i', 'shopId', 'impressions');
        //add a limit the the query
        $this->addLimitQuery($builder);
        //add a order by to the query
        $this->addOrderQuery($builder, 'totalAmount', 'DESC');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(array('success' => true, 'data' => $data, 'total' => $statement->rowCount()));
    }

    /**
     * helper to get the selected shop ids
     * if no shop is selected the ids of all shops are returned
     *
     * return array | shopIds
     */
    private function getSelectedShopIds(){
        $selectedShopIds = (string) $this->Request()->getParam('selectedShops');

        if(!empty($selectedShopIds)) {
            $selectedShopIds = explode(',', $selectedShopIds);
            return $selectedShopIds;
        }

        $builder = $this->getManager()->getDBALQueryBuilder();
        $builder->select('s.id')
                ->from('s_core_shops', 's')
                ->orderBy('s.default', 'DESC')
                ->addOrderBy('s.name');

        $statement = $builder->execute();

        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * helper to get the from date in the right format
     *
     * return DateTime | fromDate
     */
    private function getFromDate(){
        $fromDate = $this->Request()->getParam('fromDate');
        if (empty($fromDate)) {
            $fromDate = new \DateTime();
            $fromDate = $fromDate->sub(new DateInterval('P1M'));
        } else {
            $fromDate = new \DateTime($fromDate);
        }
        return $fromDate->format("Y-m-d H:i:s");
    }

    /**
     * helper to get the to date in the right format
     *
     * return DateTime | toDate
     */
    private function getToDate() {

        //if a to date passed, format it over the \DateTime object. Otherwise create a new date with today
        $toDate = $this->Request()->getParam('toDate');
        if (empty($toDate)) {
            $toDate = new \DateTime();
        } else {
            $toDate = new \DateTime($toDate);
        }
        //to get the right value cause 2012-02-02 is smaller than 2012-02-02 15:33:12
        $toDate = $toDate->add(new DateInterval('P1D'));
        $toDate = $toDate->sub(new DateInterval('PT1S'));
        return $toDate->format("Y-m-d H:i:s");
    }

    /**
     * helper method to generate the shop sub-queries
     * which is used to select the shop specific values and amounts
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder |$builder
     * @param $tableAlias
     * @param $shopFieldName
     * @param $tableFieldName
     * @param $fieldAlias
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function addShopSelectQuery($builder, $tableAlias, $shopFieldName, $tableFieldName, $fieldAlias = "amount")
    {
        $selectedShopIds = $this->getSelectedShopIds();

        foreach ($selectedShopIds as $shopId) {
            $builder->addSelect(
                'SUM(IF(' . $tableAlias . '.' . $shopFieldName . '=' . $shopId . ', ' . $tableAlias . '.' . $tableFieldName . ', 0)) as ' . $fieldAlias . $shopId
            );
        }
    }


    /**
     * helper method to add an limit to the query
     * @param \Doctrine\DBAL\Query\QueryBuilder | $builder
     */
    private function addLimitQuery($builder)
    {
        $builder->setFirstResult(intval($this->Request()->getParam('start',0)));
        $builder->setMaxResults(intval($this->Request()->getParam('limit',25)));
    }

    /**
     * helper method to add an order by query to the builder
     * uses directly the sort param
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder | $builder
     * @param $defaultProperty
     * @param $defaultDirection
     */
    private function addOrderQuery($builder, $defaultProperty, $defaultDirection)
    {
        $order = (array)$this->Request()->getParam('sort', array());
        if(empty($order)) {
            $builder->orderBy($defaultProperty,$defaultDirection);
        }
        else {
            $builder->orderBy($order[0]["property"], $order[0]["direction"]);
        }
    }
}
