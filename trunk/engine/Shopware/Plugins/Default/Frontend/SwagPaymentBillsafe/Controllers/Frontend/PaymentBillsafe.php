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
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Billsafe payment controller
 */
class Shopware_Controllers_Frontend_PaymentBillsafe extends Shopware_Controllers_Frontend_Payment
{
    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);
    }

    /**
     * Index action method.
     * 
     * Forwards to correct the action.
     */
    public function indexAction()
    {
        if($this->getPaymentShortName() == 'billsafe_invoice') {
            $this->redirect(array('action' => 'gateway', 'forceSecure' => true));
        } else {
            $this->redirect(array('controller' => 'checkout'));
        }
    }

    /**
     * Returns the article list parameter data.
     * 
     * @return array
     */
    protected function getArticleListParameter()
    {
        $user = $this->getUser();
        $basket = $this->getBasket();
        
        $articleList = array();
        foreach ($basket['content'] as $item) {
            if (!empty($user['additional']['charge_vat']) && !empty($item['amountWithTax'])){
                $price = round($item['amountWithTax'] / $item['quantity'], 2);
            } else {
                $price = str_replace(',', '.', $item['price']);
            }
            $article = array(
                'number' => $item['ordernumber'],
                'name' => strlen($item['articlename']) > 100 ? substr($item['articlename'], 0, 90) . '...' : $item['articlename'] ,
                'description' => '',
                'quantity' => $item['quantity'],
                'grossPrice' => $price,
                'quantityShipped' => 0,
            );
            if(empty($article['grossPrice']) || empty($user['additional']['charge_vat'])) {
                $article['tax'] = 0;
            } elseif(!empty($item['taxPercent'])) {
                $article['tax'] = $item['taxPercent'];
            } else {
                if (!empty($user['additional']['charge_vat']) && !empty($item['amountWithTax'])){
                    $amount = $item['amountWithTax'];
                } else {
                    $amount = str_replace(',', '.', $item['amount']);
                }
                $article['tax'] = round(
                    $amount / str_replace(',', '.', $item['amountnet'])
                , 2) * 100 - 100;
            }
            // Detect kind of article
            if($item['modus'] == 4) {
                $article['type'] = 'handling';
            } else {
                $article['type'] = $price >= 0 ? 'goods' : 'voucher';
            }
            $testKey = $article['type'] === 'goods' ?  null : $article['type'];
            if($testKey === null) {
                $articleList[] = $article;
            } elseif(!isset($articleList[$testKey])) {
                $articleList[$testKey] = $article;
            } else {
                $articleList[$testKey]['grossPrice'] += $article['grossPrice'];
            }
        }

        if(!empty($basket['sShippingcosts'])) {
            $article = array(
                'number' => 'shipment',
                'name' => 'Versandkosten',
                'description' => '',
                'quantity' => 1,
                'grossPrice' => $this->getShipment(),
                'tax' => $this->getTaxShipment(),
                'type' => 'shipment',
            );
            $articleList[] = $article;
        }

        return array_values($articleList);
    }

    /**
     * Returns the prepared customer parameter data.
     * 
     * @return array
     */
    protected function getCustomerParameter()
    {
        $user = $this->getUser();
        $customer = array(
            'id'          => $user['billingaddress']['customernumber'],
            'company'     => $user['billingaddress']['company'],
            'gender'      => $user['billingaddress']['salutation'] == 'ms' ? 'f' : 'm',
            'firstname'   => $user['billingaddress']['firstname'],
            'lastname'    => $user['billingaddress']['lastname'],
            'street'      => $user['billingaddress']['street'],
            'houseNumber' => $user['billingaddress']['streetnumber'],
            'postcode'    => $user['billingaddress']['zipcode'],
            'city'        => $user['billingaddress']['city'],
            'country'     => $user['additional']['country']['countryiso'],
            'email'       => $user['additional']['user']['email'],
            'phone'       => $user['billingaddress']['phone'],
        );
        if(!empty($user['billingaddress']['birthday']) && $user['billingaddress']['birthday'] != '0000-00-00') {
            $customer['dateOfBirth'] = $user['billingaddress']['birthday'];
        }
        if(!empty($user['billingaddress']['company'])) {
            $customer['company'] = $user['billingaddress']['company'];
        }
        return $customer;
    }
        
    /**
     * Gateway action method.
     * 
     * Collects the payment information and transmit it to the payment provider.
     */
    public function gatewayAction()
    {
        $router      = $this->Front()->Router();
        $config      = $this->Config();
        $articleList = $this->getArticleListParameter();
        $customer    = $this->getCustomerParameter();

        $parameter = array(
            'order' => array(
                'amount'       => $this->getAmount(),
                'taxAmount'    => $this->getTaxAmount(),
                'currencyCode' => $this->getCurrencyShortName()
            ),
            'customer'    => $customer,
            'articleList' => $articleList,
            'product'     => 'invoice',
            'url' => array(
                'return' => $router->assemble(array('action' => 'return', 'forceSecure' => true)),
                'cancel' => $router->assemble(array('action' => 'cancel', 'forceSecure' => true)),
                'image'  => $this->View()->fetch('string:{link file=' . var_export($config->logo, true) . ' fullPath}')
            ),
            'sessionId' => Shopware()->SessionID(),
            'custom'    => array(
                $this->getAmount(),
                $this->createPaymentUniqueId()
            )
        );
        $response = Shopware()->BillsafeClient()->prepareOrder($parameter);
        if($config->debug) {
            Shopware()->Log()->info($parameter);
            Shopware()->Log()->info($response);
        }
        $this->View()->BillsafeResponse = $response;
        $this->View()->BillsafeConfig   = $config;
        
        if(empty($response->token)) {
            $this->forward('cancel');
        }
    }

    /**
     * Return action method
     * 
     * Reads the transactionResult and represents it for the customer.
     */
    public function returnAction()
    {
        $token = $this->Request()->getParam('token');
        $client = Shopware()->BillsafeClient();
        $config = $this->Config();
        $transactionResult = $client->getTransactionResult(array('token' => $token));

        if ($transactionResult->ack === 'ERROR'
          && isset(Shopware()->Session()->BillsafeResult)) {
            $transactionResult = Shopware()->Session()->BillsafeResult;
        } else {
            Shopware()->Session()->BillsafeResult = $transactionResult;
        }

        if ($transactionResult->status == 'ACCEPTED') {
            $amount = $transactionResult->custom[0];
            $secret = $transactionResult->custom[1];

            if ($amount == $this->getAmount()) {
                $paymentStatusId = !empty($config->paymentStatusId) ? $config->paymentStatusId : 17;
            } else {
                $paymentStatusId = 21;
            }

            $orderNumber = $this->saveOrder($transactionResult->transactionId, $secret, $paymentStatusId);

            $client->setOrderNumber(array(
                'transactionId' => $transactionResult->transactionId,
                'orderNumber' => $orderNumber
            ));

            $this->redirect(array('controller' => 'checkout', 'action' => 'finish', 'sUniqueID' => $secret));
        } else {
            $this->View()->BillsafeResponse = $transactionResult;

            $this->forward('cancel');
        }
    }

    /**
     * Cancel action method
     * 
     * Reads the payment config
     */
    public function cancelAction()
    {
        $this->View()->BillsafeConfig = $this->Config();
    }

    /**
     * Returns the payment plugin config data.
     *
     * @return Shopware_Models_Plugin_Config
     */
    public function Config()
    {
        return Shopware()->Plugins()->Frontend()->SwagPaymentBillsafe()->Config();
    }

    /**
     * Sets order comment by order number
     * 
     * @param $orderNumber
     * @param $comment
     * @return void
     */
    public function setOrderComment($orderNumber, $comment)
    {
        $sql = '
            UPDATE s_order SET comment=? WHERE ordernumber=?
        ';
        Shopware()->Db()->query($sql, array(
            $comment,
            $orderNumber
        ));
    }
    
    /**
     * Returns basket tax amount as float
     *
     * @return float
     */
    public function getShipment()
    {
        $user = $this->getUser();
        $basket = $this->getBasket();
        if (!empty($user['additional']['charge_vat'])){
            return $basket['sShippingcostsWithTax'];
        } else {
            return str_replace(',', '.', $basket['sShippingcosts']);
        }
    }
    
    /**
     * Returns basket tax amount as float
     *
     * @return float
     */
    public function getTaxShipment()
    {
        $user = $this->getUser();
        $basket = $this->getBasket();
        if (!empty($user['additional']['charge_vat'])){
            return round(
                $basket['sShippingcostsWithTax'] / $basket['sShippingcostsNet']
            , 2) * 100 - 100;
        } else {
            return 0;
        }
    }
    
    /**
     * Returns basket tax amount as float
     *
     * @return float
     */
    public function getTaxAmount()
    {
        $user = $this->getUser();
        $basket = $this->getBasket();
        if (!empty($user['additional']['charge_vat'])){
            return $basket['sAmountTax'];
        } else {
            return 0;
        }
    }
    
    /**
     * Returns the full user data as array
     *
     * @return array
     */
    public function getUser()
    {
        if(!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            return Shopware()->Session()->sOrderVariables['sUserData'];
        } else {
            return null;
        }
    }

    /**
     * Returns the full basket data as array
     *
     * @return array
     */
    public function getBasket()
    {
        if(!empty(Shopware()->Session()->sOrderVariables['sBasket'])) {
            return Shopware()->Session()->sOrderVariables['sBasket'];
        } else {
            return null;
        }
    }
}
