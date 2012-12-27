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
 * @package    Shopware_Controllers
 * @subpackage Order
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
/**
 *
 */
class Shopware_Controllers_Backend_PayOne extends Shopware_Controllers_Backend_Order
{
    /**
     * Event listener method which fires when the order store is loaded. Returns an array of order data
     * which displayed in an Ext.grid.Panel. The order data contains all associations of an order (positions, shop, customer, ...).
     * The limit, filter and order parameter are used in the id query. The result of the id query are used
     * to filter the detailed query which created over the getListQuery function.
     */
    public function getListAction()
    {
        //read store parameter to filter and paginate the data.
        $limit = $this->Request()->getParam('limit', 20);
        $offset = $this->Request()->getParam('start', 0);
        $sort = $this->Request()->getParam('sort', null);
        $filter = $this->Request()->getParam('filter', array());
        $orderId = $this->Request()->getParam('orderID');
        
        if(!is_null($orderId)) {
            $orderIdFilter = array('property' => 'orders.id', 'value' => $orderId);
            $filter[] = $orderIdFilter;
        }
        
        $builder = Shopware()->Models()->createQueryBuilder();
        $builder->select(array('payment.id'))
                ->from('Shopware\Models\Payment\Payment', 'payment')
                ->where('payment.name = :name')
                ->setParameter('name', 'BuiswPaymentPayone')
                ->setFirstResult(0)
                ->setMaxResults(1);

        $payOneId = $builder->getQuery()->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        if (!empty($payOneId)) {
            $filter[] = array('property' => 'payment.id', 'value' => $payOneId['id']);
        }
        $list = $this->getList($filter, $sort, $offset, $limit);
        $this->View()->assign($list);
    }



}
