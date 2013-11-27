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
     * @return null
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
     * Get a list of installed shops
     */
    public function shopListAction()
    {
        $sql = '
            SELECT
              s.id , s.name,
              c.currency AS currency,
              c.name AS currencyName,
              c.templatechar AS currencyChar
            FROM s_core_shops s, s_core_currencies c
            WHERE s.currency_id = c.id
            ORDER BY s.default DESC, s.name
        ';
        $data =  Shopware()->Db()->fetchAll($sql);
        $this->View()->assign(array('data' => $data, 'success' => true));
    }

    /**
     * Returns the analytics data for the article impression statistic
     */
    public function articleImpressionAction()
    {
        /** @var $builder \Doctrine\DBAL\Query\QueryBuilder */
        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array(
                'SQL_CALC_FOUND_ROWS impression.articleId',
                'articles.name as articleName',
                'UNIX_TIMESTAMP(impression.date) as date',
                'SUM(impression.impressions) as totalAmount'
        ));
        $builder->from('s_statistics_article_impression', 'impression');
        $builder->leftJoin('impression', 's_articles', 'articles', 'impression.articleId = articles.id');
        $builder->where('`date` >= '. $builder->createNamedParameter($this->getFromDate()).' AND `date` <= '. $builder->createNamedParameter($this->getToDate()));
        $builder->groupBy('impression.date');

        //add the sub query for all shops to calculate the amount shop specific
        $this->addShopSelectQuery($builder, 'impression', 'shopId', 'impressions');
        //add a limit the the query
        $this->addLimitQuery($builder);
        //add a order by to the query
        $this->addOrderQuery($builder, 'totalAmount', 'DESC');

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $builder->execute();
        $articleImpressionData = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(array('success' => true, 'data' => $articleImpressionData, 'totalCount' =>  $this->getFoundRows()));
    }


    /**
     * Get a tree-column-model compatible list
     * of installed shops
     */
    public function sourceListAction()
    {
        $sql = '
           SELECT
              s.id , s.name as text,
              c.currency AS currency,
              c.name AS currencyName,
              c.templatechar AS currencyChar
            FROM s_core_shops s, s_core_currencies c
            WHERE s.currency_id = c.id
            AND s.main_id IS NULL
            ORDER BY s.default DESC, s.name
        ';
        $shops = Shopware()->Db()->fetchAll($sql);
        foreach ($shops as $index => $shop) {
            $shops[$index]['leaf'] = true;
            $shops[$index]['checked'] = false;
        }

        $this->View()->assign(array(
            'text' => '.',
            'children' => array(
                array('text' => 'Shops', 'expanded' => true, 'children' => $shops),
            ),
            'success' => true
        ));
    }


    /**
     * Get sales data for statistics
     * Kind of statistic is defined via
     * $this->Request()->getParam('type')
     *  Possible values:
     *  dispatch,payment,month,weekday,week,daytime,country
     * Returns json formatted result
     */
    public function orderAnalyticsAction()
    {
        $shopIds = $this->getSelectedShopIds();
        $fromDate = $this->getFromDate();
        $toDate = $this->getToDate();

        if (!$this->Request()->getParam('tax')) {
            $sqlAmount = 'invoice_amount-invoice_shipping';
        } else {
            $sqlAmount = 'invoice_amount_net-invoice_shipping_net';
        }
        $sqlAmount = '(' . $sqlAmount . ')/currencyFactor';

        $sqlWhere = '';
        $sqlSelect = '';
        $sqlSelectName = 'name';
        $sqlSelectField = 'ordertime';

        switch ($this->Request()->getParam('type')) {
            case 'dispatch':
                $sqlSelectField = 'd.name';
                $sqlGroupBy = 'o.dispatchID';
                break;
            case 'payment':
                $sqlSelectField = 'p.description';
                $sqlGroupBy = 'o.paymentID';
                break;
            case 'month':
                $sqlSelectField = "DATE_FORMAT(ordertime, '%Y-%m-01')";
                $sqlSelectName = 'date';
                break;
            case 'weekday':
                $sqlSelectField = "Date_Format(ordertime, '%Y-%m-%d')";
                $sqlGroupBy = 'WEEKDAY(ordertime)';
                $sqlSelectName = 'date';
                break;
            case 'week':
                $sqlSelectField = 'DATE_SUB(DATE(ordertime), INTERVAL WEEKDAY(ordertime)-3 DAY)';
                $sqlSelectName = 'date';
                break;
            case 'daytime':
                $sqlSelectField = 'DATE_FORMAT(ordertime, \'1970-01-01 %H:00:00\')';
                $sqlSelectName = 'date';
                break;
            case 'country':
                $sqlSelectField = 'c.countryname';
                $sqlGroupBy = 'ob.countryID';
                break;
            default:
                break;
        }

        if (!isset($sqlGroupBy)) {
            $sqlGroupBy = $sqlSelectField;
        }
        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $sqlSelect .= "SUM(IF(o.subshopID=$shopId, $sqlAmount, 0)) as `amount$shopId`, ";
            }
        }

        $sql = "
            SELECT
        		COUNT(*) as `count`,
        		SUM($sqlAmount) as `amount`,
        		Date_Format(ordertime, '%W') as displayDate,
                $sqlSelect
                $sqlSelectField as `$sqlSelectName`
            FROM `s_order` o

            LEFT JOIN s_premium_dispatch d
            ON o.dispatchID = d.id

            LEFT JOIN s_core_paymentmeans p
            ON o.paymentID = p.id

            JOIN s_order_billingaddress ob
            ON o.id = ob.orderID

            JOIN s_core_countries c
            ON ob.countryID = c.id

            WHERE o.status NOT IN (4, -1)

            AND o.ordertime <= ?
            AND o.ordertime >= ?

            $sqlWhere

            GROUP BY $sqlGroupBy
            ORDER BY $sqlSelectName
        ";

        $data = Shopware()->Db()->fetchAll($sql,array($toDate, $fromDate));

        foreach ($data as &$row) {
            $row['count'] = (int)$row['count'];
            $row['amount'] = (float)$row['amount'];
            $row['date'] = strtotime($row['date']);

            if (!empty($shopIds)) {
                foreach ($shopIds as $shopId) {
                    $row['amount' . $shopId] = (float)$row['amount' . $shopId];
                }
            }
        }

        $this->View()->success = true;
        $this->View()->data = $data;
    }

    /**
     * Get statistics for shop visitors
     */
    public function visitsAction()
    {
        $data = array();
        $sqlSelect = null;

        $fromDate = $this->getFromDate();
        $toDate = $this->getToDate();

        $start = intval($this->Request()->start ? $this->Request()->start : 0);
        $limit = intval($this->Request()->limit ? $this->Request()->limit : 25);
        $sort = $this->Request()->sort;

        if (empty($sort[0])) {
            $sort[0] = array("property" => "datum", "direction" => "DESC");
        }

        $sort = $sort[0];


        $shopIds = $this->getSelectedShopIds();
        if (!empty($shopIds)) {
            foreach ($shopIds as $key => $shopId) {
                if ($key == 0) $sqlSelect = ",\n";
                $sqlSelect .= "SUM(IF(IF(cs.main_id is null, cs.id, cs.main_id)=$shopId, s.pageimpressions, 0)) as `impressions$shopId`, ";
                $sqlSelect .= "SUM(IF(IF(cs.main_id is null, cs.id, cs.main_id)=$shopId, s.uniquevisits, 0)) as `visits$shopId` ";
                if ($key < count($shopIds) - 1) $sqlSelect .= ",\n";
            }
        }
        $sql = "
        SELECT datum,SUM(pageimpressions) AS totalImpressions, SUM(uniquevisits) AS totalVisits
        $sqlSelect
        FROM s_statistics_visitors s
        LEFT JOIN s_core_shops cs ON s.shopID = cs.id
        WHERE datum <= ?
        AND datum >= ?
        GROUP BY datum
        ORDER BY {$sort["property"]} {$sort["direction"]}
        ";

        $data = Shopware()->Db()->fetchAll($sql,array($toDate, $fromDate));

        $this->View()->total = count($data);

        $data = array_splice($data, $start, $limit);
        foreach ($data as &$row) {
            $row['datum'] = strtotime($row['datum']);
        }
        $this->View()->success = true;
        $this->View()->data = $data;

    }

    /**
     * Get sales data for statistics
     * Kind of statistic is defined via
     * $this->Request()->getParam('type')
     *  Possible values:
     *  supplier,category,article,voucher
     * Returns json formatted result
     */
    public function orderDetailAnalyticsAction()
    {
        if (!$this->Request()->getParam('tax')) {
            $sqlAmount = 'od.price * od.quantity';
        } else {
            $sqlAmount = 'od.price / (100+tax) * 100) * od.quantity';
        }
        $sqlAmount = '(' . $sqlAmount . ')/currencyFactor';
        $sqlSelect = '';

        $fromDate = $this->getFromDate();
        $toDate = $this->getToDate();

        switch ($this->Request()->getParam('type')) {
            case 'supplier':
                $sqlSelectField = 's.name';
                $sqlGroupBy = 'a.supplierID';
                $sqlJoin = '
                    JOIN s_articles_supplier s
                    ON s.id = a.supplierID
                ';
                break;
            case 'category':
                $sqlSelectField = 'c.description';
                $sqlGroupBy = 'c.id';
                $node = $this->Request()->getParam('node', 'root');
                if ($node === 'root') {
                    $node = 1;
                } else {
                    $node = (int)$node;
                }
                $sqlSelect .= '(
                    SELECT parent FROM s_categories
                    WHERE c.id=parent LIMIT 1
                ) as `node`, ';

                $sqlJoin = "
                    INNER JOIN s_articles_categories_ro ac
                        ON  ac.articleID  = a.id

                    INNER JOIN s_categories c
                        ON  c.id = ac.categoryID
                        AND c.active = 1
                        AND c.parent=$node
                ";
                break;
            case 'article':
                $sqlSelectField = 'a.name';
                $sqlGroupBy = 'od.articleID';
                break;
            case 'voucher':
                break;
            default:
                break;
        }

        if (!isset($sqlGroupBy)) {
            $sqlGroupBy = $sqlSelectField;
        }

        $sql = "
            SELECT
        		COUNT(DISTINCT o.id) as `count`,
                SUM($sqlAmount) as `amount`,
                $sqlSelect
                $sqlSelectField as `name`
            FROM `s_order` o

            JOIN s_order_details od
            ON od.orderID = o.id AND od.modus=0

            JOIN s_articles a
            ON a.id = od.articleID

            $sqlJoin

            WHERE o.status NOT IN (4, -1)
            AND o.ordertime <= ?
            AND o.ordertime >= ?

            GROUP BY $sqlGroupBy

            ORDER BY `name`
        ";

        $data = Shopware()->Db()->fetchAll($sql,array($toDate, $fromDate));

        foreach ($data as &$row) {
            $row['count'] = (int)$row['count'];
            $row['amount'] = (float)$row['amount'];
        }

        $this->View()->success = true;
        $this->View()->data = $data;
    }

    /**
     * Get statistics for popular search terms
     * Possible sorting values: countRequests, searchterm, countResults
     * Sort is defined in array $this->Request()->sort (property=>...,direction=>ASC/DESC)
     * @return json formatted output
     */
    public function searchAnalyticsAction()
    {
        $data = array();
        $start = intval($this->Request()->start ? $this->Request()->start : 0);
        $limit = intval($this->Request()->limit ? $this->Request()->limit : 25);
        $sort = $this->Request()->sort;

        if (empty($sort[0])) {
            $sort[0] = array("property" => "countRequests", "direction" => "DESC");
        }

        $sort = $sort[0];

        $sql = "
        SELECT COUNT(searchterm) AS countRequests, searchterm,
        MAX(results) AS countResults FROM s_statistics_search GROUP BY searchterm
        ORDER BY {$sort["property"]} {$sort["direction"]}
        ";

        $data = Shopware()->Db()->fetchAll($sql);

        $this->View()->total = count($data);

        $data = array_splice($data, $start, $limit);

        $this->View()->success = true;
        $this->View()->data = $data;
    }

    /**
     * Get conversion rates
     * Basically number of orders for a day / number of visitors
     */
    public function conversionRateAction()
    {
        $sqlSelect = "";
        $start = intval($this->Request()->start ? $this->Request()->start : 0);
        $limit = intval($this->Request()->limit ? $this->Request()->limit : 25);

        $fromDate = $this->getFromDate();
        $toDate = $this->getToDate();


        $shopIds = $this->getSelectedShopIds();
        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $sqlSelect .= "\n 0 AS visits$shopId, 0 AS orders$shopId, 0 AS conversion$shopId,\n";
            }
        }
        /**
         * Fetch total visitors and total orders for each day that may be occurs in statistics
         * Result - Sample:
         * Array ( [0] => Array
        (
        [date] => 2012-05-26
        [visitsTotal] => 2
        [visits1] => 2
        [orders1] => 0
        [conversion1] => 0
        [visits6] => 0
        [orders6] => 0
        [conversion6] => 0
        [visits9] => 0
        [orders9] => 0
        [conversion9] => 0
        [ordersTotal] => 0
        ) )
         */
        $sql = "
        	SELECT
        		datum as `date`,
        		SUM(s.uniquevisits) AS `totalVisits`,
        		$sqlSelect
        		(SELECT COUNT(DISTINCT id) FROM s_order WHERE s_order.status NOT IN (4,-1) AND DATE(s_order.ordertime) = datum) AS `totalOrders`
        	FROM
        		`s_statistics_visitors` AS s
        	WHERE datum <= ?
            AND datum >= ?
        	GROUP BY `date`
        	ORDER BY `date` DESC
       ";

        $result = Shopware()->Db()->fetchAll($sql,array($toDate, $fromDate));

        // Reformat result to use date as key
        $basicStats = array();
        foreach ($result as $row) {
            $row["totalConversion"] = round($row["totalOrders"] / $row["totalVisits"] * 100, 2);
            $basicStats[$row["date"]] = $row;
        }

        /**
         * If shop selection is active, get visitors and orders for each shop
         * Merge results into $basicStats Array
         */
        if (!empty($shopIds)) {
            foreach ($shopIds as $shopId) {
                $sql = "
                SELECT datum AS `date`,
                uniquevisits AS visits
                FROM s_statistics_visitors WHERE shopID = ?
                ";

                $result = Shopware()->Db()->fetchAll($sql, array($shopId));

                foreach ($result as $row) {
                    $basicStats[$row["date"]]["visits" . $shopId] = $row["visits"];
                }

                $sql = "
                    SELECT
                        DATE(ordertime) as `date`,
                        COUNT(o.id) AS `orders`
                    FROM
                        `s_order` AS o
                    WHERE subshopID = ? AND status NOT IN (4,-1)
                    GROUP BY DATE(ordertime)
                    ORDER BY DATE(ordertime) DESC
               ";
                $result = Shopware()->Db()->fetchAll($sql, array($shopId));
                if (!empty($result)) {
                    foreach ($result as $row) {
                        $basicStats[$row["date"]]["orders" . $shopId] = $row["orders"];
                        if (!empty($basicStats[$row["date"]]["visits" . $shopId])) {
                            $basicStats[$row["date"]]["conversion" . $shopId] = round($row["orders"] / $basicStats[$row["date"]]["visits" . $shopId] * 100, 2);
                        } else {
                            $basicStats[$row["date"]]["conversion" . $shopId] = 0;
                        }
                    }
                }
            }
        }

        foreach ($basicStats as &$row) $row["date"] = strtotime($row["date"]);
        $this->View()->total = count($basicStats);
        $basicStats = array_splice($basicStats, $start, $limit);
        $this->View()->data = array_values($basicStats);
        $this->View()->success = true;

    }



    /**
     * helper to get the selected shop ids
     * if no shop is selected the ids of all shops are returned
     *
     * return array | shopIds
     */
    private function getSelectedShopIds(){
        $selectedShopIds = $this->Request()->getParam('selectedShops');
        if(!empty($selectedShopIds)) {
            $selectedShopIds = explode(",",$selectedShopIds);
            return $selectedShopIds;
        }
        $sql = '
            SELECT s.id
            FROM s_core_shops s
            ORDER BY s.default DESC, s.name
        ';
        return Shopware()->Db()->fetchCol($sql);
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

    /**
     * returns the found rows of the last mysql SQL_CALC_FOUND_ROWS function
     * @return int
     */
    private function getFoundRows()
    {
        $sql= "SELECT FOUND_ROWS()";
        return (int)Shopware()->Db()->fetchOne($sql);
    }
}
