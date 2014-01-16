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
use Shopware\Models\Analytics\Repository;

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

    /**
     * @var Repository
     */
    protected $repository = null;

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
     * @return Repository
     */
    public function getRepository()
    {
        if (!$this->repository) {
            $this->repository = new Repository(
                $this->get('models')->getConnection(),
                $this->get('events')
            );
        }

        return $this->repository;
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

    private function formatOrderAnalyticsData($data)
    {
        $shopIds = $this->getSelectedShopIds();

        foreach ($data as &$row) {
            $row['count'] = (int)$row['count'];
            $row['amount'] = (float)$row['amount'];

            if (!empty($row['date'])) {
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

        $this->View()->assign(array(
            'data' => $data,
            'success' => true
        ));
    }

    public function getOverviewAction()
    {
        $result = $this->getRepository()->getShopStatistic(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 25),
            $this->getFromDate(),
            $this->getToDate()
        );

        $data = $result->getData();
        $shopIds = $this->getSelectedShopIds();
        foreach ($data as &$row) {
            $row['date'] = strtotime($row['date']);
            $row['totalConversion'] = round($row['totalOrders'] / $row['totalVisits'] * 100, 2);

            if (!empty($shopIds)) {
                foreach ($shopIds as $shopId) {
                    $row['conversion' . $shopId] = round($row['orderCount' . $shopId] / $row['visits' . $shopId] * 100, 2);
                }
            }
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'totalCount' => $result->getTotalCount()
        ));
    }

    public function getRatingAction()
    {
        $result = $this->getRepository()->getOrdersOfVisitors(
            $this->getFromDate(),
            $this->getToDate()
        );

        $data = array();

        foreach ($result->getData() as $row) {
            $orders = $row['orderCount'];
            $visitors = $row['visitors'];
            $cancelledOrders = $row['cancelledOrders'];

            $data[] = array(
                'date' => strtotime($row['date']),
                'basketConversion' => round($orders / ($cancelledOrders + $orders) * 100, 2),
                'orderConversion' => round($orders / $visitors * 100, 2),
                'basketVisitConversion' => round($cancelledOrders / $visitors * 100, 2)
            );
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'totalCount' => $result->getTotalCount()
        ));
    }

    public function getReferrerRevenueAction()
    {
        $shop = $this->getManager()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
        $shop->registerResources(Shopware()->Bootstrap());

        $results = $this->getRepository()->getReferrerRevenue(
            $shop,
            $this->getFromDate(),
            $this->getToDate()
        );

        $referrer = array();
        $customers = array();
        foreach ($results as $result) {
            $url = parse_url($result['referrer']);
            $host = $url['host'];

            if (!array_key_exists($host, $referrer)) {
                $referrer[$host] = array(
                    'host' => $host,
                    'entireRevenue' => 0,
                    'lead' => 0,
                    'customerValue' => 0,
                    'entireNewRevenue' => 0,
                    'entireOldRevenue' => 0,
                    'orderCount' => 0,
                    'newCustomers' => 0,
                    'oldCustomers' => 0,
                    'perNewRevenue' => 0,
                    'perOldRevenue' => 0
                );
            }

            if (!in_array($result['userID'], $customers)) {
                if (strtotime($result['orderTime']) - strtotime($result['firstLogin']) < 60 * 60 * 24) {
                    $referrer[$host]['entireNewRevenue'] += $result['revenue'];
                    $referrer[$host]['newCustomers']++;
                } else {
                    $referrer[$host]['entireOldRevenue'] += $result['revenue'];
                    $referrer[$host]['oldCustomers']++;
                }

                $referrer[$host]['customerRevenue'] += $result['revenue'];
            }

            $referrer[$host]['entireRevenue'] += $result['revenue'];
            $referrer[$host]['orderCount']++;
        }

        foreach ($referrer as &$ref) {
            $ref['lead'] = round($ref['entireRevenue'] / $ref['orderCount'], 2);
            $ref['perNewRevenue'] = round($ref['entireNewRevenue'] / $ref['newCustomers'], 2);
            $ref['perOldRevenue'] = round($ref['entireOldRevenue'] / $ref['oldCustomers'], 2);
            $ref['customerValue'] = round($ref['customerRevenue'] / ($ref['newCustomers'] + $ref['oldCustomers']), 2);
        }

        $referrer = array_values($referrer);

        $this->View()->assign(array(
            'success' => true,
            'data' => $referrer,
            'totalCount' => count($referrer)
        ));
    }

    public function getPartnerRevenueAction()
    {
        $result = $this->getRepository()->getPartnerRevenue(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 25),
            $this->getFromDate(),
            $this->getToDate()
        );

        $data = $result->getData();

        foreach ($data as &$row) {
            if (empty($row['partner'])) {
                $row['partner'] = $row['trackingCode'];
            }
            if (empty($row['PartnerID'])) {
                $row['PartnerID'] = 0;
            }
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'totalCount' => $result->getTotalCount()
        ));
    }

    public function getReferrerVisitorsAction()
    {
        $result = $this->getRepository()->getVisitedReferrer(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 25),
            $this->getFromDate(),
            $this->getToDate()
        );

        $data = $result->getData();

        $referrer = array();
        foreach ($data as &$row) {
            $host = parse_url($row['referrer']);
            $host = str_replace('www.', '', $host['host']);

            if (!array_key_exists($host, $referrer)) {
                $referrer[$host] = array(
                    'count' => 0,
                    'referrer' => $host
                );
            }

            $referrer[$host]['count']++;
        }

        $referrer = array_values($referrer);

        $this->View()->assign(array(
            'success' => true,
            'data' => $referrer,
            'totalCount' => $result->getTotalCount()
        ));
    }

    public function getArticleSellsAction()
    {
        $result = $this->getRepository()->getProductSells(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 25),
            $this->getFromDate(),
            $this->getToDate()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $result->getData(),
            'totalCount' => $result->getTotalCount()
        ));
    }

    public function getCustomersAction()
    {
        $result = $this->getRepository()->getOrdersOfCustomers(
            $this->getFromDate(),
            $this->getToDate()
        );

        $customers = array();
        $data = $result->getData();

        foreach ($data as $row) {
            $week = date('Y - W', strtotime($row['orderTime']));

            if (!array_key_exists($week, $customers)) {
                $customers[$week] = array(
                    'week' => $week,
                    'newCustomersOrders' => 0,
                    'oldCustomersOrders' => 0,
                    'orderCount' => 0,
                    'male' => 0,
                    'female' => 0
                );
            }

            if (strtotime($row['orderTime']) - strtotime($row['firstLogin']) < 60 * 60 * 24) {
                $customers[$week]['newCustomersOrders'] += $row['count'];
            } else {
                $customers[$week]['oldCustomersOrders'] += $row['count'];
            }

            $customers[$week]['orderCount'] += $row['count'];

            if ($row['salutation'] == 'mr') {
                $customers[$week]['male']++;
            } else if ($row['salutation' == 'ms']) {
                $customers[$week]['female']++;
            }
        }

        foreach ($customers as &$entry) {
            $entry['amountNewCustomers'] = round($entry['newCustomersOrders'] / $entry['orderCount'] * 100, 2);
            $entry['amountOldCustomers'] = round($entry['oldCustomersOrders'] / $entry['orderCount'] * 100, 2);
            $entry['maleAmount'] = round($entry['male'] / ($entry['male'] + $entry['female']) * 100, 2);
            $entry['femaleAmount'] = round($entry['female'] / ($entry['male'] + $entry['female']) * 100, 2);
        }

        $customers = array_values($customers);
        $this->View()->assign(array(
            'success' => true,
            'data' => $customers,
            'totalCount' => count($customers)
        ));
    }

    public function getCustomerAgeAction()
    {
        $result = $this->getRepository()->getAgeOfCustomers(
            $this->getFromDate(),
            $this->getToDate()
        );

        $ages = array();
        foreach ($result->getData() as $row) {
            $age = floor((time() - strtotime($row['birthday'])) / (60 * 60 * 24 * 365));

            if (!array_key_exists("{$age}", $ages)) {
                $ages["{$age}"] = array(
                    'age' => $age,
                    'count' => 0
                );
            }

            $ages["{$age}"]['count']++;
        }

        foreach ($ages as &$age) {
            $age['percent'] = round($age['count'] / $result->getTotalCount() * 100, 2);
        }

        $ages = array_values($ages);

        $this->View()->assign(array(
            'success' => true,
            'data' => $ages,
            'totalCount' => $result->getTotalCount()
        ));
    }

    public function getMonthAction()
    {
        $result = $this->getRepository()->getAmountPerMonth(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $this->formatOrderAnalyticsData($result->getData()),
            'total' => $result->getTotalCount()
        ));
    }

    public function getCalendarWeeksAction()
    {
        $result = $this->getRepository()->getAmountPerCalendarWeek(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $this->formatOrderAnalyticsData($result->getData()),
            'total' => $result->getTotalCount()
        ));
    }

    public function getWeekdaysAction()
    {
        $result = $this->getRepository()->getAmountPerWeekday(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $this->formatOrderAnalyticsData($result->getData()),
            'total' => $result->getTotalCount()
        ));
    }

    public function getTimeAction()
    {
        $result = $this->getRepository()->getAmountPerHour(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $this->formatOrderAnalyticsData($result->getData()),
            'total' => $result->getTotalCount()
        ));
    }


    public function getCountriesAction()
    {
        $result = $this->getRepository()->getAmountPerCountry(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $this->formatOrderAnalyticsData($result->getData()),
            'total' => $result->getTotalCount()
        ));
    }

    public function getPaymentAction()
    {
        $result = $this->getRepository()->getAmountPerPayment(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $this->formatOrderAnalyticsData($result->getData()),
            'total' => $result->getTotalCount()
        ));
    }

    public function getShippingMethodsAction()
    {
        $result = $this->getRepository()->getAmountPerShipping(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $this->formatOrderAnalyticsData($result->getData()),
            'total' => $result->getTotalCount()
        ));
    }



    public function getCategoriesAction()
    {
        $node = $this->Request()->getParam('node', 'root');
        $node = $node === 'root' ? 1 : (int)$node;

        $result = $this->getRepository()->getProductAmountPerCategory(
            $node,
            $this->getFromDate(),
            $this->getToDate()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $result->getData(),
            'total' => $result->getTotalCount()
        ));
    }


    public function getVendorsAction()
    {
        $result = $this->getRepository()->getProductAmountPerManufacturer(
            $this->getFromDate(),
            $this->getToDate()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $result->getData(),
            'total' => $result->getTotalCount()
        ));
    }


    public function getSearchTermsAction()
    {
        $result = $this->getRepository()->getSearchTerms(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 25),
            $this->Request()->getParam('sort', array(
                array('property' => 'countRequests', 'direction' => 'DESC')
            ))
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $result->getData(),
            'total' => $result->getTotalCount()
        ));
    }

    public function getVisitorsAction()
    {
        $result = $this->getRepository()->getVisitorImpressionsInRange(
            $this->getFromDate(),
            $this->getToDate(),
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 25),
            $this->Request()->getParam('sort', array(
                array('property' => 'datum', 'direction' => 'DESC')
            )),
            $this->getSelectedShopIds()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $result->getData(),
            'total' => $result->getTotalCount()
        ));
    }

    public function getArticleImpressionsAction()
    {
        $result = $this->getRepository()->getProductImpressions(
            $this->getFromDate(),
            $this->getToDate(),
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 20),
            $this->Request()->getParam('sort', array(
                array('property' => 'totalAmount', 'direction' => 'DESC')
            )),
            $this->getSelectedShopIds()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $result->getData(),
            'total' => $result->getTotalCount()
        ));
    }

    /**
     * helper to get the selected shop ids
     * if no shop is selected the ids of all shops are returned
     *
     * return array | shopIds
     */
    private function getSelectedShopIds()
    {
        $selectedShopIds = (string)$this->Request()->getParam('selectedShops');

        if (!empty($selectedShopIds)) {
            return explode(',', $selectedShopIds);
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
    private function getFromDate()
    {
        $fromDate = $this->Request()->getParam('fromDate');
        if (empty($fromDate)) {
            $fromDate = new \DateTime();
            $fromDate = $fromDate->sub(new DateInterval('P1M'));
        } else {
            $fromDate = new \DateTime($fromDate);
        }

        return $fromDate;
    }

    /**
     * helper to get the to date in the right format
     *
     * return DateTime | toDate
     */
    private function getToDate()
    {
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

        return $toDate;
    }

    /**
     * helper method to add an limit to the query
     * @param \Doctrine\DBAL\Query\QueryBuilder | $builder
     */
    private function addLimitQuery($builder)
    {
        $builder->setFirstResult(intval($this->Request()->getParam('start', 0)));
        $builder->setMaxResults(intval($this->Request()->getParam('limit', 25)));
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
        if (empty($order)) {
            $builder->orderBy($defaultProperty, $defaultDirection);
        } else {
            $builder->orderBy($order[0]["property"], $order[0]["direction"]);
        }
    }
}

