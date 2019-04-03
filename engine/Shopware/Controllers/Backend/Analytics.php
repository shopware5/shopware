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

use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Analytics\Repository;
use Shopware\Models\Shop\Shop;

class Shopware_Controllers_Backend_Analytics extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    protected $dateFields = [
        'date', 'displayDate',  'firstLogin', 'birthday', 'orderTime',
    ];

    protected $shopFields = [
        'amount', 'count', 'totalImpressions', 'totalVisits', 'orderCount', 'visitors',
    ];

    /**
     * Entity Manager
     *
     * @var \Shopware\Components\Model\ModelManager
     */
    protected $manager;

    /**
     * @var \Shopware\Models\Shop\Repository
     */
    protected $shopRepository;

    /**
     * @var Repository|null
     */
    protected $repository;

    /**
     * @var string
     */
    protected $format;

    public function preDispatch()
    {
        if ($this->Request()->has('format')) {
            $this->format = $this->Request()->getParam('format');

            // Remove limit parameter to export all data.
            $this->Request()->setParam('limit', null);
        }
        parent::preDispatch();
    }

    public function init()
    {
        parent::init();
        $currency = Shopware()->Db()->fetchRow(
            'SELECT templatechar as sign, (symbol_position = 16) currencyAtEnd
            FROM s_core_currencies
            WHERE standard = 1'
        );

        $this->View()->assign('analyticsCurrency', $currency);
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'getOverview',
            'getRating',
            'getReferrerRevenue',
            'getPartnerRevenue',
            'getCustomerGroupAmount',
            'getReferrerVisitors',
            'getArticleSales',
            'getCustomers',
            'getCustomerAge',
            'getMonth',
            'getCalendarWeeks',
            'getWeekdays',
            'getTime',
            'getCategories',
            'getCountries',
            'getPayment',
            'getShippingMethods',
            'getVendors',
            'getDevice',
            'getSearchTerms',
            'getVisitors',
            'getArticleImpressions',
            'getReferrerSearchTerms',
        ];
    }

    /**
     * @deprecated since 5.6 will be private in 5.8
     * Helper Method to get access to the shop repository.
     *
     * @return Shopware\Models\Shop\Repository
     */
    public function getShopRepository()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if ($this->shopRepository === null) {
            $this->shopRepository = $this->getManager()->getRepository(Shop::class);
        }

        return $this->shopRepository;
    }

    /**
     * @deprecated since 5.6, will be private in 5.8
     *
     * @return Repository
     */
    public function getRepository()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        if (!$this->repository) {
            $this->repository = new Repository(
                $this->get('models')->getConnection(),
                $this->get('events')
            );
        }

        return $this->repository;
    }

    /**
     * Get a list of installed shops
     */
    public function shopListAction()
    {
        $builder = $this->getShopsQueryBuilder();
        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign([
            'data' => $data,
            'success' => true,
        ]);
    }

    public function getOverviewAction()
    {
        $turnover = $this->getRepository()->getDailyTurnover(
            $this->getFromDate(),
            $this->getToDate()
        );

        $visitors = $this->getRepository()->getDailyVisitors(
            $this->getFromDate(),
            $this->getToDate()
        );

        $registrations = $this->getRepository()->getDailyRegistrations(
            $this->getFromDate(),
            $this->getToDate()
        );

        $turnover = array_map('reset', $turnover->getData());
        $visitors = array_map('reset', $visitors->getData());
        $registrations = array_map('reset', $registrations->getData());

        $data = array_merge_recursive($turnover, $visitors);
        $data = array_merge_recursive($data, $registrations);
        $data = $this->prepareOverviewData($data);

        krsort($data);

        foreach ($data as $date => &$row) {
            $row['date'] = strtotime($date);
            if ($row['visits'] != 0) {
                $row['conversion'] = round($row['orderCount'] / $row['visits'] * 100, 2);
            } else {
                $row['conversion'] = 0;
            }
        }

        // Sets the correct limit
        $limit = 25;
        if (strtolower($this->format) === 'csv') {
            $limit = count($data);
        }

        $values = array_values($data);
        $splice = array_splice(
            $values,
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', $limit)
        );

        $this->send($splice, count($data));
    }

    public function getRatingAction()
    {
        $shopIds = $this->getSelectedShopIds();
        $visitors = $this->getRepository()->getDailyShopVisitors(
            $this->getFromDate(),
            $this->getToDate(),
            $shopIds
        );
        $visitors = array_map('reset', $visitors->getData());

        $orders = $this->getRepository()->getDailyShopOrders(
            $this->getFromDate(),
            $this->getToDate(),
            $shopIds
        );

        $orders = array_map('reset', $orders->getData());
        $data = array_merge_recursive($orders, $visitors);

        foreach ($data as $date => &$row) {
            $row['date'] = strtotime($date);
            $orders = $row['orderCount'];
            $visitors = $row['visits'];
            $cancelledOrders = $row['cancelledOrders'];

            if (($cancelledOrders + $orders) != 0) {
                $row['basketConversion'] = round($orders / ($cancelledOrders + $orders) * 100, 2);
            } else {
                $row['basketConversion'] = 0;
            }

            if ($visitors != 0) {
                $row['orderConversion'] = round($orders / $visitors * 100, 2);
                $row['basketVisitConversion'] = round($cancelledOrders / $visitors * 100, 2);
            } else {
                $row['orderConversion'] = 0;
                $row['basketVisitConversion'] = 0;
            }

            foreach ($shopIds as $shopId) {
                $orders = $row['orderCount' . $shopId];
                $visitors = $row['visits' . $shopId];
                $cancelledOrders = $row['cancelledOrders' . $shopId];

                if (($cancelledOrders + $orders) != 0) {
                    $row['basketConversion' . $shopId] = round($orders / ($cancelledOrders + $orders) * 100, 2);
                } else {
                    $row['basketConversion' . $shopId] = 0;
                }

                if ($visitors != 0) {
                    $row['orderConversion' . $shopId] = round($orders / $visitors * 100, 2);
                    $row['basketVisitConversion' . $shopId] = round($cancelledOrders / $visitors * 100, 2);
                } else {
                    $row['orderConversion' . $shopId] = 0;
                    $row['basketVisitConversion' . $shopId] = 0;
                }
            }
        }

        $values = array_values($data);
        $splice = array_splice(
            $values,
            (int) $this->Request()->getParam('start', 0),
            (int) $this->Request()->getParam('limit', 25)
        );

        $this->send($splice, count($data));
    }

    public function getReferrerRevenueAction()
    {
        $shop = $this->getManager()->getRepository(Shop::class)->getActiveDefault();
        $this->get('shopware.components.shop_registration_service')->registerShop($shop);

        $result = $this->getRepository()->getReferrerRevenue(
            $shop,
            $this->getFromDate(),
            $this->getToDate()
        );

        $referrer = [];
        $customers = [];
        foreach ($result->getData() as $row) {
            $url = parse_url($row['referrer']);
            $host = $url['host'];

            if (!array_key_exists($host, $referrer)) {
                $referrer[$host] = [
                    'host' => $host,
                    'orderCount' => 0,
                    'turnover' => 0,
                    'average' => 0,
                    'newCustomers' => 0,
                    'turnoverNewCustomer' => 0,
                    'averageNewCustomer' => 0,
                    'regularCustomers' => 0,
                    'turnoverRegularCustomer' => 0,
                    'averageRegularCustomer' => 0,
                ];
            }

            if (!in_array($row['userID'], $customers)) {
                if (strtotime($row['orderTime']) - strtotime($row['firstLogin']) < 60 * 60 * 24) {
                    $referrer[$host]['turnoverNewCustomer'] += $row['turnover'];
                    ++$referrer[$host]['newCustomers'];
                } else {
                    $referrer[$host]['turnoverRegularCustomer'] += $row['turnover'];
                    ++$referrer[$host]['regularCustomers'];
                }
            }

            $referrer[$host]['turnover'] += $row['turnover'];
            ++$referrer[$host]['orderCount'];
        }

        foreach ($referrer as &$ref) {
            if ($ref['orderCount'] != 0) {
                $ref['average'] = round($ref['turnover'] / $ref['orderCount'], 2);
            } else {
                $ref['average'] = 0;
            }

            if ($ref['newCustomers'] != 0) {
                $ref['averageNewCustomer'] = round($ref['turnoverNewCustomer'] / $ref['newCustomers'], 2);
            } else {
                $ref['averageNewCustomer'] = 0;
            }

            if ($ref['regularCustomers'] != 0) {
                $ref['averageRegularCustomer'] = round($ref['turnoverRegularCustomer'] / $ref['regularCustomers'], 2);
            } else {
                $ref['averageRegularCustomer'] = 0;
            }
        }

        // Sort the multidimensional array
        usort($referrer, function ($a, $b) {
            return $a['turnover'] < $b['turnover'];
        });

        $this->send(
            array_values($referrer),
            $this->Request()->getParam('limit', 25)
        );
    }

    public function getPartnerRevenueAction()
    {
        $result = $this->getRepository()->getPartnerRevenue(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit'),
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

    public function getCustomerGroupAmountAction()
    {
        $result = $this->getRepository()->getCustomerGroupAmount(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->send(
            $result->getData(),
            $result->getTotalCount()
        );
    }

    public function getReferrerVisitorsAction()
    {
        $result = $this->getRepository()->getVisitedReferrer(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit'),
            $this->getFromDate(),
            $this->getToDate()
        );

        $data = $result->getData();

        $referrer = [];
        foreach ($data as &$row) {
            $host = parse_url($row['referrer']);
            $host = str_replace('www.', '', $host['host']);

            if (!array_key_exists($host, $referrer)) {
                $referrer[$host] = [
                    'count' => 0,
                    'referrer' => $host,
                ];
            }

            ++$referrer[$host]['count'];
        }

        $this->send(array_values($referrer), $result->getTotalCount());
    }

    public function getArticleSalesAction()
    {
        $result = $this->getRepository()->getProductSales(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit'),
            $this->getFromDate(),
            $this->getToDate()
        );

        $this->send($result->getData(), $result->getTotalCount());
    }

    public function getCustomersAction()
    {
        $result = $this->getRepository()->getOrdersOfCustomers(
            $this->getFromDate(),
            $this->getToDate()
        );

        /** @var array<string, mixed> $customers */
        $customers = [];
        /** @var array<string, mixed> $users */
        $users = [];

        foreach ($result->getData() as $row) {
            $week = $row['orderTime'];
            ++$customers[$week]['orderCount'];
            $customers[$week]['week'] = $week;
            $customers[$week]['female'] = (int) $customers[$week]['female'];
            $customers[$week]['male'] = (int) $customers[$week]['male'];
            $customers[$week]['registration'] = (int) $customers[$week]['registration'];
            $users[$week] = (array) $users[$week];

            switch (strtolower($row['salutation'])) {
                case 'mr':
                    $customers[$week]['male']++;
                    break;
                default:
                    $customers[$week]['female']++;
                    break;
            }

            if ($row['isNewCustomerOrder'] && !in_array($row['userId'], $users[$week])) {
                ++$customers[$week]['registration'];
            }

            $users[$week][] = $row['userId'];

            if ($row['isNewCustomerOrder']) {
                ++$customers[$week]['newCustomersOrders'];
            } else {
                ++$customers[$week]['oldCustomersOrders'];
            }
        }

        $this->send(
            array_values($customers),
            $this->Request()->getParam('limit', 25)
        );
    }

    public function getCustomerAgeAction()
    {
        $shopIds = $this->getSelectedShopIds();
        $result = $this->getRepository()->getAgeOfCustomers(
            $this->getFromDate(),
            $this->getToDate(),
            $shopIds
        );

        $subShopCounts = [];
        $ages = [];
        foreach ($result->getData() as $row) {
            $age = floor((time() - strtotime($row['birthday'])) / (60 * 60 * 24 * 365));

            if (!array_key_exists("$age", $ages)) {
                $ages["$age"] = [
                    'age' => $age,
                    'count' => 0,
                ];
            }

            if (!empty($shopIds)) {
                foreach ($shopIds as $shopId) {
                    if (!array_key_exists($shopId, $subShopCounts)) {
                        $subShopCounts[$shopId] = 0;
                    }

                    if (!array_key_exists('count' . $shopId, $ages["$age"])) {
                        $ages["$age"]['count' . $shopId] = 0;
                    }

                    if (!empty($row['birthday' . $shopId])) {
                        ++$ages["$age"]['count' . $shopId];
                        ++$subShopCounts[$shopId];
                    }
                }
            }

            ++$ages["$age"]['count'];
        }

        foreach ($ages as &$age) {
            if ($result->getTotalCount() != 0) {
                $age['percent'] = round($age['count'] / $result->getTotalCount() * 100, 2);
            } else {
                $age['percent'] = 0;
            }

            if (!empty($shopIds)) {
                foreach ($shopIds as $shopId) {
                    if ($subShopCounts[$shopId] != 0) {
                        $age['percent' . $shopId] = round($age['count' . $shopId] / $subShopCounts[$shopId] * 100, 2);
                    } else {
                        $age['percent' . $shopId] = 0;
                    }
                }
            }
        }

        $this->send(
            array_values($ages),
            $this->Request()->getParam('limit', 0)
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
            $this->Request()->getParam('limit', 0)
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

    public function getCategoriesAction()
    {
        $node = $this->Request()->getParam('node', 'root');
        $node = $node === 'root' ? 1 : (int) $node;

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

    public function getVendorsAction()
    {
        $result = $this->getRepository()->getProductAmountPerManufacturer(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit'),
            $this->getFromDate(),
            $this->getToDate()
        );

        $this->send(
            $result->getData(),
            $result->getTotalCount()
        );
    }

    /**
     * Returns the sales amount grouped per device type
     */
    public function getDeviceAction()
    {
        $result = $this->getRepository()->getProductAmountPerDevice(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->send(
            $this->formatOrderAnalyticsData($result->getData()),
            $result->getTotalCount()
        );
    }

    public function getSearchTermsAction()
    {
        $result = $this->getRepository()->getSearchTerms(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit'),
            $this->getFromDate(),
            $this->getToDate(),
            $this->Request()->getParam('sort', [
                [
                    'property' => 'countRequests',
                    'direction' => 'DESC',
                ],
            ]),
            $this->getSelectedShopIds()
        );

        $this->send(
            $result->getData(),
            $result->getTotalCount()
        );
    }

    public function getVisitorsAction()
    {
        $result = $this->getRepository()->getVisitorImpressions(
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit'),
            $this->getFromDate(),
            $this->getToDate(),
            $this->Request()->getParam('sort', [
                [
                    'property' => 'datum',
                    'direction' => 'DESC',
                ],
            ]),
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
            $this->Request()->getParam('limit'),
            $this->getFromDate(),
            $this->getToDate(),
            $this->Request()->getParam('sort', [
                [
                    'property' => 'totalImpressions',
                    'direction' => 'DESC',
                ],
            ]),
            $this->getSelectedShopIds()
        );

        $this->send(
            $result->getData(),
            $result->getTotalCount()
        );
    }

    public function getReferrerSearchTermsAction()
    {
        $selectedReferrer = (string) $this->Request()->getParam('selectedReferrer');

        $result = $this->getRepository()->getReferrerSearchTerms($selectedReferrer);

        $keywords = [];
        foreach ($result->getData() as $data) {
            preg_match_all('#[?&]([qp]|query|highlight|encquery|url|field-keywords|as_q|sucheall|satitle|KW)=([^&\$]+)#', utf8_encode($data['referrer']) . '&', $matches);
            if (empty($matches[0])) {
                continue;
            }

            $ref = $matches[2][0];
            $ref = html_entity_decode(rawurldecode(strtolower($ref)));
            $ref = str_replace('+', ' ', $ref);
            $ref = trim(preg_replace('/\s\s+/', ' ', $ref));

            if (!array_key_exists($ref, $keywords)) {
                $keywords[$ref] = [
                    'keyword' => $ref,
                    'count' => 0,
                ];
            }

            ++$keywords[$ref]['count'];
        }

        $keywords = array_values($keywords);

        $this->send($keywords, count($keywords));
    }

    public function getSearchUrlsAction()
    {
        $selectedReferrer = (string) $this->Request()->getParam('selectedReferrer');

        $result = $this->getRepository()->getReferrerUrls(
            $selectedReferrer,
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit')
        );

        $this->View()->assign([
            'success' => true,
            'data' => $result->getData(),
            'totalCount' => $result->getTotalCount(),
        ]);
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

    protected function send($data, $totalCount)
    {
        if (strtolower($this->format) == 'csv') {
            $data = $this->formatCsvData($data);
            $this->exportCSV($data);
        } else {
            $this->View()->assign([
                'success' => true,
                'data' => $data,
                'total' => $totalCount,
            ]);
        }
    }

    protected function getShopFields($data)
    {
        $ids = $this->getSelectedShopIds();
        $fields = [];
        foreach (array_keys($data) as $key) {
            if (in_array($key, $this->shopFields)) {
                foreach ($ids as $id) {
                    if (array_key_exists($key . $id, $data)) {
                        $fields[$key . $id] = $id;
                    }
                }
            }
        }

        return $fields;
    }

    protected function getDateFields($data)
    {
        $fields = [];
        foreach (array_keys($data) as $key) {
            if (in_array($key, $this->dateFields)) {
                $fields[] = $key;
            }
        }

        return $fields;
    }

    protected function exportCSV($data)
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);
        $this->Response()->headers->set('content-type', 'text/csv; charset=utf-8');
        $this->Response()->headers->set('content-disposition', 'attachment;filename=' . $this->getCsvFileName());

        echo "\xEF\xBB\xBF";
        $fp = fopen('php://output', 'w');

        fputcsv($fp, array_keys($data[0]), ';');

        foreach ($data as $value) {
            if (empty($value)) {
                continue;
            }
            fputcsv($fp, $value, ';');
        }
        fclose($fp);
    }

    /**
     * Internal helper function to get access to the entity manager.
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    private function getManager()
    {
        if ($this->manager === null) {
            $this->manager = Shopware()->Models();
        }

        return $this->manager;
    }

    /**
     * Returns the query builder to fetch all available stores
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function getShopsQueryBuilder()
    {
        $builder = $this->getManager()->getDBALQueryBuilder();
        $builder->select([
            's.id',
            's.name',
            'c.currency',
            'c.name AS currencyName',
            'c.templateChar AS currencyChar',
            '(c.symbol_position = 16) currencyAtEnd',
        ])
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
            $row['orderCount'] = (int) $row['orderCount'];
            $row['turnover'] = (float) $row['turnover'];

            if (!empty($row['date'])) {
                $row['normal'] = $row['date'];
                $row['date'] = strtotime($row['date']);
            }

            if (!empty($shopIds)) {
                foreach ($shopIds as $shopId) {
                    $row['turnover' . $shopId] = (float) $row['turnover' . $shopId];
                }
            }
        }

        return $data;
    }

    private function formatCsvData($data)
    {
        if ($fields = $this->getDateFields($data[0])) {
            foreach ($data as &$row) {
                foreach ($fields as $field) {
                    if (array_key_exists($field, $row)) {
                        $row[$field] = date('Y-m-d H:i:s', $row[$field]);
                    }
                }
            }
        }

        if ($fields = $this->getShopFields($data[0])) {
            $shopNames = $this->getShopNames();

            foreach ($fields as $field => $shopId) {
                $suffix = substr($field, 0, strlen($fields) - strlen($shopId));
                $data = $this->switchArrayKeys($data, $shopNames[$shopId] . ' (' . $suffix . ')', $field);
            }
        }

        return $data;
    }

    private function switchArrayKeys($array, $newKey, $oldKey)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->switchArrayKeys($value, $newKey, $oldKey);
            } else {
                $array[$newKey] = $array[$oldKey];
            }
        }
        unset($array[$oldKey]);

        return $array;
    }

    private function getShopNames()
    {
        $builder = $this->getManager()->getDBALQueryBuilder();
        $builder->select(['s.id', 's.name'])
            ->from('s_core_shops', 's')
            ->orderBy('s.default', 'DESC')
            ->addOrderBy('s.name');

        $statement = $builder->execute();

        return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
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
        $selectedShopIds = (string) $this->Request()->getParam('selectedShops');

        if (!empty($selectedShopIds)) {
            return explode(',', $selectedShopIds);
        }

        return [];
    }

    /**
     * helper to get the from date in the right format
     *
     * return \DateTimeInterface | fromDate
     */
    private function getFromDate()
    {
        $fromDate = $this->Request()->getParam('fromDate');
        if (empty($fromDate)) {
            $fromDate = new \DateTime();
            $fromDate = $fromDate->sub(new \DateInterval('P1M'));
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
        $toDate = $toDate->add(new \DateInterval('P1D'));
        $toDate = $toDate->sub(new \DateInterval('PT1S'));

        return $toDate;
    }

    /**
     * fills empty array elements for the csv export
     *
     * @param array $data
     *
     * @return array
     */
    private function prepareOverviewData($data)
    {
        foreach ($data as &$row) {
            if (!isset($row['orderCount'])) {
                $row = $this->insertArrayAtPosition(['orderCount' => 0], $row, 0);
            }
            if (!isset($row['turnover'])) {
                $row = $this->insertArrayAtPosition(['turnover' => 0], $row, 1);
            }
            if (!isset($row['clicks'])) {
                $row = $this->insertArrayAtPosition(['clicks' => 0], $row, 2);
            }
            if (!isset($row['visits'])) {
                $row = $this->insertArrayAtPosition(['visits' => 0], $row, 3);
            }
            if (!isset($row['registrations'])) {
                $row = $this->insertArrayAtPosition(['registrations' => 0], $row, 4);
            }
            if (!isset($row['customers'])) {
                $row = $this->insertArrayAtPosition(['customers' => 0], $row, 5);
            }
        }

        return $data;
    }

    /**
     * helper method which allows to insert an array element with a key
     *
     * @param array $insertValue
     * @param array $array
     * @param int   $position
     *
     * @return array
     */
    private function insertArrayAtPosition($insertValue, $array, $position)
    {
        return array_slice($array, 0, $position, true) +
                $insertValue +
                array_slice($array, $position, count($array), true);
    }
}
