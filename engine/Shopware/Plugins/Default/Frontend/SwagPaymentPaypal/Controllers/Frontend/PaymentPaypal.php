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
class Shopware_Controllers_Frontend_PaymentPaypal extends Shopware_Controllers_Frontend_Payment
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
         // PayPal Express > Sale
        if(!empty(Shopware()->Session()->PaypalResponse['TOKEN'])) {
            $this->forward('return');
        // Paypal Basis || PayPal Express
        } elseif($this->getPaymentShortName() == 'paypal') {
			$this->forward('gateway');
		} else {
			$this->redirect(array('controller' => 'checkout'));
		}
	}

    /**
     * Express payment action method.
     */
    public function expressAction()
    {
        unset(Shopware()->Session()->sOrderVariables);
        $this->forward('gateway');
    }

	/**
	 * Gateway payment action method.
	 * 
	 * Collects the payment information and transmit it to the payment provider.
	 */
	public function gatewayAction()
	{
		$router = $this->Front()->Router();
		$config = $this->Plugin()->Config();
        $client = $this->Plugin()->Client();

        $logoImage = $config->get('paypalLogoImage');
        $logoImage = 'string:{link file=' . var_export($logoImage, true) . ' fullPath}';
        $logoImage = $this->View()->fetch($logoImage);

        $shopName = Shopware()->Config()->get('shopName');
        $shopName = $config->get('paypalBrandName', $shopName);

        $borderColor = ltrim($config->get('paypalCartBorderColor'), '#');

        if($this->getUser() === null) {
            $paymentAction = 'Authorization';
        } elseif($config->get('paypalPaymentActionPending', true)) {
            $paymentAction = 'Order';
        } else {
            $paymentAction = 'Sale';
        }

        $params = array(
            'PAYMENTACTION' => $paymentAction,
            'RETURNURL' => $router->assemble(array('action' => 'return', 'forceSecure' => true)),
            'CANCELURL' => $router->assemble(array('action' => 'cancel', 'forceSecure' => true)),
            'NOTIFYURL' => $router->assemble(array('action' => 'notify', 'forceSecure' => true, 'appendSession' => true)),
            'GIROPAYSUCCESSURL' => $router->assemble(array('action' => 'return', 'forceSecure' => true)),
            'GIROPAYCANCELURL' => $router->assemble(array('action' => 'cancel', 'forceSecure' => true)),
            'BANKTXNPENDINGURL' => $router->assemble(array('action' => 'return', 'forceSecure' => true)),
//            'NOSHIPPING' => 0, //todo@hl
//            'REQCONFIRMSHIPPING' => 0,
            'ALLOWNOTE' => 1, //todo@hl
            'ADDROVERRIDE' => $paymentAction == 'Authorization' ? 0 : 1,
            'BRANDNAME' => $shopName,
            'LOGOIMG' => $logoImage,
            'CARTBORDERCOLOR' => $borderColor,
            'CUSTOM' => $this->createPaymentUniqueId(),
//            'SOLUTIONTYPE' => $config->get('paypalAllowGuestCheckout') ? 'Sole' : 'Mark',
            'TOTALTYPE' => $this->getUser() !== null ? 'Total' : 'EstimatedTotal',
        );
        if($config->get('paypalBillingAgreement') && $this->getUser() !== null) {
            $params['BILLINGTYPE'] = 'MerchantInitiatedBilling';
        }

        $params = array_merge($params, $this->getBasketParameter());
        $params = array_merge($params, $this->getCustomerParameter());

        $response = $client->setExpressCheckout($params);

        Shopware()->Session()->PaypalResponse = $response;

        if($response['ACK'] == 'SuccessWithWarning') {
            $response['ACK'] = 'Success';
        }
		if(!empty($response['ACK']) && $response['ACK'] == 'Success') {
            if(!empty($config->paypalSandbox)) {
                $gatewayUrl = 'https://www.sandbox.paypal.com/';
            } else {
                $gatewayUrl = 'https://www.paypal.com/';
            }
            $gatewayUrl .= 'webscr&cmd=_express-checkout';
            if($this->getUser() !== null) {
                $gatewayUrl .= '&useraction=commit';
            }
            $gatewayUrl .= '&token=' . urlencode($response['TOKEN']);
            $this->View()->PaypalGatewayUrl = $gatewayUrl;
        } else {
            $this->forward('return');
        }
	}

    /**
     * Recurring payment action method.
     */
    public function recurringAction()
    {
        if(!$this->getAmount() || $this->getOrderNumber()) {
            $this->redirect(array(
                'controller' => 'checkout'
            ));
            return;
        }
        $orderId = $this->Request()->getParam('orderId');
        $sql = '
            SELECT swag_payal_billing_agreement_id
            FROM s_order_attributes a, s_order o
            WHERE o.id = a.orderID
            AND o.id = ?
            AND o.userID = ?
            AND a.swag_payal_billing_agreement_id IS NOT NULL
            ORDER BY o.id DESC
        ';
        $agreementId = Shopware()->Db()->fetchOne($sql, array(
            $orderId,
            Shopware()->Session()->sUserId
        ));
        $details = array(
            'REFERENCEID' => $agreementId
        );
        $response = $this->finishCheckout($details);

        if($this->Request()->isXmlHttpRequest()) {
            if($response['ACK'] != 'Success') {
                $data = array(
                    'success' => false,
                    'message' => "[{$response['L_ERRORCODE0']}] - {$response['L_SHORTMESSAGE0']}"
                );
            } else {
                $data = array(
                    'success' => false,
                    'data' => array(array(
                        'orderNumber' => $response['INVNUM'],
                        'transactionId' => $response['TRANSACTIONID'],
                    ))
                );
            }
            echo Zend_Json::encode($data);
        } else {
            if($response['ACK'] != 'Success') {
                $this->View()->loadTemplate('frontend/payment_paypal/return.tpl');
                $this->View()->PaypalConfig = $this->Plugin()->Config();
                $this->View()->PaypalResponse = $response;
            } else {
                $this->redirect(array(
                    'controller' => 'checkout',
                    'action' => 'finish',
                    'sUniqueID' => $response['CUSTOM']
                ));
            }
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
		$config = $this->Plugin()->Config();
        $client = $this->Plugin()->Client();
        $response = Shopware()->Session()->PaypalResponse;

        if($token !== null) {
            $details = $client->getExpressCheckoutDetails(array('token' => $token));
        } elseif(!empty($response['TOKEN'])) {
            $details = $client->getExpressCheckoutDetails(array('token' => $response['TOKEN']));
        } else {
            $details = array();
        }

        switch(!empty($details['CHECKOUTSTATUS']) ? $details['CHECKOUTSTATUS'] : null) {
            case 'PaymentActionCompleted':
            case 'PaymentCompleted':
                $this->redirect(array(
                    'controller' => 'checkout',
                    'action' => 'finish',
                    'sUniqueID' => $details['CUSTOM']
                ));
                break;
            case 'PaymentActionNotInitiated':
                // If user exits and order not finished
                if($this->getUser() !== null && $this->getOrderNumber() === null) {
                    unset(Shopware()->Session()->PaypalResponse);
                    $response = $this->finishCheckout($details);
                    if($response['ACK'] != 'Success') {
                        $this->View()->PaypalConfig = $config;
                        $this->View()->PaypalResponse = $response;
                    } elseif ($response['REDIRECTREQUIRED'] === 'true') {
                        if(!empty($config->paypalSandbox)) {
                            $redirectUrl = 'https://www.sandbox.paypal.com/';
                        } else {
                            $redirectUrl = 'https://www.paypal.com/';
                        }
                        $redirectUrl .= 'webscr?cmd=_complete-express-checkout';
                        $redirectUrl .= '&token=' . urlencode($response['TOKEN']);
                        $this->redirect($redirectUrl);
                    } else {
                        $this->redirect(array(
                            'controller' => 'checkout',
                            'action' => 'finish',
                            'sUniqueID' => $response['CUSTOM']
                        ));
                    }
                } else {
                    if(!empty($details['PAYERID']) && !empty($details['SHIPTONAME'])) {
                        $this->createAccount($details);
                    }
                    $this->redirect(array(
                        'controller' => 'checkout'
                    ));
                }
                break;
            case 'PaymentActionInProgress':
            case 'PaymentActionFailed':
            default:
                $this->View()->PaypalConfig = $config;
                $this->View()->PaypalResponse = $response;
                $this->View()->PaypalDetails = $details;
                unset(Shopware()->Session()->PaypalResponse);
                break;
        }
	}

    /**
     * Return action method
     */
    public function cancelAction()
    {
        unset(Shopware()->Session()->PaypalResponse);
        $config = $this->Plugin()->Config();
        $this->View()->PaypalConfig = $config;
    }

    /**
     * Notify action method
     */
    public function notifyAction()
    {
        $this->View()->setTemplate();
        $client = $this->Plugin()->Client();

        $details = $client->getTransactionDetails(array(
            'TRANSACTIONID' => $this->Request()->get('txn_id')
        ));

        if(empty($details['PAYMENTSTATUS']) || empty($details['ACK']) || $details['ACK'] != 'Success') {
            return;
        }

        $paymentStatusId = $this->Plugin()->getPaymentStatusId($details['PAYMENTSTATUS']);
        if ($paymentStatusId == 12 || $paymentStatusId == 18) {
            $this->saveOrder($details['TRANSACTIONID'], $details['CUSTOM']);
        }
        $this->Plugin()->setPaymentStatus($details['TRANSACTIONID'], $details['PAYMENTSTATUS']);
    }

    /**
     * @param $details
     * @return array
     */
    protected function finishCheckout($details)
    {
        $client = $this->Plugin()->Client();
        $config = $this->Plugin()->Config();

        if(!empty($details['REFERENCEID'])) {
            $router = $this->Front()->Router();
            $notifyUrl = $router->assemble(array(
                'action' => 'notify', 'forceSecure' => true, 'appendSession' => true
            ));
            $params = array(
                'REFERENCEID' => $details['REFERENCEID'],
                'IPADDRESS' => $this->Request()->getClientIp(false),
                'NOTIFYURL' => $notifyUrl,
                'CUSTOM' => $this->createPaymentUniqueId(),
                'BUTTONSOURCE' => 'Shopware_Cart_ECM'
            );
        } else {
            $params = array(
                'TOKEN' => $details['TOKEN'],
                'PAYERID' => $details['PAYERID'],
                'CUSTOM' => $details['CUSTOM'],
                'BUTTONSOURCE' => 'Shopware_Cart_ECS'
            );
        }

        if($config->get('paypalPaymentActionPending', true)) {
            $params['PAYMENTACTION'] = empty($params['TOKEN']) ? 'Authorization' : 'Order';
        } else {
            $params['PAYMENTACTION'] = 'Sale';
        }

        $params = array_merge($params, $this->getBasketParameter());
        $params = array_merge($params, $this->getCustomerParameter());

        $orderNumber = $this->saveOrder(
            isset($params['TOKEN']) ? $params['TOKEN'] : $params['REFERENCEID'],
            $params['CUSTOM']
        );
        $params['INVNUM'] = $orderNumber;
        //$params['SOFTDESCRIPTOR'] = $orderNumber;

        if(!empty($params['REFERENCEID'])) {
            $result = $client->doReferenceTransaction($params);
        } else {
            $result = $client->doExpressCheckoutPayment($params);
        }

        $result['INVNUM'] = $params['INVNUM'];
        $result['CUSTOM'] = $params['CUSTOM'];

        if(!empty($result['BILLINGAGREEMENTID'])) {
            try {
                $sql = '
                    INSERT INTO s_order_attributes (orderID, swag_payal_billing_agreement_id)
                    SELECT id, ? FROM s_order WHERE ordernumber = ?
                    ON DUPLICATE KEY
                    UPDATE swag_payal_billing_agreement_id = VALUES(swag_payal_billing_agreement_id)
                ';
                Shopware()->Db()->query($sql, array(
                    $result['BILLINGAGREEMENTID'],
                    $orderNumber
                ));
            } catch(Exception $e) { }
        }

        $sql = '
            UPDATE `s_order`
            SET transactionID = ?, comment = ?,
              customercomment = CONCAT(customercomment, ?)
            WHERE temporaryID = ? AND transactionID = ?
        ';
        Shopware()->Db()->query($sql, array(
            $result['TRANSACTIONID'],
            isset($details['EMAIL']) ? "{$details['EMAIL']} ({$details['PAYERSTATUS']})\r\n" : null,
            isset($details['NOTE']) ? $details['NOTE'] : null,
            $params['CUSTOM'],
            isset($params['TOKEN']) ? $params['TOKEN'] : $params['REFERENCEID']
        ));

        if($result['ACK'] == 'Success') {
            $paymentStatus = $result['PAYMENTSTATUS'];
            if($this->getAmount() > (float)$result['AMT']) {
                $paymentStatus = 'AmountMissMatch'; //Überprüfung notwendig
            }
            $this->Plugin()->setPaymentStatus($result['TRANSACTIONID'], $paymentStatus);
        }

        return $result;
    }

    /**
     * @param $details
     */
    protected function createAccount($details)
    {
        $module = Shopware()->Modules()->Admin();
        $session = Shopware()->Session();

        $data['auth']['email'] = $details['EMAIL'];
        $data['auth']['password'] = $details['PAYERID'];
        $data['auth']['accountmode'] = '1';

        $data['billing']['salutation'] = 'mr';
        $data['billing']['firstname'] = $details['FIRSTNAME'];
        $data['billing']['lastname'] = $details['LASTNAME'];
        $street = explode(' ', $details['SHIPTOSTREET']);
        $data['billing']['street'] = $street[0];
        $data['billing']['streetnumber'] = implode(' ', array_slice($street, 1));
        if(strlen($data['billing']['streetnumber']) > 4) {
            $data['billing']['street'] .= ' ' . $data['billing']['streetnumber'];
            $data['billing']['streetnumber'] = '';
        }
        if(empty($data['billing']['streetnumber'])) {
            $data['billing']['streetnumber'] = ' ';
        }
        $data['billing']['zipcode'] = $details['SHIPTOZIP'];
        $data['billing']['city'] = $details['SHIPTOCITY'];
        $sql = 'SELECT id FROM s_core_countries WHERE countryiso=?';
        $countryId = Shopware()->Db()->fetchOne($sql, array($details['SHIPTOCOUNTRYCODE']));
        $data['billing']['country'] = $countryId;
        if (!empty($details['SHIPTOSTATE']) && $details['SHIPTOSTATE'] != 'Empty') {
            $sql = 'SELECT id FROM s_core_countries_states WHERE countryID=? AND shortcode=?';
            $stateId = Shopware()->Db()->fetchOne($sql, array($countryId, $details['SHIPTOSTATE']));
            $data['billing']['stateID'] = $stateId;
        }
        if (!empty($details['BUSINESS'])) {
            $data['billing']['company'] = $details['BUSINESS'];
        } else {
            $data['billing']['company'] = '';
        }
        $data['billing']['department'] = '';

        $data['shipping'] = $data['billing'];
        $name = explode(' ', $details['SHIPTONAME']);
        $data['shipping']['firstname'] = $name[0];
        $data['shipping']['lastname'] = implode(' ', array_slice($name, 1));
        if (!empty($details['SHIPTOPHONENUM'])) {
            $data['billing']['phone'] = $details['SHIPTOPHONENUM'];
        }

        $sql = 'SELECT id FROM s_core_paymentmeans WHERE name=?';
        $paymentId = Shopware()->Db()->fetchOne($sql, array('paypal'));
        $data['payment']['object'] = $module->sGetPaymentMeanById($paymentId);

        // First try login / Reuse paypal account
        $module->sSYSTEM->_POST = $data['auth'];
        $module->sLogin(true);

        // Check login status
        if ($module->sCheckUser()) {
            $module->sSYSTEM->_POST = $data['shipping'];
            $module->sUpdateShipping();
            $module->sSYSTEM->_POST = array('sPayment' => $paymentId);
            $module->sUpdatePayment();
        } else {
            $data['auth']['password'] = md5($data['auth']['password']);
            $session->sRegisterFinished = false;
            $session->sRegister = new ArrayObject($data, ArrayObject::ARRAY_AS_PROPS);
            $module->sSaveRegister();
        }
    }

    /**
     * Returns the article list parameter data.
     *
     * @return array
     */
    protected function getBasketParameter()
    {
        $params = array();
        $user = $this->getUser();

        $params['CURRENCYCODE'] = $this->getCurrencyShortName();

        if($user !== null) {
            $basket = $this->getBasket();
            if(!empty($basket['sShippingcosts'])) {
                $params['SHIPPINGAMT'] = $this->getShipment();
            }
            $params['AMT'] = $this->getAmount();
        } else {
            $basket = Shopware()->Modules()->Basket()->sGetBasket();
            if(!empty($basket['sShippingcosts'])) {
                $params['SHIPPINGAMT'] = !empty($basket['sShippingcostsWithTax']) ? $basket['sShippingcostsWithTax']: $basket['sShippingcosts'];
                $params['SHIPPINGAMT'] = str_replace(',', '.',  $params['SHIPPINGAMT']);
            }
            if (!empty($user['additional']['charge_vat']) && !empty($item['AmountWithTaxNumeric'])) {
                $params['AMT'] = $basket['AmountWithTaxNumeric'];
            } else {
                $params['AMT'] = $basket['AmountNumeric'];
            }
            $params['AMT'] = $basket['AmountNumeric'];
        }
        $params['AMT'] = number_format($params['AMT'], 2, '.', '');
        $params['SHIPPINGAMT'] = number_format($params['SHIPPINGAMT'], 2, '.', '');
        $params['ITEMAMT'] = number_format($params['AMT'] - $params['SHIPPINGAMT'], 2, '.', '');
        $params['TAXAMT'] = number_format(0, 2, '.', '');

        $config = $this->Plugin()->Config();
        if($config->get('paypalTransferCart')) {
            foreach ($basket['content'] as $key => $item) {
                if (!empty($user['additional']['charge_vat']) && !empty($item['amountWithTax'])) {
                    $amount = $item['amountWithTax'];
                } else {
                    $amount = str_replace(',', '.', $item['amount']);
                }
//                if(empty($amount) || empty($user['additional']['charge_vat'])) {
//                    $tax = 0;
//                } elseif(!empty($item['tax'])) {
//                    $tax = str_replace(',', '.', $item['tax']);
//                } else {
//                    $tax = $amount - str_replace(',', '.', $item['amountnet']);
//                }
                $article = array(
                    'L_NUMBER' . $key   => $item['ordernumber'],
                    'L_NAME' . $key     => $item['articlename'],
                    'L_AMT' . $key      => number_format($amount / $item['quantity'], 2, '.', ''),
                    'L_QTY' . $key      => $item['quantity'],
//                    'L_TAXAMT' . $key   => $tax
                );
//            if($item['modus'] == 4) {
//                $article['type'] = 'handling';
//            } else {
//                $article['type'] = $price >= 0 ? 'goods' : 'voucher';
//            }
                $params = array_merge($params, $article);
            }
        }

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
        if(empty($user)) {
            return array();
        }
        $shipping = $user['shippingaddress'];
        $name = $shipping['firstname'] . ' ' . $shipping['lastname'];
        if(!empty($shipping['company'])) {
            $name = $shipping['company'] . ' - ' . $name;
        }
        $customer = array(
            'CUSTOMERSERVICENUMBER' => $user['billingaddress']['customernumber'],
            //'gender' => $shipping['salutation'] == 'ms' ? 'f' : 'm',
            'SHIPTONAME' => $name,
            'SHIPTOSTREET' => $shipping['street'] . ' ' .$shipping['streetnumber'],
            'SHIPTOSTREET2' => '',
            'SHIPTOZIP' => $shipping['zipcode'],
            'SHIPTOCITY' => $shipping['city'],
            'SHIPTOCOUNTRY' => $user['additional']['countryShipping']['countryiso'],
            'EMAIL' => $user['additional']['user']['email'],
            'SHIPTOPHONENUM' => $user['billingaddress']['phone'],
            'LOCALECODE' => Shopware()->Locale()->getRegion(),
        );
        if(!empty($user['additional']['stateShipping']['shortcode'])) {
            $customer['SHIPTOSTATE'] = $user['additional']['stateShipping']['shortcode'];
        }
        return $customer;
    }

    /**
     * Returns the payment plugin config data.
     *
     * @return Shopware_Plugins_Frontend_SwagPaymentPaypal_Bootstrap
     */
    public function Plugin()
    {
        return Shopware()->Plugins()->Frontend()->SwagPaymentPaypal();
    }
}
