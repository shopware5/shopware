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
        $start = (int)$this->Request()->getParam('start', 0);
        $limit = (int)$this->Request()->getParam('limit', 25);

        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array(
            'sv.datum AS date',
            'sv.pageimpressions AS clicks',
            'COUNT(o.id) AS orders',
            'o.invoice_amount AS revenue',
            'sv.uniquevisits AS visitors',
            'SUM(sv.uniquevisits) as totalVisits',
            'COUNT(DISTINCT o.id) AS totalOrders',
            '(
                SELECT COUNT(o2.invoice_amount)
                FROM s_order o2
                WHERE o2.status=-1
                AND DATE(o2.ordertime) = sv.datum
            ) AS cancelledOrders',
            'COUNT(DISTINCT u.id) AS newCustomers'
        ))
            ->from('s_statistics_visitors', 'sv')
            ->leftJoin('sv', 's_order', 'o', 'sv.datum = DATE(o.ordertime)AND o.status NOT IN (4, -1)')
            ->leftJoin('sv', 's_user', 'u', 'u.firstlogin = sv.datum')
            ->where('sv.datum >= :fromDate AND sv.datum <= :toDate')
            ->groupBy('sv.datum')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->setParameter(':fromDate', $this->getFromDate()->format("Y-m-d H:i:s"))
            ->setParameter(':toDate', $this->getToDate()->format("Y-m-d H:i:s"));

        $this->addLimitQuery($builder);
        $statement = $builder->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);

        $shopIds = $this->getSelectedShopIds();
        foreach ($data as &$row) {
            $row['date'] = strtotime($row['date']);
            $row['totalConversion'] = round($row['totalOrders'] / $row['totalVisits'] * 100, 2);

            if (!empty($shopIds)) {
                foreach ($shopIds as $shopId) {
                    $row['conversion' . $shopId] = round($row['orders' . $shopId] / $row['visits' . $shopId] * 100, 2);
                }
            }
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'totalCount' => $statement->rowCount()
        ));
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
            ->setParameter(':fromDate', $this->getFromDate()->format("Y-m-d H:i:s"))
            ->setParameter(':toDate', $this->getToDate()->format("Y-m-d H:i:s"));

        $statement = $builder->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $data = array();

        foreach ($results as $result) {
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

        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'totalCount' => $statement->rowCount()
        ));
    }

    public function getReferrerRevenueAction()
    {
        $shop = $this->getManager()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
        $shop->registerResources(Shopware()->Bootstrap());

        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array(
            'ROUND(o.invoice_amount / o.currencyFactor, 2) AS revenue',
            'u.id as userID',
            'o.referer AS referrer',
            'DATE(u.firstlogin) as firstLogin',
            'DATE(o.ordertime) as orderTime',
            '(
                SELECT o2.ordertime
                FROM s_order o2
                WHERE o2.userID = u.id
                ORDER BY o2.ordertime DESC
                LIMIT 1
            ) as firstOrder',
            '(
                SELECT ROUND(SUM(o3.invoice_amount / o3.currencyFactor), 2)
                FROM s_order o3
                WHERE o3.userID = u.id
                AND o3.status != 4
                AND o3.status != -1
            ) as customerRevenue'
        ))
            ->from('s_order', 'o')
            ->add('from', array(
                'table' => 's_user',
                'alias' => 'u',
            ), true)
            ->where('o.status != 4 AND o.status != -1')
            ->andWhere('o.userID = u.id')
            ->andWhere('o.ordertime >= :fromDate AND o.ordertime <= :toDate')
            ->andWhere("o.referer LIKE 'http%//%'")
            ->andWhere("o.referer NOT LIKE :hostname")
            ->orderBy('revenue')
            ->setParameter(':fromDate', $this->getFromDate()->format("Y-m-d H:i:s"))
            ->setParameter(':toDate', $this->getToDate()->format("Y-m-d H:i:s"))
            ->setParameter(':hostname', '%' . $shop->getHost() . '%');

        $statement = $builder->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

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
                    'orders' => 0,
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
            $referrer[$host]['orders']++;
        }

        foreach ($referrer as &$ref) {
            $ref['lead'] = round($ref['entireRevenue'] / $ref['orders'], 2);
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
        $start = (int)$this->Request()->getParam('start', 0);
        $limit = (int)$this->Request()->getParam('limit', 25);

        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array(
            'ROUND(SUM((o.invoice_amount - o.invoice_shipping) / o.currencyFactor), 2) AS revenue',
            'p.company AS partner',
            'o.partnerID as trackingCode',
            'p.id as partnerId'
        ))
            ->from('s_order', 'o')
            ->leftJoin('o', 's_emarketing_partner', 'p', 'p.idcode = o.partnerID')
            ->where('o.status NOT IN (-1, 4)')
            ->andWhere('o.ordertime >= :fromTime')
            ->andWhere('o.ordertime <= :toTime')
            ->andWhere("o.partnerID != ''")
            ->groupBy('o.partnerID')
            ->orderBy('revenue', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->setParameter(':fromTime', $this->getFromDate()->format("Y-m-d H:i:s"))
            ->setParameter(':toTime', $this->getToDate()->format("Y-m-d H:i:s"));

        $statement = $builder->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as &$result) {
            if (empty($result['partner'])) {
                $result['partner'] = $result['trackingCode'];
            }
            if (empty($result['PartnerID'])) {
                $result['PartnerID'] = 0;
            }
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $results,
            'totalCount' => $statement->rowCount()
        ));
    }

    public function getReferrerVisitorsAction()
    {
        $start = (int)$this->Request()->getParam('start', 0);
        $limit = (int)$this->Request()->getParam('limit', 25);

        $shop = $this->getManager()->getRepository('Shopware\Models\Shop\Shop')->getActiveDefault();
        $shop->registerResources(Shopware()->Bootstrap());

        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array(
            'COUNT(r.referer) as count',
            'r.referer as referrer'
        ))
            ->from('s_statistics_referer', 'r')
            ->where('r.datum >= :fromTime')
            ->andWhere('r.datum <= :toTime')
            ->groupBy('referer')
            ->orderBy('count', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->setParameter(':fromTime', $this->getFromDate()->format("Y-m-d H:i:s"))
            ->setParameter(':toTime', $this->getToDate()->format("Y-m-d H:i:s"))
            ->setParameter(':hostname', '%' . $shop->getHost() . '%');

        $statement = $builder->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $referrer = array();
        foreach ($results as &$result) {
            $host = parse_url($result['referrer']);
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
            'totalCount' => $statement->rowCount()
        ));
    }

    public function getArticleSellsAction()
    {
        $start = (int)$this->Request()->getParam('start', 0);
        $limit = (int)$this->Request()->getParam('limit', 25);

        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array(
            'SUM(od.quantity) AS sellCount',
            'a.name',
            'od.articleordernumber as ordernumber'
        ))
            ->from('s_order_details', 'od')
            ->innerJoin('od', 's_articles', 'a', 'a.id = od.articleID')
            ->innerJoin('od', 's_order', 'o', 'o.id = od.orderID')
            ->where('o.ordertime >= :fromTime')
            ->andWhere('o.ordertime <= :toTime')
            ->andWhere('o.status NOT IN (-1, 4)')
            ->groupBy('a.id')
            ->orderBy('sellCount', 'DESC')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->setParameter(':fromTime', $this->getFromDate()->format("Y-m-d H:i:s"))
            ->setParameter(':toTime', $this->getToDate()->format("Y-m-d H:i:s"));

        $statement = $builder->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $this->View()->assign(array(
            'success' => true,
            'data' => $results,
            'totalCount' => $statement->rowCount()
        ));
    }

    public function getCustomersAction()
    {
        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array(
            'u.firstlogin AS firstLogin',
            'o.ordertime AS orderTime',
            'COUNT(o.id) AS count',
            'ub.salutation'
        ))
            ->from('s_user', 'u')
            ->innerJoin('u', 's_order', 'o', 'o.userID = u.id')
            ->innerJoin('u', 's_user_billingaddress', 'ub', 'ub.userID = u.id')
            ->where('o.ordertime >= :fromTime')
            ->andWhere('o.ordertime <= :toTime')
            ->andWhere('o.status NOT IN (-1, 4)')
            ->groupBy('u.id')
            ->orderBy('orderTime', 'DESC')
            ->setParameter(':fromTime', $this->getFromDate()->format("Y-m-d H:i:s"))
            ->setParameter(':toTime', $this->getToDate()->format("Y-m-d H:i:s"));

        $statement = $builder->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $data = array();
        foreach ($results as $result) {
            $week = date('Y - W', strtotime($result['orderTime']));

            if (!array_key_exists($week, $data)) {
                $data[$week] = array(
                    'week' => $week,
                    'newCustomersOrders' => 0,
                    'oldCustomersOrders' => 0,
                    'orders' => 0,
                    'male' => 0,
                    'female' => 0
                );
            }

            if (strtotime($result['orderTime']) - strtotime($result['firstLogin']) < 60 * 60 * 24) {
                $data[$week]['newCustomersOrders'] += $result['count'];
            } else {
                $data[$week]['oldCustomersOrders'] += $result['count'];
            }

            $data[$week]['orders'] += $result['count'];

            if ($result['salutation'] == 'mr') {
                $data[$week]['male']++;
            } else if ($result['salutation' == 'ms']) {
                $data[$week]['female']++;
            }
        }

        foreach ($data as &$entry) {
            $entry['amountNewCustomers'] = round($entry['newCustomersOrders'] / $entry['orders'] * 100, 2);
            $entry['amountOldCustomers'] = round($entry['oldCustomersOrders'] / $entry['orders'] * 100, 2);
            $entry['maleAmount'] = round($entry['male'] / ($entry['male'] + $entry['female']) * 100, 2);
            $entry['femaleAmount'] = round($entry['female'] / ($entry['male'] + $entry['female']) * 100, 2);
        }

        $data = array_values($data);
        $this->View()->assign(array(
            'success' => true,
            'data' => $data,
            'totalCount' => count($data)
        ));
    }

    public function getCustomerAgeAction()
    {
        $builder = Shopware()->Models()->getDBALQueryBuilder();
        $builder->select(array(
            'u.firstlogin',
            'ub.birthday'
        ))
            ->from('s_user', 'u')
            ->innerJoin('u', 's_user_billingaddress', 'ub', 'ub.userID = u.id')
            ->where('u.firstlogin >= :fromTime')
            ->andWhere('u.firstlogin <= :toTime')
            ->andWhere('ub.birthday IS NOT NULL')
            ->andWhere("ub.birthday != '0000-00-00'")
            ->orderBy('birthday', 'DESC')
            ->setParameter(':fromTime', $this->getFromDate()->format("Y-m-d H:i:s"))
            ->setParameter(':toTime', $this->getToDate()->format("Y-m-d H:i:s"));

        $statement = $builder->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $ages = array();
        foreach ($results as &$result) {
            $age = floor((time() - strtotime($result['birthday'])) / (60 * 60 * 24 * 365));

            if (!array_key_exists("{$age}", $ages)) {
                $ages["{$age}"] = array(
                    'age' => $age,
                    'count' => 0
                );
            }

            $ages["{$age}"]['count']++;
        }

        foreach ($ages as &$age) {
            $age['percent'] = round($age['count'] / $statement->rowCount() * 100, 2);
        }

        $ages = array_values($ages);
        $this->View()->assign(array(
            'success' => true,
            'data' => $ages,
            'totalCount' => count($ages)
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
        $result = $this->getRepository()->getVisitorsInRange(
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
        $result = $this->getRepository()->getProductImpressionOfRange(
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
    private function getFromDate()
    {
        $fromDate = $this->Request()->getParam('fromDate');
        if (empty($fromDate)) {
            $fromDate = new \DateTime();
            $fromDate = $fromDate->sub(new DateInterval('P1M'));
        } else {
            $fromDate = new \DateTime($fromDate);
        }

        return new DateTime('2000-01-01 00:00:00');
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

        return new DateTime('2020-01-01 00:00:00');
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

