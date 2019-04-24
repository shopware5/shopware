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

use Shopware\Models\Article\Article;

class Shopware_Controllers_Backend_Notification extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * returns a JSON string to with all found articles for the backend listing
     */
    public function getArticleListAction()
    {
        try {
            $limit = (int) $this->Request()->limit;
            $offset = (int) $this->Request()->start;

            /** @var array $filter */
            $filter = $this->Request()->getParam('filter', []);

            //order data
            $order = (array) $this->Request()->getParam('sort', []);

            /** @var \Shopware\Models\Article\Repository $repository */
            $repository = Shopware()->Models()->getRepository(Article::class);
            $dataQuery = $repository->getArticlesWithRegisteredNotificationsQuery($filter, $offset, $limit, $order);
            $data = $dataQuery->getArrayResult();

            // manually calc the totalAmount cause the paginate(Shopware()->Models()->getQueryCount) doesn't work with this query
            $dataQuery->setFirstResult(0);
            $totalCount = count($dataQuery->getArrayResult());

            $summaryQuery = $repository->getArticlesWithRegisteredNotificationsQuery($filter, $offset, $limit, $order, true);
            $summaryData = $summaryQuery->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            $this->View()->assign(
                [
                    'success' => true,
                    'data' => $data,
                    'totalCount' => $totalCount,
                    'totalRegistered' => $summaryData['registered'],
                    'totalNotNotified' => $summaryData['notNotified'],
                ]
            );
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * returns a JSON string to with all found customers for the backend listing
     */
    public function getCustomerListAction()
    {
        try {
            $limit = (int) $this->Request()->limit;
            $offset = (int) $this->Request()->start;
            $productOrderNumber = $this->Request()->orderNumber;

            /** @var array $filter */
            $filter = $this->Request()->getParam('filter', []);

            //order data
            $order = (array) $this->Request()->getParam('sort', []);

            /** @var \Shopware\Models\Article\Repository $repository */
            $repository = Shopware()->Models()->getRepository(Article::class);
            $dataQuery = $repository->getNotificationCustomerByArticleQuery($productOrderNumber, $filter, $offset, $limit, $order);
            $totalCount = Shopware()->Models()->getQueryCount($dataQuery);
            $data = $dataQuery->getArrayResult();

            $this->View()->assign(['success' => true, 'data' => $data, 'totalCount' => $totalCount]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'errorMsg' => $e->getMessage()]);
        }
    }

    /**
     * Registers the different acl permission for the different controller actions.
     */
    protected function initAcl()
    {
        /*
         * permission to list all notifications
         */
        $this->addAclPermission('getArticleList', 'read', 'Insufficient Permissions');
        $this->addAclPermission('getCustomerList', 'read', 'Insufficient Permissions');
    }
}
