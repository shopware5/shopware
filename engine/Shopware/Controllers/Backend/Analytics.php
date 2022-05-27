<?php

declare(strict_types=1);

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

use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Analytics\Repository as AnalyticsRepository;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class Shopware_Controllers_Backend_Analytics extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @var array<string>
     */
    protected $dateFields = [
        'date', 'displayDate',  'firstLogin', 'birthday', 'orderTime',
    ];

    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @var array<string>
     */
    protected $shopFields = [
        'amount', 'count', 'totalImpressions', 'totalVisits', 'orderCount', 'visitors',
    ];

    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @var ModelManager|null
     */
    protected $manager;

    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @var ShopRepository|null
     */
    protected $shopRepository;

    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @var AnalyticsRepository|null
     */
    protected $repository;

    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @var string
     */
    protected $format = '';

    /**
     * @return void
     */
    public function preDispatch()
    {
        if ($this->Request()->has('format')) {
            $this->format = (string) $this->Request()->getParam('format');

            // Remove limit parameter to export all data.
            $this->Request()->setParam('limit', null);
        }
        parent::preDispatch();
    }

    /**
     * @return void
     */
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
     * @deprecated - Will be private in Shopware 5.8
     * Helper Method to get access to the shop repository.
     *
     * @return ShopRepository
     */
    public function getShopRepository()
    {
        if ($this->shopRepository === null) {
            $this->shopRepository = $this->getManager()->getRepository(Shop::class);
        }

        return $this->shopRepository;
    }

    /**
     * @return AnalyticsRepository
     *
     * @deprecated - Will be private in Shopware 5.8
     */
    public function getRepository()
    {
        if (!$this->repository) {
            $this->repository = new AnalyticsRepository(
                $this->get(ModelManager::class)->getConnection(),
                $this->get('events')
            );
        }

        return $this->repository;
    }

    /**
     * Get a list of installed shops
     *
     * @return void
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

    /**
     * @return void
     */
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
            $limit = \count($data);
        }

        $values = array_values($data);
        $splice = array_splice(
            $values,
            (int) $this->Request()->getParam('start'),
            (int) $this->Request()->getParam('limit', $limit)
        );

        $this->send($splice, \count($data));
    }

    /**
     * @return void
     */
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
            $orders = (int) $row['orderCount'];
            $visitors = (int) $row['visits'];
            $cancelledOrders = (int) $row['cancelledOrders'];

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
                $orders = (int) $row['orderCount' . $shopId];
                $visitors = (int) $row['visits' . $shopId];
                $cancelledOrders = (int) $row['cancelledOrders' . $shopId];

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

        $this->send($splice, \count($data));
    }

    /**
     * @return void
     */
    public function getReferrerRevenueAction()
    {
        $shop = $this->getManager()->getRepository(Shop::class)->getActiveDefault();
        $this->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

        $result = $this->getRepository()->getReferrerRevenue(
            $shop,
            $this->getFromDate(),
            $this->getToDate()
        );

        $referrer = [];
        $customers = [];
        foreach ($result->getData() as $row) {
            $url = parse_url($row['referrer']);
            if (!\is_array($url) || !\array_key_exists('host', $url)) {
                continue;
            }
            $host = $url['host'];

            if (!\array_key_exists($host, $referrer)) {
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

            if (!\in_array($row['userID'], $customers)) {
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
            if ($ref['orderCount'] !== 0) {
                $ref['average'] = round($ref['turnover'] / $ref['orderCount'], 2);
            } else {
                $ref['average'] = 0;
            }

            if ($ref['newCustomers'] !== 0) {
                $ref['averageNewCustomer'] = round($ref['turnoverNewCustomer'] / $ref['newCustomers'], 2);
            } else {
                $ref['averageNewCustomer'] = 0;
            }

            if ($ref['regularCustomers'] !== 0) {
                $ref['averageRegularCustomer'] = round($ref['turnoverRegularCustomer'] / $ref['regularCustomers'], 2);
            } else {
                $ref['averageRegularCustomer'] = 0;
            }
        }

        // Sort the multidimensional array
        usort($referrer, static function ($a, $b) {
            return $a['turnover'] <=> $b['turnover'];
        });

        $this->send(
            array_values($referrer),
            (int) $this->Request()->getParam('limit', 25)
        );
    }

    /**
     * @return void
     */
    public function getPartnerRevenueAction()
    {
        $result = $this->getRepository()->getPartnerRevenue(
            (int) $this->Request()->getParam('start', 0),
            $this->getLimit(),
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function getReferrerVisitorsAction()
    {
        $result = $this->getRepository()->getVisitedReferrer(
            (int) $this->Request()->getParam('start', 0),
            $this->getLimit(),
            $this->getFromDate(),
            $this->getToDate()
        );

        $data = $result->getData();

        $referrer = [];
        foreach ($data as &$row) {
            $url = parse_url($row['referrer']);
            if (!\is_array($url) || !\array_key_exists('host', $url)) {
                continue;
            }
            $host = str_replace('www.', '', $url['host']);
            if (!\is_string($host)) {
                continue;
            }

            if (!\array_key_exists($host, $referrer)) {
                $referrer[$host] = [
                    'count' => 0,
                    'referrer' => $host,
                ];
            }

            ++$referrer[$host]['count'];
        }

        $this->send(array_values($referrer), $result->getTotalCount());
    }

    /**
     * @return void
     */
    public function getArticleSalesAction()
    {
        $result = $this->getRepository()->getProductSales(
            (int) $this->Request()->getParam('start', 0),
            $this->getLimit(),
            $this->getFromDate(),
            $this->getToDate()
        );

        $this->send($result->getData(), $result->getTotalCount());
    }

    /**
     * @return void
     */
    public function getCustomersAction()
    {
        $result = $this->getRepository()->getOrdersOfCustomers(
            $this->getFromDate(),
            $this->getToDate()
        );

        $customers = [];
        $users = [];

        foreach ($result->getData() as $row) {
            $week = $row['orderTime'];
            ++$customers[$week]['orderCount'];
            $customers[$week]['week'] = $week;
            $customers[$week]['female'] = (int) $customers[$week]['female'];
            $customers[$week]['male'] = (int) $customers[$week]['male'];
            $customers[$week]['registration'] = (int) $customers[$week]['registration'];
            $customers[$week]['newCustomersOrders'] = (int) $customers[$week]['newCustomersOrders'];
            $customers[$week]['oldCustomersOrders'] = (int) $customers[$week]['oldCustomersOrders'];
            $users[$week] = (array) $users[$week];

            switch (strtolower($row['salutation'])) {
                case 'mr':
                    $customers[$week]['male']++;
                    break;
                default:
                    $customers[$week]['female']++;
                    break;
            }

            if ($row['isNewCustomerOrder'] && !\in_array($row['userId'], $users[$week])) {
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
            (int) $this->Request()->getParam('limit', 25)
        );
    }

    /**
     * @return void
     */
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

            if (!\array_key_exists("$age", $ages)) {
                $ages["$age"] = [
                    'age' => $age,
                    'count' => 0,
                ];
            }

            if (!empty($shopIds)) {
                foreach ($shopIds as $shopId) {
                    if (!\array_key_exists($shopId, $subShopCounts)) {
                        $subShopCounts[$shopId] = 0;
                    }

                    if (!\array_key_exists('count' . $shopId, $ages["$age"])) {
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
            if ((int) $result->getTotalCount() !== 0) {
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
            (int) $this->Request()->getParam('limit', 0)
        );
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function getCalendarWeeksAction()
    {
        $result = $this->getRepository()->getAmountPerCalendarWeek(
            $this->getFromDate(),
            $this->getToDate(),
            $this->getSelectedShopIds()
        );

        $this->send(
            $this->formatOrderAnalyticsData($result->getData()),
            (int) $this->Request()->getParam('limit', 0)
        );
    }

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
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

    /**
     * @return void
     */
    public function getVendorsAction()
    {
        $result = $this->getRepository()->getProductAmountPerManufacturer(
            (int) $this->Request()->getParam('start', 0),
            $this->getLimit(),
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
     *
     * @return void
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

    /**
     * @return void
     */
    public function getSearchTermsAction()
    {
        $result = $this->getRepository()->getSearchTerms(
            (int) $this->Request()->getParam('start', 0),
            $this->getLimit(),
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

    /**
     * @return void
     */
    public function getVisitorsAction()
    {
        $result = $this->getRepository()->getVisitorImpressions(
            (int) $this->Request()->getParam('start'),
            $this->getLimit(),
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

    /**
     * @return void
     */
    public function getArticleImpressionsAction()
    {
        $result = $this->getRepository()->getProductImpressions(
            (int) $this->Request()->getParam('start', 0),
            $this->getLimit(),
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

    /**
     * @return void
     */
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
            $replaced = preg_replace('/\s\s+/', ' ', $ref);
            if (!\is_string($replaced)) {
                continue;
            }
            $ref = trim($replaced);

            if (!\array_key_exists($ref, $keywords)) {
                $keywords[$ref] = [
                    'keyword' => $ref,
                    'count' => 0,
                ];
            }

            ++$keywords[$ref]['count'];
        }

        $keywords = array_values($keywords);

        $this->send($keywords, \count($keywords));
    }

    /**
     * @return void
     */
    public function getSearchUrlsAction()
    {
        $selectedReferrer = (string) $this->Request()->getParam('selectedReferrer');

        $result = $this->getRepository()->getReferrerUrls(
            $selectedReferrer,
            (int) $this->Request()->getParam('start', 0),
            $this->getLimit(),
        );

        $this->View()->assign([
            'success' => true,
            'data' => $result->getData(),
            'totalCount' => $result->getTotalCount(),
        ]);
    }

    /**
     * @return void
     */
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
     * @deprecated - Will be private in Shopware 5.8
     *
     * @param array<array<string, string|int|float>> $data
     * @param int                                    $totalCount
     *
     * @return void
     */
    protected function send($data, $totalCount)
    {
        if (strtolower($this->format) === 'csv') {
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

    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @param array<string, string|int|float>|null $data
     *
     * @return array<string, int>
     */
    protected function getShopFields($data)
    {
        if (!\is_array($data)) {
            return [];
        }

        $ids = $this->getSelectedShopIds();
        $fields = [];
        foreach (array_keys($data) as $key) {
            if (\in_array($key, $this->shopFields, true)) {
                foreach ($ids as $id) {
                    if (\array_key_exists($key . $id, $data)) {
                        $fields[$key . $id] = $id;
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @param array<string, string|int|float>|null $data
     *
     * @return array<string>
     */
    protected function getDateFields($data)
    {
        if (!\is_array($data)) {
            return [];
        }

        $fields = [];
        foreach (array_keys($data) as $key) {
            if (\in_array($key, $this->dateFields)) {
                $fields[] = $key;
            }
        }

        return $fields;
    }

    /**
     * @deprecated - Will be private in Shopware 5.8
     *
     * @param array<array<string, string|int|float>> $data
     *
     * @return void
     */
    protected function exportCSV($data)
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);
        $this->Response()->headers->set('content-type', 'text/csv; charset=utf-8');
        $this->Response()->headers->set('content-disposition', 'attachment;filename=' . $this->getCsvFileName());

        echo "\xEF\xBB\xBF";
        $fp = fopen('php://output', 'w');
        if (!\is_resource($fp)) {
            throw new RuntimeException('Could not open stream');
        }

        if (\is_array($data[0])) {
            fputcsv($fp, array_keys($data[0]), ';');
        }

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
     */
    private function getManager(): ModelManager
    {
        if ($this->manager === null) {
            $this->manager = $this->get('models');
        }

        return $this->manager;
    }

    /**
     * Returns the query builder to fetch all available stores
     */
    private function getShopsQueryBuilder(): QueryBuilder
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

    /**
     * @param array<array<string, string|int|float>> $data
     *
     * @return array<array<string, string|int|float>>
     */
    private function formatOrderAnalyticsData(array $data): array
    {
        $shopIds = $this->getSelectedShopIds();

        foreach ($data as &$row) {
            $row['orderCount'] = (int) $row['orderCount'];
            $row['turnover'] = (float) $row['turnover'];

            if (!empty($row['date'])) {
                $row['normal'] = $row['date'];
                $row['date'] = strtotime((string) $row['date']);
            }

            if (!empty($shopIds)) {
                foreach ($shopIds as $shopId) {
                    $row['orderCount' . $shopId] = (int) $row['orderCount' . $shopId];
                    $row['turnover' . $shopId] = (float) $row['turnover' . $shopId];
                }
            }
        }

        return $data;
    }

    /**
     * @param array<array<string, string|int|float>> $data
     *
     * @return array<array<string, string|int|float>>
     */
    private function formatCsvData(array $data): array
    {
        $fields = $this->getDateFields($data[0]);
        if ($fields !== []) {
            foreach ($data as &$row) {
                foreach ($fields as $field) {
                    if (\array_key_exists($field, $row)) {
                        $row[$field] = date('Y-m-d H:i:s', (int) $row[$field]);
                    }
                }
            }
        }

        $fields = $this->getShopFields($data[0]);
        if ($fields !== []) {
            $shopNames = $this->getShopNames();

            foreach ($fields as $field => $shopId) {
                $suffix = substr($field, 0, \strlen($field) - \strlen((string) $shopId));
                $data = $this->switchArrayKeys($data, $shopNames[$shopId] . ' (' . $suffix . ')', $field);
            }
        }

        return $data;
    }

    /**
     * The `$array` parameter has the "hacky" `array<mixed>` annotation because of the recursion in this method
     *
     * @param array<array<string, string|int|float>>|array<mixed> $array
     *
     * @return array<array<string, string|int|float>>
     */
    private function switchArrayKeys(array $array, string $newKey, string $oldKey): array
    {
        foreach ($array as $key => $value) {
            if (\is_array($value)) {
                $array[$key] = $this->switchArrayKeys($value, $newKey, $oldKey);
            } else {
                $array[$newKey] = $array[$oldKey];
            }
        }
        unset($array[$oldKey]);

        return $array;
    }

    /**
     * @return array<int, string>
     */
    private function getShopNames(): array
    {
        $builder = $this->getManager()->getDBALQueryBuilder();
        $builder->select(['s.id', 's.name'])
            ->from('s_core_shops', 's')
            ->orderBy('s.default', 'DESC')
            ->addOrderBy('s.name');

        return $builder->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function getCsvFileName(): string
    {
        $name = $this->Request()->getActionName();
        if (str_starts_with($name, 'get')) {
            $name = substr($name, 3);
        }

        return $this->camelCaseToUnderscore($name) . '.csv';
    }

    private function camelCaseToUnderscore(string $str): string
    {
        return (new CamelCaseToSnakeCaseNameConverter())->normalize($str);
    }

    /**
     * helper to get the selected shop ids
     * if no shop is selected the ids of all shops are returned
     *
     * @return array<int>
     */
    private function getSelectedShopIds(): array
    {
        $selectedShopIds = (string) $this->Request()->getParam('selectedShops');

        if ($selectedShopIds !== '') {
            $shopIds = explode(',', $selectedShopIds);

            return array_map('\intval', $shopIds);
        }

        return [];
    }

    private function getFromDate(): DateTime
    {
        $fromDate = $this->Request()->getParam('fromDate');
        if (empty($fromDate)) {
            $fromDate = new DateTime();
            $fromDate = $fromDate->sub(new DateInterval('P1M'));
        } else {
            $fromDate = new DateTime($fromDate);
        }

        return $fromDate;
    }

    private function getToDate(): DateTime
    {
        //if a "to" date passed, format it over the \DateTime object. Otherwise, create a new date with today
        $toDate = $this->Request()->getParam('toDate');
        if (empty($toDate)) {
            $toDate = new DateTime();
        } else {
            $toDate = new DateTime($toDate);
        }
        //to get the right value cause 2012-02-02 is smaller than 2012-02-02 15:33:12
        $toDate = $toDate->add(new DateInterval('P1D'));

        return $toDate->sub(new DateInterval('PT1S'));
    }

    /**
     * fills empty array elements for the csv export
     *
     * @param array<string, array{orderCount?: string, turnover?: string, clicks?: string, visits?: string, registrations?: string, customers?: string}> $data
     *
     * @return array<string, array{orderCount: int, turnover: float, clicks: int, visits: int, registrations: int, customers: int}>
     */
    private function prepareOverviewData(array $data): array
    {
        $preparedData = [];
        foreach ($data as $key => $row) {
            $newRow = $row;

            $newRow['orderCount'] = isset($row['orderCount']) ? (int) $row['orderCount'] : 0;
            $newRow['turnover'] = isset($row['turnover']) ? (float) $row['turnover'] : 0.0;
            $newRow['clicks'] = isset($row['clicks']) ? (int) $row['clicks'] : 0;
            $newRow['visits'] = isset($row['visits']) ? (int) $row['visits'] : 0;
            $newRow['registrations'] = isset($row['registrations']) ? (int) $row['registrations'] : 0;
            $newRow['customers'] = isset($row['customers']) ? (int) $row['customers'] : 0;

            $preparedData[$key] = $newRow;
        }

        return $preparedData;
    }

    private function getLimit(): ?int
    {
        if (!$this->Request()->has('limit')) {
            return null;
        }

        return (int) $this->Request()->getParam('limit');
    }
}
