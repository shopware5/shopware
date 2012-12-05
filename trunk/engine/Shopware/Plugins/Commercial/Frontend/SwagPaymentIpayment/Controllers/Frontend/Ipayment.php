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
 * Paypal payment controller
 */
class Shopware_Controllers_Frontend_Ipayment extends Shopware_Controllers_Frontend_Payment
{
    /**
     *
     */
    public function preDispatch()
    {
        if (in_array($this->Request()->getActionName(), array('recurring'))) {
            $this->Front()->Plugins()->ViewRenderer()->setNoRender();
        }
    }

    /**
    * Index action method.
    *
    * Forwards to correct the action.
    */
    public function indexAction()
    {
        if ($this->getAmount() > 0 && $this->getPaymentShortName() == 'ipayment') {
            $this->forward('gateway');
        } else {
            $this->redirect(array('controller' => 'checkout'));
        }
    }

    /**
     * @return array
     */
    protected function getPaymentParams()
    {
        $router = $this->Front()->Router();
        $config = $this->Plugin()->Config();
        $test = $config->get('ipaymentSandbox');

        $params = array(
            'trxuser_id' => $test ? '99998' : $config->get('ipaymentAppId'),
            'trxpassword' => $test ? '0': $config->get('ipaymentAppPassword'),
            'silent' => 1,
            'trx_paymenttyp' => 'cc',
            'trx_typ' => $config->get('ipaymentPaymentPending') ? 'preauth' : 'auth',
            'trx_amount' => number_format($this->getAmount(), 2, '', ''),
            'trx_currency' => $this->getCurrencyShortName(),
            'silent_error_url' => $router->assemble(array('action' => 'return', 'forceSecure' => true)),
            'hidden_trigger_url' => $router->assemble(array('action' => 'notify', 'forceSecure' => true)),
            'redirect_url' => $router->assemble(array('action' => 'return', 'forceSecure' => true)),
            'client_name' => 'Shopware ' . Shopware::VERSION,
            'client_version' => $this->Plugin()->getVersion(),
            'from_ip' => $this->Request()->getClientIp(),
            'error_lang' => Shopware()->Shop()->getLocale()->getLanguage(),
            'browser_user_agent' => $this->Request()->getHeader('user_agent'),
            'browser_accept_headers' => $this->Request()->getHeader('accept')
        );

        $securityHash = array(
            $params['trxuser_id'], $params['trx_amount'], $params['trx_currency'],
            $params['trxpassword'], !$test ? $config->get('ipaymentSecurityKey') : 'testtest',
        );

        $params['trx_securityhash'] = md5(implode('', $securityHash));

        return $params;
    }

    /**
     * Returns the prepared customer parameter data.
     *
     * @return array
     */
    protected function getCustomerParameter()
    {
        $user = $this->getUser();
        if (empty($user)) {
            return array();
        }
        $billing = $user['billingaddress'];
        $customer = array(
            'shopper_id' => $billing['customernumber'],
            'addr_name' => $billing['firstname'] . ' ' . $billing['lastname'],
            'addr_street' => $billing['street'] . ' ' .$billing['streetnumber'],
            'addr_zip' => $billing['zipcode'],
            'addr_city' => $billing['city'],
            'addr_country' => $user['additional']['country']['countryiso'],
            'addr_email' => $user['additional']['user']['email'],
            'addr_telefon' => $billing['phone'],
        );
        if (!empty($user['additional']['stateBilling']['shortcode'])) {
            $customer['addr_state'] = $user['additional']['stateBilling']['shortcode'];
        }
        return $customer;
    }

    /**
     * @return array
     */
    protected function getPaymentSession()
    {
        $config = $this->Plugin()->Config();
        $client = $this->Client();
        $params = array('ipayment_session_id' => $client->createSession(
            $this->Plugin()->getAccountData(),
            $this->getTransactionData(array(
                //'recurringData' => array(
                //    'recurringTyp' => 'initial',
                //    'recurringExpiry' => date('Y/m/d', mktime(0, 0, 0, date('m'), date('d'), date('Y'))),
                //    'recurringFrequency' => 356,
                //    'recurringAllowExpiryCorrection' => true,
                //)
            )),
            $config->get('ipaymentPaymentPending') ? 'preauth' : 'auth',
            'cc', //paymentType
            $this->getOptions(),
            $this->getProcessorUrls()
        ), 'silent' => 1);
        return $params;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function getTransactionData($data = array())
    {
        $data = array_merge(array(
            'trxAmount' => number_format($this->getAmount(), 2, '', ''),
            'trxCurrency' => $this->getCurrencyShortName(),
        ), $data);
        $user = $this->getUser();
        if (!empty($user['billingaddress']['customernumber'])) {
            $data['shopperId'] = $user['billingaddress']['customernumber'];
        }
        return $data;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function getOptions($options = array())
    {
        return array_merge(array(
            'fromIp' => $this->Request()->getClientIp(),
            'errorLang' => Shopware()->Shop()->getLocale()->getLanguage(),
            'browserData' => array(
                'browserUserAgent' => $this->Request()->getHeader('user_agent'),
                'browserAcceptHeaders' => $this->Request()->getHeader('accept'),
            ),
            'clientData' => array(
                'clientName' => 'Shopware ' . Shopware::VERSION,
                'clientVersion' => $this->Plugin()->getVersion(),
            ),
        ), $options);
    }

    /**
     * @return array
     */
    protected function getProcessorUrls()
    {
        $router = $this->Front()->Router();
        return array(
            'silentErrorUrl' => $router->assemble(array('action' => 'return', 'forceSecure' => true)),
            'hiddenTriggerUrl' => $router->assemble(array('action' => 'notify', 'forceSecure' => true)),
            'redirectUrl' => $router->assemble(array('action' => 'return', 'forceSecure' => true)),
        );
    }

    /**
     *
     */
    public function recurringAction()
    {
        if (!$this->getAmount() || $this->getOrderNumber()) {
            $this->redirect(array(
                'controller' => 'checkout'
            ));
            return;
        }

        $config = $this->Plugin()->Config();
        $orderId = $this->Request()->getParam('orderId');

        $sql = '
            SELECT o.transactionID
            FROM s_order o
            WHERE o.userID = ?
            AND o.id = ?
            AND o.status >= 0
            ORDER BY o.id DESC
        ';
        $transactionId = Shopware()->Db()->fetchOne($sql, array(
            Shopware()->Session()->sUserId, $orderId
        ));

        $client = $this->Client();
        $method = $config->get('ipaymentPaymentPending') ? 'rePreAuthorize' : 'reAuthorize';
        $result = $client->$method(
            $this->Plugin()->getAccountData(),
            $transactionId,
            $this->getTransactionData(array(
                'recurringData' => array(
                    'recurringTyp' => 'sequencial',
                    'recurringAllowExpiryCorrection' => true,
                    'recurringIgnoreMissingInitial' => true
                )
            )),
            $this->getOptions()
        );

        if ($result->status != 'SUCCESS') {
            if (!$this->Request()->isXmlHttpRequest()) {
                Shopware()->Session()->IpaymentError = array(
                    'recurring' => true,
                    'errorCode' => $result->errorDetails->retErrorcode,
                    'errorMessage' => $result->errorDetails->retErrorMsg,
                );
                $this->redirect(array('action' => 'index', 'forceSecure' => true));
            } else {
                echo Zend_Json::encode(array(
                    'success' => false,
                    'message' => "[{$result->errorDetails->retErrorcode}] {$result->errorDetails->retErrorMsg}"
                ));
            }
        } else {
            $transactionId = $result->successDetails->retTrxNumber;
            $paymentUniqueId = $this->createPaymentUniqueId();
            $paymentStatus = $config->get('ipaymentPaymentPending') ? 'pre_auth' : 'auth';
            $paymentStatusId = $this->Plugin()->getPaymentStatusId($paymentStatus);

            $orderNumber = $this->saveOrder($transactionId, $paymentUniqueId, $paymentStatusId);
            $comment = "{$result->paymentMethod} ({$result->trxPaymentDataCountry})";
            $sql = 'UPDATE `s_order` SET `comment` = ? WHERE `ordernumber` = ?';
            Shopware()->Db()->query($sql, array($comment, $orderNumber));

            if (!$this->Request()->isXmlHttpRequest()) {
                $this->redirect(array(
                    'controller' => 'checkout',
                    'action'     => 'finish',
                    'sUniqueID'  => $paymentUniqueId
                ));
            } else {
                echo Zend_Json::encode(array(
                    'success' => true,
                    'data' => array(
                        'orderNumber'    => $orderNumber,
                        'transactionId'  => $transactionId,
                        'paymentComment' => $comment
                    )
                ));
            }
        }
    }

    /**
     * @return array
     */
    public function getRecurringPayments()
    {
        $sql = '
            SELECT o.id, MAX(o.id) as orderId,
              a.swag_ipayment_description as description
            FROM s_order o, s_order_attributes a
            WHERE o.userID = ?
            AND o.status >= 0
            AND a.orderID = o.id
            AND a.swag_ipayment_description IS NOT NULL
            AND o.ordertime >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
            GROUP BY description
            ORDER BY o.id DESC
        ';
        return Shopware()->Db()->fetchAll($sql, array(
            Shopware()->Session()->sUserId
        ));
    }

    /**
     * Gateway action method.
     *
     * Collects the payment information and transmit it to the payment provider.
     */
    public function gatewayAction()
    {
        if (!$this->getAmount() || $this->getOrderNumber()) {
            $this->redirect(array(
                'controller' => 'checkout'
            ));
            return;
        }

        $config = $this->Plugin()->Config();
        $test = $config->get('ipaymentSandbox');
        $uniqueId = $this->createPaymentUniqueId();

        $url = 'https://ipayment.de/merchant/';
        $url .= $test ? '99999' : $config->get('ipaymentAccountId');
        $url .= 0 && $test ? '/example' : '/processor';
        $url .= '/2.0/';

        if ($config->get('ipaymentSecurityKey')) {
            $params = $this->getPaymentParams();
        } else {
            $params = $this->getPaymentSession();
        }

        $params['return_paymentdata_details'] = 1;
        $params['sw_unique_id'] = $uniqueId;
        $params = array_merge($params, $this->getCustomerParameter());

        if ($config->get('ipaymentRecurring')) {
            $this->View()->recurringPayments = $this->getRecurringPayments();
        }

        $this->View()->assign(array(
            'gatewayUrl' => $url,
            'gatewayParams' => $params,
            'gatewayAmount' => $this->getAmount(),
            'gatewaySecureImage' => $config->get('ipaymentSecureImage')
        ));
        if (!empty(Shopware()->Session()->IpaymentError)) {
            if (!empty(Shopware()->Session()->IpaymentError['recurring'])
              && !empty($this->View()->recurringPayments)) {
                $this->View()->assign('recurringError', Shopware()->Session()->IpaymentError);
            } else {
                $this->View()->assign('gatewayError', Shopware()->Session()->IpaymentError);
            }
            unset(Shopware()->Session()->IpaymentError);
        }
	}

    /**
     * Return action method
     *
     * Reads the transactionResult and represents it for the customer.
     */
    public function returnAction()
    {
        $request = $this->Request();
		$config = $this->Plugin()->Config();
        $test = $config->get('ipaymentSandbox');

        $status = $this->Request()->getParam('ret_status');
        if ($status == 'ERROR') {
            Shopware()->Session()->IpaymentError = array(
                'errorCode' => $request->getParam('ret_errorcode'),
                'errorMessage' => $request->getParam('ret_errormsg')
            );
            $this->redirect(array('action' => 'index', 'forceSecure' => true));
            return;
        }

        $secret = $test ? 'testtest' : $config->get('ipaymentSecurityKey');
        $url = $request->getScheme() . '://' . $request->getHttpHost() . $request->getRequestUri();
        $url = substr($url, 0, strpos($url, '&ret_url_checksum') + 1);
        $result = $request->getQuery();

        $transactionId = $result['ret_trx_number'];
        $paymentUniqueId = $result['sw_unique_id'];
        $paymentStatus = $result['trx_typ'];
        if ($this->getAmount() > ($result['trx_amount'] / 100)) {
            $paymentStatus = 'miss'; //Überprüfung notwendig
        }
        if ($request->get('ret_url_checksum') != md5($url . $secret)) {
            $paymentStatus = 'checksum'; //Überprüfung notwendig
            //$this->redirect(array('action' => 'index', 'forceSecure' => true));
            //return;
        }
        $paymentStatusId = $this->Plugin()->getPaymentStatusId($paymentStatus);
        $orderNumber = $this->saveOrder($transactionId, $paymentUniqueId, $paymentStatusId);

        $comment = "{$result['trx_paymentmethod']} ({$result['trx_paymentdata_country']})";
        if (!empty($result['paydata_cc_number'])) {
            $comment .= " - {$result['paydata_cc_number']}";
        }
        if (!empty($result['paydata_cc_cardowner'])) {
            if (mb_detect_encoding($result['paydata_cc_cardowner'], 'UTF-8', true) === false) {
                $result['paydata_cc_cardowner'] = utf8_encode($result['paydata_cc_cardowner']);
            }
            $comment .= " - {$result['paydata_cc_cardowner']}";
        }
        $sql = 'UPDATE `s_order` SET `comment` = ? WHERE `ordernumber` = ?';
        Shopware()->Db()->query($sql, array($comment . "\r\n", $orderNumber));

        try {
            $sql = '
                INSERT INTO s_order_attributes (orderID, swag_ipayment_description)
                SELECT id, ? FROM s_order WHERE ordernumber = ?
                ON DUPLICATE KEY
                UPDATE swag_ipayment_description = VALUES(swag_ipayment_description)
            ';
            Shopware()->Db()->query($sql, array(
                $comment,
                $orderNumber
            ));
        } catch(Exception $e){ }

        $this->redirect(array(
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => $paymentUniqueId
        ));
    }

    /**
     * Notify action method
     */
    public function notifyAction()
    {
        $request = $this->Request();

        if (!preg_match('/\.ipayment\.de$/', gethostbyaddr($request->getClientIp(false)))) {
            return;
        }
        if ($request->getParam('ret_status') != 'SUCCESS') {
            return;
        }

        $transactionId = $request->getParam('ret_trx_number');
        $paymentStatus = $request->getParam('trx_typ');
        $paymentUniqueId = $request->getParam('sw_unique_id');
        $paymentStatusId = $this->Plugin()->getPaymentStatusId($paymentStatus);
        if ($paymentStatusId == 12 || $paymentStatusId == 18) {
            $this->saveOrder($transactionId, $paymentUniqueId);
        }
        $this->Plugin()->setPaymentStatus($transactionId, $paymentStatus);
    }

    /**
     * Returns the payment plugin config data.
     *
     * @return Shopware_Plugins_Frontend_SwagPaymentIpayment_Bootstrap
     */
    public function Plugin()
    {
        return Shopware()->Plugins()->Frontend()->SwagPaymentIpayment();
    }

    /**
     * @return SoapClient
     */
    public function Client()
    {
        $apiUrl = 'https://ipayment.de/service/3.0/?wsdl';
        $client = new SoapClient($apiUrl, array('trace' => 1));
        return $client;
    }
}
