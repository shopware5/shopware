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

use Doctrine\ORM\AbstractQuery;
use Shopware\Components\CSRFWhitelistAware;
use Shopware\Models\Partner\Partner;

class Shopware_Controllers_Backend_Partner extends Shopware_Controllers_Backend_ExtJs implements CSRFWhitelistAware
{
    /**
     * Disable template engine for selected actions
     *
     * @codeCoverageIgnore
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (in_array($this->Request()->getActionName(), ['validateTrackingCode', 'mapCustomerAccount'])) {
            $this->Front()->Plugins()->Json()->setRenderer(false);
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'redirectToPartnerLink',
            'downloadStatistic',
        ];
    }

    /**
     * Returns a JSON string to with all found partner for the backend listing
     */
    public function getListAction()
    {
        try {
            $limit = (int) $this->Request()->limit;
            $offset = (int) $this->Request()->start;

            // Order data
            $order = (array) $this->Request()->getParam('sort', []);

            /** @var \Shopware\Models\Partner\Repository $repository */
            $repository = Shopware()->Models()->getRepository(Partner::class);
            $dataQuery = $repository->getListQuery($order, $offset, $limit);

            $totalCount = Shopware()->Models()->getQueryCount($dataQuery);
            $data = $dataQuery->getArrayResult();

            $this->View()->assign(['success' => true, 'data' => $data, 'totalCount' => $totalCount]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Returns a JSON string for the statistic overview
     */
    public function getStatisticListAction()
    {
        try {
            $limit = (int) $this->Request()->limit;
            $offset = (int) $this->Request()->start;
            $partnerId = (int) $this->Request()->partnerId;

            // Order data
            $order = (array) $this->Request()->getParam('sort', []);

            $fromDate = $this->getFromDate();
            $toDate = $this->getToDate();

            /** @var \Shopware\Models\Partner\Repository $repository */
            $repository = Shopware()->Models()->getRepository(Partner::class);
            $dataQuery = $repository->getStatisticListQuery($order, $offset, $limit, $partnerId, false, $fromDate, $toDate);

            $totalCount = $this->getStatisticListTotalCount($dataQuery);

            $data = $dataQuery->getArrayResult();

            $summaryQuery = $repository->getStatisticListQuery($order, $offset, $limit, $partnerId, true, $fromDate, $toDate);
            $summaryData = $summaryQuery->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

            $this->View()->assign(
                [
                    'success' => true,
                    'data' => $data,
                    'totalCount' => $totalCount,
                    'totalNetTurnOver' => $summaryData['netTurnOver'],
                    'totalProvision' => $summaryData['provision'],
                ]
            );
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * returns a JSON string to show all the partner detail information
     */
    public function getDetailAction()
    {
        /** @var array $filter */
        $filter = $this->Request()->getParam('filter', []);

        /** @var \Shopware\Models\Partner\Repository $repository */
        $repository = Shopware()->Models()->getRepository(Partner::class);

        $dataQuery = $repository->getDetailQuery($filter);
        $data = $dataQuery->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Returns a JSON string for the statistic chart
     */
    public function getChartDataAction()
    {
        $partnerId = (int) $this->Request()->partnerId;

        $fromDate = $this->getFromDate();
        $toDate = $this->getToDate();

        /** @var \Shopware\Models\Partner\Repository $repository */
        $repository = Shopware()->Models()->getRepository(Partner::class);

        // Get the information of the partner chart
        $dataQuery = $repository->getStatisticChartQuery($partnerId, $fromDate, $toDate);
        $data = $dataQuery->getArrayResult();

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * Creates or updates a new Partner
     */
    public function savePartnerAction()
    {
        $params = $this->Request()->getParams();

        $id = $this->Request()->id;

        if (!empty($id)) {
            // Edit Data
            $partnerModel = Shopware()->Models()->getRepository(Partner::class)->find($id);
        } else {
            // New Data
            $partnerModel = new Partner();
            $partnerModel->setDate('now');
        }
        unset($params['date']);
        $partnerModel->fromArray($params);

        try {
            Shopware()->Models()->persist($partnerModel);
            Shopware()->Models()->flush();

            /** @var \Shopware\Models\Partner\Repository $repository */
            $repository = Shopware()->Models()->getRepository(Partner::class);

            $filter = [['property' => 'id', 'value' => $partnerModel->getId()]];
            $dataQuery = $repository->getDetailQuery($filter);
            $data = $dataQuery->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);

            $this->View()->assign(['success' => true, 'data' => $data]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * return the customerId for the customer mapping
     */
    public function mapCustomerAccountAction()
    {
        $mapCustomerAccountValue = $this->Request()->mapCustomerAccountValue;

        /** @var \Shopware\Models\Partner\Repository $repository */
        $repository = Shopware()->Models()->getRepository(Partner::class);
        $dataQuery = $repository->getCustomerForMappingQuery($mapCustomerAccountValue);
        $customerData = $dataQuery->getOneOrNullResult(AbstractQuery::HYDRATE_ARRAY);
        $userId = $customerData['id'];
        unset($customerData['id']);
        if (!empty($customerData)) {
            echo implode(', ', array_filter($customerData)) . '|' . $userId;
        }
    }

    /**
     * Deletes a Partner from the database
     */
    public function deletePartnerAction()
    {
        try {
            /** @var \Shopware\Models\Partner\Partner $model */
            $model = Shopware()->Models()->getRepository(Partner::class)->find($this->Request()->id);
            Shopware()->Models()->remove($model);
            Shopware()->Models()->flush();
            $this->View()->assign(['success' => true, 'data' => $this->Request()->getParams()]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Validate the Tracking code to prevent that it already exists
     */
    public function validateTrackingCodeAction()
    {
        $trackingCode = $this->Request()->value;
        $partnerId = (int) $this->Request()->param;

        /** @var \Shopware\Models\Partner\Repository $repository */
        $repository = Shopware()->Models()->getRepository(Partner::class);
        $foundPartner = $repository->getValidateTrackingCodeQuery($trackingCode, $partnerId);
        $foundPartnerArray = $foundPartner->getArrayResult();
        echo empty($foundPartnerArray);
    }

    /**
     * Exports the Statistic Data of the partner via CSV
     */
    public function downloadStatisticAction()
    {
        $this->Front()->Plugins()->Json()->setRenderer(false);
        $partnerId = (int) $this->Request()->partnerId;

        /** @var \Shopware\Models\Partner\Repository $repository */
        $repository = Shopware()->Models()->getRepository(Partner::class);
        $dataQuery = $repository->getStatisticListQuery(null, null, null, $partnerId, false, $this->getFromDate(), $this->getToDate());
        $resultArray = $dataQuery->getArrayResult();

        $this->Response()->headers->set('content-type', 'text/csv; charset=utf-8');
        $this->Response()->headers->set('content-disposition', 'attachment;filename=partner_statistic.csv');
        // Use this to set the BOM to show it in the right way for excel and stuff
        echo "\xEF\xBB\xBF";
        $fp = fopen('php://output', 'w');
        if (is_array($resultArray[0])) {
            fputcsv($fp, array_keys($resultArray[0]), ';');
        }

        foreach ($resultArray as $value) {
            $date = $value['orderTime']->format('d-m-Y');
            $value['orderTime'] = $date;
            $value['netTurnOver'] = number_format((float) $value['netTurnOver'], 2, ',', '.');
            $value['provision'] = number_format((float) $value['provision'], 2, ',', '.');
            fputcsv($fp, $value, ';');
        }
        fclose($fp);
    }

    /**
     * Will redirect to the frontend to execute the partner link
     */
    public function redirectToPartnerLinkAction()
    {
        $partnerId = $this->Request()->getParam('sPartner');

        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repository->getActiveDefault();

        if (!$shop instanceof \Shopware\Models\Shop\Shop) {
            throw new Exception('Invalid shop provided.');
        }

        $this->get('shopware.components.shop_registration_service')->registerShop($shop);

        $url = $this->Front()->Router()->assemble(['module' => 'frontend', 'controller' => 'index']);

        $this->redirect($url . '?sPartner=' . urlencode($partnerId));
    }

    /**
     * Registers the different acl permission for the different controller actions.
     */
    protected function initAcl()
    {
        /*
         * permission to list all partner
         */
        $this->addAclPermission('getList', 'read', 'Insufficient Permissions');

        /*
         * permission to view the statistic information's and downloads
         */
        $this->addAclPermission('getStatisticList', 'statistic', 'Insufficient Permissions');
        $this->addAclPermission('getChartData', 'statistic', 'Insufficient Permissions');
        $this->addAclPermission('downloadStatistic', 'statistic', 'Insufficient Permissions');

        /*
         * permission to show detail information of a partner
         */
        $this->addAclPermission('getDetail', 'read', 'Insufficient Permissions');

        /*
         * permission to delete the partner
         */
        $this->addAclPermission('deletePartner', 'delete', 'Insufficient Permissions');
    }

    /**
     * Helper function returns total count of the passed query builder
     *
     * @return int|null
     */
    private function getStatisticListTotalCount(\Doctrine\ORM\Query $dataQuery)
    {
        //userCurrencyFactor has not to be part of the count parameters
        $originalParameters = $dataQuery->getParameters();
        $countParameters = new \Doctrine\Common\Collections\ArrayCollection();

        /** @var \Doctrine\ORM\Query\Parameter $parameter */
        foreach ($originalParameters as $parameter) {
            if ($parameter->getName() === 'userCurrencyFactor') {
                continue;
            }

            $countParameters->add($parameter);
        }

        $dataQuery->setParameters($countParameters);
        $totalCount = Shopware()->Models()->getQueryCount($dataQuery);
        $dataQuery->setParameters($originalParameters);

        return $totalCount;
    }

    /**
     * Helper to get the from date in the right format
     *
     * @return \DateTime
     */
    private function getFromDate()
    {
        $fromDate = $this->Request()->getParam('fromDate');
        if (empty($fromDate)) {
            $fromDate = new \DateTime();
            $fromDate = $fromDate->sub(new DateInterval('P1Y'));
        } else {
            $fromDate = new \DateTime($fromDate);
        }

        return $fromDate;
    }

    /**
     * helper to get the to date in the right format
     *
     * @return \DateTime
     */
    private function getToDate()
    {
        // If a to date passed, format it over the \DateTime object. Otherwise create a new date with today
        $toDate = $this->Request()->getParam('toDate');
        if (empty($toDate)) {
            $toDate = new \DateTime();
        } else {
            $toDate = new \DateTime($toDate);
        }
        // To get the right value cause 2012-02-02 is smaller than 2012-02-02 15:33:12
        return $toDate->add(new DateInterval('P1D'));
    }
}
