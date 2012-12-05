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
 */

/**
 * Shopware SwagAboCommerce Plugin - Backend Controller
 *
 * @category  Shopware
 * @package   Shopware\Plugins\SwagAboCommerce\Controllers\Backend
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Backend_AboCommerce extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Getter of the entity manager property.
     *
     * @return \Shopware\Components\Model\ModelManager
     */
    protected function getEntityManager()
    {
        return Shopware()->Models();
    }

    /**
     * Getter of the aboCommerceRepository property.
     *
     * @return \Shopware\CustomModels\SwagAboCommerce\Repository
     */
    public function getAboCommerceRepository()
    {
        return $this->getEntityManager()->getRepository('Shopware\CustomModels\SwagAboCommerce\Article');
    }

    /**
     * Getter of the articleRepostiroy property.
     *
     * @return \Shopware\Models\Article\Repository
     */
    public function getArticleRepository()
    {
        return $this->getEntityManager()->getRepository('Shopware\Models\Article\Article');
    }

    /**
     * Global interface to create a new AboCommerce.
     */
    public function createAboCommerceAction()
    {
        $this->View()->assign(
            $this->saveAboCommerce(
                $this->Request()->getParam('articleId'),
                $this->Request()->getParams()
            )
        );
    }

    /**
     * Global interface to update an existing AboCommerce record.
     */
    public function updateAboCommerceAction()
    {
        $this->View()->assign(
            $this->saveAboCommerce(
                $this->Request()->getParam('articleId'),
                $this->Request()->getParams()
            )
        );
    }

    /**
     * @param $articleId
     * @param $params
     *
     * @return array
     */
    public function saveAboCommerce($articleId, $params)
    {
        $aboArticle = $this->getAboCommerceRepository()
                           ->getDetailQueryBuilder($articleId)->getQuery()->getOneOrNullResult();

        if (empty($aboArticle)) {
            $article = $this->getArticleRepository()->find($articleId);
            $aboArticle = new \Shopware\CustomModels\SwagAboCommerce\Article();
            $aboArticle->setArticle($article);
            $this->getEntityManager()->persist($aboArticle);
        }

        $aboArticle->fromArray($params);

        $this->getEntityManager()->flush();

        $aboArticle = $this->getAboCommerceRepository()
                           ->getDetailQueryBuilder($articleId)->getQuery()->getArrayResult();

        return array(
            'success' => true,
            'data'    => $aboArticle,
        );
    }



    /**
     * Global interface to get the whole data for a single AboCommerce record.
     *
     * @return bool
     */
    public function getDetailAction()
    {
        $articleId = $this->Request()->getParam('articleId');

        $article = $this->getAboCommerceRepository()->getDetailQueryBuilder($articleId)->getQuery()->getArrayResult();

        $this->View()->assign(array(
            'success' => true,
            'data'    => $article,
        ));
    }

    /**
     * Internal function to get the whole data for a single AboCommerce record.
     * The AboCommerce record will be identified over the
     * passed id parameter. The second parameter "$hydrationMode" can be use to control the result data type.
     *
     * @param     $id
     * @param int $hydrationMode
     *
     * @return array
     */
    protected function getDetail($id, $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY)
    {
        try {
            $builder = $this->getAboCommerceRepository()
                            ->getDetailQueryBuilder($id);

            $query = $builder->getQuery();

            $query->setHydrationMode($hydrationMode);

            $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);

            $records = $paginator->getIterator()->getArrayCopy();

            return array(
                'success' => true,
                'data' => $records[0]
            );
        } catch (Exception $e) {
            return array(
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
    }

    /**
     *
     */
    public function createOrderAction()
    {
        $aboId = $this->Request()->getParam('aboId');

        $uri = $this->Request()->getScheme()
                . '://'
                . $this->Request()->getHttpHost()
                . rtrim($this->Request()->getBasePath(), '/') . '/';

        $uri .= 'backend/AboCommerce/createInternalOrder?aboId=' . $aboId;

        $opts = array('http' => array(
            'header'=> 'Cookie: ' . $_SERVER['HTTP_COOKIE']."\r\nX-Requested-With: XMLHttpRequest\r\n"
        ));

        $context = stream_context_create($opts);

        session_write_close();
        $content = file_get_contents($uri, false, $context);

        if (false === $content) {
            $this->View()->assign(array(
                'success' => false,
                'message' => 'Request went wrong'
            ));
        }

        try {
            $result = Zend_Json::decode($content);
        } catch (\Zend_Json_Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => 'Could not decode response',
            ));

            return;
        }

        if (!isset($result['success']) || !$result['success']) {
            $this->View()->assign(array(
                'success' => false,
                'data'    => array('result' => $result)
            ));

            return;
        }

        $lastOrdernumber = $result['data']['orderNumber'];
        $lastOrderId = Shopware()->Db()->fetchOne(
            'SELECT id FROM  `s_order` WHERE  `ordernumber` LIKE ?',
            array($lastOrdernumber)
        );

        Shopware()->Db()->update(
            's_plugin_swag_abo_commerce_orders',
            array('last_order_id' => $lastOrderId),
            'id = ' . $aboId
        );

        $this->View()->assign(array(
             'success' => true,
             'data'    => array(
                 'result'      => $result,
                 'lastOrderId' => $lastOrderId,
             )
        ));
    }

    /**
     *
     */
    public function createInternalOrderAction()
    {
        $aboId = $this->Request()->getParam('aboId');

        $sql = 'SELECT * FROM s_plugin_swag_abo_commerce_orders WHERE id = ?';
        $abo = Shopware()->Db()->fetchRow($sql, array($aboId));

        if (!$abo) {
            throw new \Exception('AboNotFound');
        }

        $sql = '
            SELECT o.*, p.action as payment_action FROM s_order o
            LEFT JOIN s_core_paymentmeans p
            ON p.id = o.paymentID
            WHERE o.id = ?
        ';

        $order = Shopware()->Db()->fetchRow($sql, array($abo['last_order_id']));

        if (!$order) {
            throw new \Exception('OrderNotFound');
        }

        $this->registerShop($order);
        $this->registerOrder($abo, $order);

        $this->redirect(array(
            'module'        => 'frontend',
            'controller'    => $order['payment_action'],
            'action'        => 'recurring',
            'orderId'       => $abo['last_order_id'],
            'appendSession' => true,
            'forceSecure'   => true
        ));

//        $this->forward('recurring', $order['payment_action'], 'frontend', array(
//            'orderId' => $abo['last_order_id'],
//        ));
    }

    /**
     * @param $order
     */
    public function registerShop($order)
    {
        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repository->getActiveById($order['language']);
        foreach ($shop->getCurrencies() as $currency) {
            if ($order['currency'] == $currency->getCurrency()) {
                $shop->setCurrency($currency);
                break;
            }
        }

        $shop->registerResources(Shopware()->Bootstrap());
    }

    /**
     * @param $abo
     * @param $order
     * @return array
     */
    public function getBasketData($abo, $order)
    {
        $sql = '
            SELECT
              d.articleID, d.articleordernumber as ordernumber,
              d.name as articlename,
              d.price, d.quantity, d.modus, d.esdarticle,
              d.taxID, d.tax_rate
            FROM s_order_details d
            WHERE d.id IN (?, ?)
        ';

        $orderDetails = Shopware()->Db()->fetchAll($sql, array(
            $abo['article_order_detail_id'],
            $abo['discount_order_detail_id']
        ));

        $amount = 0;
        $amountNet = 0;
        foreach ($orderDetails as &$detail) {
            $detail['priceNumeric'] = (float)$detail['price'];
            $detail['amount'] = round($detail['price'] * $detail['quantity'], 2);
            $detailAmountNet = round($detail['amount'] * 100 / (100 + $detail['tax_rate']), 2);
            $amount += $detail['amount'];
            $amountNet += $detailAmountNet;
        }

        return array(
            'content' => $orderDetails,
            'sAmount' => empty($order['taxfree']) ? $amount : $amountNet,
            'AmountNumeric' => empty($order['taxfree']) ? $amount : $amountNet,
            'AmountWithTaxNumeric' => $amount,
            'AmountNetNumeric' => $amountNet,
            'sShippingcostsWithTax' => 0,
            'sShippingcostsNet' => 0,
            'sShippingcosts' => 0,
        );
    }

    /**
     * @param $abo
     * @param $order
     */
    public function registerOrder($abo, $order)
    {
        $dispatch = Shopware()->Modules()->Admin()->sGetPremiumDispatch(
            $order['dispatchID']
        );
        $vars = new ArrayObject(array(
            'sUserData' => $this->getUserData($order['id']),
            'sBasket'   => $this->getBasketData($abo, $order),
            'sDispatch' => $dispatch
        ), ArrayObject::ARRAY_AS_PROPS);

        $vars->sAmount = $vars->sBasket['sAmount'];
        $vars->sAmountWithTax = $vars->sBasket['AmountWithTaxNumeric'];
        $vars->sAmountNet = $vars->sBasket['AmountNetNumeric'];
        $vars->sShippingcosts = $vars->sBasket['sShippingcosts'];
        $vars->sShippingcostsNumeric = $vars->sBasket['sShippingcostsWithTax'];
        $vars->sShippingcostsNumericNet = $vars->sBasket['sShippingcostsNet'];

        Shopware()->Session()->sOrderVariables = $vars;
        Shopware()->Session()->sDispatch       = $dispatch['id'];
        Shopware()->Session()->sUserId         = $order['userID'];
    }

    /**
     * @param $orderId
     * @return array
     */
    public function getUserData($orderId)
    {
        $sql = 'SELECT * FROM s_order_billingaddress WHERE orderID = ?';
        $billingAddress = Shopware()->Db()->fetchRow($sql, array($orderId));

        $sql = 'SELECT * FROM s_order_shippingaddress WHERE orderID = ?';
        $shippingAddress = Shopware()->Db()->fetchRow($sql, array($orderId));

        $sql = 'SELECT * FROM s_order o WHERE o.id = ?';
        $order = Shopware()->Db()->fetchRow($sql, array($orderId));

        $sql = 'SELECT * FROM s_core_paymentmeans p WHERE p.id = ?';
        $payment = Shopware()->Db()->fetchRow($sql, array($order['paymentID']));

        $sql = 'SELECT * FROM s_user u WHERE u.id = ?';
        $user = Shopware()->Db()->fetchRow($sql, array($order['userID']));

        $sql = 'SELECT * FROM s_core_countries WHERE id = ?';
        $shippingCountry = Shopware()->Db()->fetchRow($sql, array($shippingAddress['countryID']));

        $sql = 'SELECT * FROM s_core_countries_states WHERE id = ?';
        $shippingState = Shopware()->Db()->fetchRow($sql, array($shippingAddress['stateID']));

        return array(
            'billingaddress' => $billingAddress,
            'shippingaddress' => $shippingAddress,
            'additional' => array(
                'user' => $user,
                'payment' => $payment,
                //'state',
                //'country',
                'countryShipping' => $shippingCountry,
                'stateShipping' => $shippingState,
                'charge_vat' => empty($order['taxfree']),
                'show_net' => !empty($order['net']),
            ),
        );
    }

//    /**
//     * @throws Exception
//     */
//    public function testAction()
//    {
//        $aboId = $this->Request()->getParam('aboId');
//
//        $sql = 'SELECT * FROM s_plugin_swag_abo_commerce_orders WHERE id = ?';
//        $abo = Shopware()->Db()->fetchRow($sql, array($aboId));
//
//        if (!$abo) {
//            throw new \Exception('AboNotFound');
//        }
//
//        $sql = '
//            SELECT o.*, p.action as payment_action FROM s_order o
//            LEFT JOIN s_core_paymentmeans p
//            ON p.id = o.paymentID
//            WHERE o.id = ?
//        ';
//
//        $order = Shopware()->Db()->fetchRow($sql, array($abo['last_order_id']));
//        if (!$order) {
//            throw new \Exception('OrderNotFound');
//        }
//
//        $userId      = $order['userID'];
//        $lastOrderId = $order['id'];
//
//
//        $shop = $this->getShopByOrder($order);
//        $this->registerShop($order);idch
//        $orderVars = $this->getOrderVars($abo, $order);
//
//        // to register namespace
//        Shopware()->AboCommerce();
//        $aboCommerceOrder = new Shopware_Components_AboCommerceOrder($shop, $orderVars);
//
////        $this->registerOrder($abo, $order);
//
//
//        /** @var $plugin Shopware_Plugins_Frontend_SwagPaymentIpayment_Bootstrap */
//        $plugin = Shopware()->Plugins()->Frontend()->SwagPaymentIpayment();
//
//        $config = $plugin->Config();
//
//        $sql = '
//            SELECT o.transactionID
//            FROM s_order o
//            WHERE o.userID = ?
//            AND o.id = ?
//            AND o.status >= 0
//            ORDER BY o.id DESC
//        ';
//
//        $transactionId = Shopware()->Db()->fetchOne($sql, array(
//            $userId,
//            $lastOrderId
//        ));
//
//        $accountData = $plugin->getAccountData();
//
//        $client = $this->getClient();
//        $method = $config->get('ipaymentPaymentPending') ? 'rePreAuthorize' : 'reAuthorize';
//
//        $result = $client->$method(
//            $accountData,
//            $transactionId,
//            $this->getTransactionData(
//                array(
//                     'recurringData' => array(
//                         'recurringTyp'                   => 'sequencial',
//                         'recurringAllowExpiryCorrection' => true,
//                         'recurringIgnoreMissingInitial'  => true
//                     )
//                )
//            )
//        );
//
//        if ($result->status != 'SUCCESS') {
//            echo Zend_Json::encode(
//                array(
//                     'success' => false,
//                     'message' => "[{$result->errorDetails->retErrorcode}] {$result->errorDetails->retErrorMsg}"
//                )
//            );
//            return;
//        }
//
//        $transactionId   = $result->successDetails->retTrxNumber;
//        $paymentUniqueId = $this->createPaymentUniqueId();
//        $paymentStatus   = $config->get('ipaymentPaymentPending') ? 'pre_auth' : 'auth';
//        $paymentStatusId = $plugin->getPaymentStatusId($paymentStatus);
//
//        $orderNumber = $this->saveOrder($transactionId, $paymentUniqueId, $userId, $paymentStatusId);
//        $comment = "{$result->paymentMethod} ({$result->trxPaymentDataCountry})";
//        $sql = 'UPDATE `s_order` SET `comment` = ? WHERE `ordernumber` = ?';
//        Shopware()->Db()->query($sql, array($comment, $orderNumber));
//
//        echo Zend_Json::encode(array(
//            'success' => true,
//            'data' => array(
//                'orderNumber'    => $orderNumber,
//                'transactionId'  => $transactionId,
//                'paymentComment' => $comment
//            )
//        ));
//
//        die('enede');
//    }

    /**
     * @param $abo
     * @param $order
     * @return ArrayObject
     */
    public function getOrderVars($abo, $order)
    {
        $dispatch = Shopware()->Modules()->Admin()->sGetPremiumDispatch(
            $order['dispatchID']
        );

        $vars = new ArrayObject(array(
            'sUserData' => $this->getUserData($order['id']),
            'sBasket'   => $this->getBasketData($abo, $order),
            'sDispatch' => $dispatch
        ), ArrayObject::ARRAY_AS_PROPS);

        $vars->sAmount                  = $vars->sBasket['sAmount'];
        $vars->sAmountWithTax           = $vars->sBasket['AmountWithTaxNumeric'];
        $vars->sAmountNet               = $vars->sBasket['AmountNetNumeric'];
        $vars->sShippingcosts           = $vars->sBasket['sShippingcosts'];
        $vars->sShippingcostsNumeric    = $vars->sBasket['sShippingcostsWithTax'];
        $vars->sShippingcostsNumericNet = $vars->sBasket['sShippingcostsNet'];

        return $vars;
    }

    /**
     * @param $order
     *
     * @return Shopware\Models\Shop\Shop
     */
    public function getShopByOrder($order)
    {
        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repository->getActiveById($order['language']);
        foreach ($shop->getCurrencies() as $currency) {
            if ($order['currency'] == $currency->getCurrency()) {
                $shop->setCurrency($currency);
                break;
            }
        }

        return $shop;
    }
}
