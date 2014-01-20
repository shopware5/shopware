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

    protected $format = null;

    public function preDispatch()
    {
        if ($this->Request()->has('format')) {
            $this->format = $this->Request()->getParam('format', null);
        }
        parent::preDispatch();
    }

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

        $this->send($data, $result->getTotalCount());
    }

    public function getRatingAction()
    {
        $result = $this->getRepository()->getOrdersOfVisitors(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
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
        $this->send($data, $result->getTotalCount());

    }

    public function getReferrerRevenueAction()
    {
        $shop = $this->getManager()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
        $shop->registerResources(Shopware()->Bootstrap());

        $result = $this->getRepository()->getReferrerRevenue(
            $shop,
            $this->getFromDate(),
            $this->getToDate()
        );

        $referrer = array();
        $customers = array();
        foreach ($result->getData() as $row) {
            $url = parse_url($row['referrer']);
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

            if (!in_array($row['userID'], $customers)) {
                if (strtotime($row['orderTime']) - strtotime($row['firstLogin']) < 60 * 60 * 24) {
                    $referrer[$host]['entireNewRevenue'] += $row['revenue'];
                    $referrer[$host]['newCustomers']++;
                } else {
                    $referrer[$host]['entireOldRevenue'] += $row['revenue'];
                    $referrer[$host]['oldCustomers']++;
                }

                $referrer[$host]['customerRevenue'] += $row['revenue'];
            }

            $referrer[$host]['entireRevenue'] += $row['revenue'];
            $referrer[$host]['orderCount']++;
        }

        foreach ($referrer as &$ref) {
            $ref['lead'] = round($ref['entireRevenue'] / $ref['orderCount'], 2);
            $ref['perNewRevenue'] = round($ref['entireNewRevenue'] / $ref['newCustomers'], 2);
            $ref['perOldRevenue'] = round($ref['entireOldRevenue'] / $ref['oldCustomers'], 2);
            $ref['customerValue'] = round($ref['customerRevenue'] / ($ref['newCustomers'] + $ref['oldCustomers']), 2);
        }

        $this->send(array_values($referrer), $result->getTotalCount());
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

        $this->send($data, $result->getTotalCount());
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

        $this->send(array_values($referrer), $result->getTotalCount());
    }

    public function getArticleSellsAction()
    {
        $result = $this->getRepository()->getProductSells(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 25),
            $this->getFromDate(),
            $this->getToDate()
        );

        $this->send($result->getData(), $result->getTotalCount());
    }

    public function getReferrerSearchTermsAction()
    {
        $selectedReferrer = (string)$this->Request()->getParam('selectedReferrer');

        $result = $this->getRepository()->getReferrerSearchTerms($selectedReferrer);

        $keywords = array();
        foreach ($result->getData() as $data) {
            preg_match_all("#[?&]([qp]|query|highlight|encquery|url|field-keywords|as_q|sucheall|satitle|KW)=([^&\\$]+)#", utf8_encode($data['referrer']) . "&", $matches);
            if (empty($matches[0])) {
                continue;
            }

            $ref = $matches[2][0];
            $ref = html_entity_decode(rawurldecode(strtolower($ref)));
            $ref = str_replace('+', ' ', $ref);
            $ref = trim(preg_replace('/\s\s+/', ' ', $ref));

            if (!array_key_exists($ref, $keywords)) {
                $keywords[$ref] = array(
                    'keyword' => $ref,
                    'count' => 0
                );
            }

            $keywords[$ref]['count']++;
        }

        $keywords = array_values($keywords);

        $this->send($keywords, count($keywords));
    }

    public function getSearchUrlsAction()
    {
        $selectedReferrer = (string)$this->Request()->getParam('selectedReferrer');

        $result = $this->getRepository()->getReferrerUrls(
            $selectedReferrer,
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 25)
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

        $this->send(array_values($customers), count($customers));
    }

    public function getCustomerAgeAction()
    {
        $shopIds = $this->getSelectedShopIds();
        $result = $this->getRepository()->getAgeOfCustomers(
            $this->getFromDate(),
            $this->getToDate(),
            $shopIds
        );

        $subShopCounts = array();
        $ages = array();
        foreach ($result->getData() as $row) {
            $age = floor((time() - strtotime($row['birthday'])) / (60 * 60 * 24 * 365));

            if (!array_key_exists("{$age}", $ages)) {
                $ages["{$age}"] = array(
                    'age' => $age,
                    'count' => 0
                );
            }

            if (!empty($shopIds)) {
                foreach ($shopIds as $shopId) {
                    if (!array_key_exists($shopId, $subShopCounts)) {
                        $subShopCounts[$shopId] = 0;
                    }

                    if (!array_key_exists('count' . $shopId, $ages["{$age}"])) {
                        $ages["{$age}"]['count' . $shopId] = 0;
                    }

                    if (!empty($row['birthday' . $shopId])) {
                        $ages["{$age}"]['count' . $shopId]++;
                        $subShopCounts[$shopId]++;
                    }
                }
            }

            $ages["{$age}"]['count']++;
        }

        foreach ($ages as &$age) {
            $age['percent'] = round($age['count'] / $result->getTotalCount() * 100, 2);

            if (!empty($shopIds)) {
                foreach ($shopIds as $shopId) {
                    $age['percent' . $shopId] = round($age['count' . $shopId] / $subShopCounts[$shopId] * 100, 2);
                }
            }
        }

        $this->send(
            array_values($ages),
            count($ages)
        );
    }

    public function getMonthAction()
    {
        $result = $this->getRepository()->getAmountPerMonth(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->send(
            $this->formatOrderAnalyticsData($result->getData()),
            $result->getTotalCount()
        );
    }

    public function getCalendarWeeksAction()
    {
        $result = $this->getRepository()->getAmountPerCalendarWeek(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->send(
            $this->formatOrderAnalyticsData($result->getData()),
            $result->getTotalCount()
        );
    }

    public function getWeekdaysAction()
    {
        $result = $this->getRepository()->getAmountPerWeekday(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->send(
            $this->formatOrderAnalyticsData($result->getData()),
            $result->getTotalCount()
        );
    }

    public function getTimeAction()
    {
        $result = $this->getRepository()->getAmountPerHour(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->send(
            $this->formatOrderAnalyticsData($result->getData()),
            $result->getTotalCount()
        );
    }


    public function getCountriesAction()
    {
        $result = $this->getRepository()->getAmountPerCountry(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->send(
            $this->formatOrderAnalyticsData($result->getData()),
            $result->getTotalCount()
        );
    }

    public function getPaymentAction()
    {
        $result = $this->getRepository()->getAmountPerPayment(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->send(
            $this->formatOrderAnalyticsData($result->getData()),
            $result->getTotalCount()
        );
    }

    public function getShippingMethodsAction()
    {
        $result = $this->getRepository()->getAmountPerShipping(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->send(
            $this->formatOrderAnalyticsData($result->getData()),
            $result->getTotalCount()
        );
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

        $this->send(
            $result->getData(),
            $result->getTotalCount()
        );
    }


    public function getVendorsAction()
    {
        $result = $this->getRepository()->getProductAmountPerManufacturer(
            $this->getFromDate(),
            $this->getToDate()
        );

        $this->send(
            $result->getData(),
            $result->getTotalCount()
        );
    }


    public function getSearchTermsAction()
    {
        $result = $this->getRepository()->getSearchTerms(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 25),
            $this->Request()->getParam('sort', array(
                array(
                    'property' => 'countRequests',
                    'direction' => 'DESC'
                )
            ))
        );

        $this->send(
            $result->getData(),
            $result->getTotalCount()
        );
    }

    public function getVisitorsAction()
    {
        $result = $this->getRepository()->getVisitorImpressions(
            $this->getFromDate(),
            $this->getToDate(),
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 25),
            $this->Request()->getParam('sort', array(
                array(
                    'property' => 'datum',
                    'direction' => 'DESC'
                )
            )),
            $this->getSelectedShopIds()
        );

        $this->send(
            $result->getData(),
            $result->getTotalCount()
        );
    }

    public function getArticleImpressionsAction()
    {
        $result = $this->getRepository()->getProductImpressions(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 20),
            $this->getFromDate(),
            $this->getToDate(),
            $this->Request()->getParam('sort', array(
                array(
                    'property' => 'totalAmount',
                    'direction' => 'DESC'
                )
            )),
            $this->getSelectedShopIds()
        );

        $this->send(
            $result->getData(),
            $result->getTotalCount()
        );
    }

    public function getCustomerGroupAmountAction()
    {
        $result = $this->getRepository()->getCustomerGroupAmount(
            $this->getFromDate(),
            $this->getToDate()
        );

        $this->send(
            $result->getData(),
            $result->getTotalCount()
        );
    }

    protected function send($data, $totalCount)
    {
        if (strtolower($this->format) == 'csv') {
            $this->exportCSV($data);
        } else {
            $this->View()->assign(array(
                'success' => true,
                'data' => $data,
                'total' => $totalCount
            ));
        }
    }

    protected function exportCSV($data)
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);
        $this->Response()->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $this->Response()->setHeader('Content-Disposition', 'attachment;filename=' . $this->getCsvFileName());

        echo "\xEF\xBB\xBF";
        $fp = fopen('php://output', 'w');

        fputcsv($fp, array_keys($data[0]), ";");

        foreach ($data as $value) {
            if (empty($value)) {
                continue;
            }
            fputcsv($fp, $value, ";");
        }
        fclose($fp);
    }

    private function getCsvFileName()
    {
        $name = $this->Request()->getActionName();
        if (strpos($name, 'get') == 0) {
            $name = substr($name, 3);
        }

        return $this->underscoreToCamelCase($name) . '.csv';
    }

    private function underscoreToCamelCase($str)
    {
        $str[0] = strtolower($str[0]);
        $func = function ($c) {
            return '_' . strtolower($c[1]);
        };

        return preg_replace_callback('/([A-Z])/', $func, $str);
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
}

