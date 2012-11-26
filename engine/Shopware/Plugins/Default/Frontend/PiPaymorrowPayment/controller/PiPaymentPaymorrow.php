<?php

class Shopware_Controllers_Frontend_PiPaymentPaymorrow extends Shopware_Controllers_Frontend_Payment {

    public $orderArr = null;

    /**
     * Index action method
     *
     * return void
     */
    public function indexAction() {
        if ($this->getPaymentShortName() == 'PaymorrowInvoice'
                || $this->getPaymentShortName() == 'PaymorrowRate') {
            return $this->redirect(array('action' => 'gateway'));
        }
        else {
            return $this->redirect(array(
                        'controller' => 'checkout')
            );
        }
    }
    
    


    /**
     * Paymorrow action method
     *
     * return void
     */
    public function gatewayAction() {
        
        $piPaymorrowUser = array();
        $piPaymorrowBasket = array();
        $piPaymorrowUser = $this->getUser();
        $piPaymorrowBasket = $this->getBasket();
 
        $piPaymorrowConfig = Shopware()->Plugins()->Frontend()->PiPaymorrowPayment()->Config();
        Shopware()->Session()->soapBody = new DOMDocument('1.0', 'UTF-8');
        $hash = md5(time());
        $orderNumber = "";
        if (!Shopware()->Session()->orderNumber) {
            $order = Shopware()->Modules()->Order();
            $orderNumber = $order->sGetOrderNumber();
            Shopware()->Session()->orderNumber = $orderNumber;
        }
        else {
            $orderNumber = Shopware()->Session()->orderNumber;
            Shopware()->Db()->query("DELETE FROM s_order WHERE ordernumber = ?", array($orderNumber));
        }
        Shopware()->Session()->hash = $hash;
        require_once dirname(__FILE__) . '/../paymorrow_direct_webservice_client/index.php';

        Shopware()->Session()->paymorrowOrderResponse = $paymorrowOrderResponse->responseResultURL;
        $this->View()->PaymorrowConfig = $piPaymorrowConfig;
        
        $this->writeIframeResponseLog($paymorrowOrderResponse);
        if ($paymorrowOrderResponse->responseResultCode == 'ACCEPTED') {
            $sql = "INSERT INTO `pi_paymorrow_orders` (`ordernumber`, `type`, `signature`, `fullSend`) VALUES(?, ?, ?, ?)";
            Shopware()->Db()->query($sql, array(
               $paymorrowOrderResponse->responsePaymorrowOrder->paymorrowOrderRequest->order->orderId,
               $this->getPaymentShortName(),
               $hash,
               (int)0
            ));
            
            $this->mySaveOrder();
            
            $sql = "SELECT id FROM s_order WHERE ordernumber = ?";
            $orderID = Shopware()->Db()->fetchOne($sql, array($orderNumber));
			if($piPaymorrowUser["billingaddress"]["stateID"] == '') {
				$piPaymorrowUser["billingaddress"]["stateID"] = 0;
				$piPaymorrowUser["shippingaddress"]["stateID"] = 0;
			}
            Shopware()->Modules()->Order()->sSaveBillingAddress($piPaymorrowUser["billingaddress"], $orderID);
            Shopware()->Modules()->Order()->sSaveShippingAddress($piPaymorrowUser["shippingaddress"], $orderID);
        }
        else {
            if($paymorrowOrderResponse->responseError->resonseErrorNO=='510') Shopware()->Session()->orderNumber+=1;
            Shopware()->Session()->sPaymorrowPaymentError = $paymorrowOrderResponse->responseError->responseErrorMessage;
            if (!Shopware()->Session()->sPaymorrowPaymentError)
                Shopware()->Session()->sPaymorrowPaymentError = "Es gab einen unerwarteten Fehler. Bitte w&auml;hlen Sie eine andere Zahlart oder versuchen Sie es erneut.";
            return $this->redirect(array(
                        'controller' => 'account',
                        'action' => 'payment',
                        'sTarget' => 'checkout')
            );
        }
        if (empty($paymorrowOrderResponse->responseResultURL)
                || $paymorrowOrderResponse->responseResultCode != 'ACCEPTED') {
            Shopware()->Session()->sPaymorrowPaymentError = $paymorrowOrderResponse->responseError->responseErrorMessage;
            if (!Shopware()->Session()->sPaymorrowPaymentError) 
                Shopware()->Session()->sPaymorrowPaymentError = "Es gab einen unerwarteten Fehler. Bitte w&auml;hlen Sie eine andere Zahlart oder versuchen Sie es erneut.";
            return $this->redirect(array(
                        'controller' => 'account',
                        'action' => 'payment',
                        'sTarget' => 'checkout')
            );
        }
        $this->View()->loadTemplate("_default/frontend/checkout/gateway.tpl");
        $this->View()->gatewayUrl = $paymorrowOrderResponse->responseResultURL;
    }

    /**
     *  After IFrame is Accepted or Pending ---> Go to finish
     *
     *  return void
     */
    public function endAction() {
        $orderNumber = Shopware()->Session()->orderNumber;
        Shopware()->Modules()->Order()->sDeleteTemporaryOrder();
//        Shopware()->Modules()->Order()->sSaveOrderBundle($orderNumber);
//        Shopware()->Modules()->Order()->sSaveOrderLiveShopping();
        Shopware()->Session()->orderNumber = false;
        Shopware()->Session()->sPaymorrowPaymentError = false;
        Shopware()->Db()->query("DELETE FROM s_order_basket WHERE sessionID = ?", array(Shopware()->SessionID()));
        if (isset(Shopware()->Session()->sOrderVariables)) {
            $variables = Shopware()->Session()->sOrderVariables;
            $variables['ordernumber'] = $orderNumber;
        }
        $this->redirect(array(
            'controller' => 'checkout',
            'action' => 'finish',
            'sUniqueID' => Shopware()->Session()->hash)
        );
    }

    /**
     *  After IFrame is Declined---> Go to change payment
     *
     *  return void
     */
    public function cancelAction() {
        Shopware()->Session()->pi_Paymorrow_no_paymorrow = true;
        return $this->redirect(array(
            'controller' => 'account',
            'action' => 'payment',
            'sTarget' => 'checkout')
        );
    }    
    /**
     *  After IFrame is Declined---> Go to change payment
     *
     *  return void
     */
    public function enableAction() {
        unset(Shopware()->Session()->pi_Paymorrow_no_paymorrow);
        unset(Shopware()->Session()->sPaymorrowPaymentError);
        return $this->redirect(array(
            'controller' => 'account',
            'action' => 'payment',
            'sTarget' => 'checkout')
        );
    }

    /**
     *  After IFrame is Declined---> Go to change payment
     *
     *  return void
     */
    public function changePaymentAction() {
        if(Shopware()->Session()->hash){
            $pi_paymorrow_user = $this->getUser();
            Shopware()->Session()->pi_Paymorrow_no_paymorrow = true;
            $sql = "UPDATE s_user SET paymentID = 0 WHERE id = ?";
            Shopware()->Db()->query($sql, array((int)$pi_paymorrow_user['billingaddress']['userID']));
            return $this->redirect(array(
                    'controller' => 'account',
                    'action' => 'payment',
                    'sTarget' => 'checkout')
            );
        }
    }

    /**
     *  Notify action called by Paymorrow
     *
     *  return void
     */
    public function notifyAction() {
        $this->View()->setTemplate();
        $postdata = file_get_contents("php://input");
        
        if(empty($postdata)) $postdata=$_REQUEST;
        $this->writeNotifyLog($postdata);
        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->loadXML($postdata);        
        $oid = $xml->getElementsByTagName('orderId')->item(0)->nodeValue;
        
        if ($this->isOrderRequestModified($xml) == "true") {
            if (!$this->changeResponseResultCode($oid, $xml)) {
                $retVal = false;
            }
            if (!$this->changeOrderBillingAddress($oid, $xml)) {
                $retVal = false;
            }
            if (!$this->changeOrderShippingAddress($oid, $xml)) {
                $retVal = false;
            }
            if (!$this->checkCustomerPersonalDetails($oid, $xml)) {
                $retVal = false;
            }
        }
        Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Shopware_Adodb'));
    }


    /**
     * Checks response code and updates bankdata and orderstate
     *
     * @param String $orderId   Current order id
     * @param Object $xml        XML-response
     *
     * @return bool
     */
    public function changeResponseResultCode($orderId, $xml) {
        $sql = "SELECT id FROM s_order WHERE ordernumber like ?";
        $pi_Paymorrow_orderid = Shopware()->Db()->fetchOne($sql, array($orderId));
        if ($xml->getElementsByTagName('responseResultCode')->item(0)->nodeValue == 'ACCEPTED') {
            
            $status = $this->getPaymorrowGoStatusId();
            $bankdata = $this->getBankdata($xml, $pi_Paymorrow_orderid);
            $this->setBankdata($bankdata, $orderId);
        }
        elseif ($xml->getElementsByTagName('responseResultCode')->item(0)->nodeValue == 'PENDING') {
            $this->setTransactionID($xml, $orderId);
            $status = $this->getPaymorrowPendingStatusId();
            $bankdata = $this->getBankdata($xml, $pi_Paymorrow_orderid);
            $this->setBankdata($bankdata, $orderId);
        }
        else {
            $status = $this->getPaymorrowDeclinedStatusId();
            Shopware()->Session()->pi_Paymorrow_no_paymorrow = true;
            return $this->redirect(array(
                'controller' => 'account',
                'action' => 'payment',
                'sTarget' => 'checkout')
            );
        }
        
        $sql = "UPDATE `s_order` SET `cleared` = ?, `status` = ? WHERE id = ?";
        Shopware()->Db()->query($sql, array((int)$status, (int)0, (int)$pi_Paymorrow_orderid));
        Shopware()->Session()->status = $status;
        if ($xml->getElementsByTagName('responseResultCode')->item(0)->nodeValue == 'ACCEPTED') {
            Shopware()->Cache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('Shopware_Adodb'));
            $this->myCreateMail($pi_Paymorrow_orderid);
        }
        return true;
    }

    /**
     * Returns the full user data as array
     *
     * @return array
     */
    public function getUser() {
        if (!empty(Shopware()->Session()->sOrderVariables['sUserData'])) {
            return Shopware()->Session()->sOrderVariables['sUserData'];
        }
        else {
            return null;
        }
    }

    /**
     * Returns the full basket data as array
     *
     * @return array
     */
    public function getBasket() {
        if (!empty(Shopware()->Session()->sOrderVariables['sBasket'])) {
            return Shopware()->Session()->sOrderVariables['sBasket'];
        }
        else {
            return null;
        }
    }

    /**
     * Returns the value in lower case
     * @param Object $_nodes    xml node
     * @param string $_name     name to get
     * @return array
     */
    public function getValue($_nodes, $_name) {
        $retVal = '';
        for ($i = 0; $i < $_nodes->length; $i++) {
            if (strtolower($_nodes->item($i)->tagName) == strtolower($_name)) {
                $retVal = $_nodes->item($i)->nodeValue;
                break;
            }
        }
        return $this->codeString($retVal);
    }

    /**
     * de/encodes Strings in UTF8
     *
     * @param string $_string    String to de/encode
     *
     * @return String
     */
    public function codeString($_string) {
        if (strstr(strtoupper(mb_detect_encoding($_string)), "UTF-8") === false) {
            return utf8_encode($_string);
        }
        else {
            return utf8_decode($_string);
        }
    }

    /**
     * get Bankdata from XML as array
     *
     * @param Object $xml    Current XML Object
     *
     * @return Array
     */
    public function getBankdata($xml, $piPaymorrowOrderid) {
        //Initial of bank data
        $sql = "SELECT userID FROM s_order_billingaddress WHERE orderID = ?";
        $piPaymorrowUserID = Shopware()->Db()->fetchOne($sql, array((int)$piPaymorrowOrderid));
        $sql = "SELECT customernumber FROM s_user_billingaddress WHERE userID = ?";
        $piPaymorrowCustomerNumber = Shopware()->Db()->fetchOne($sql, array((int)$piPaymorrowUserID));
        $bankdata = array('bank' => 'Commerzbank AG',
            'bic' => $this->getValue($xml->getElementsByTagName('paymentAdvice')->item(0)->childNodes, 'BIC'),
            'iban' => $this->getValue($xml->getElementsByTagName('paymentAdvice')->item(0)->childNodes, 'IBAN'),
            'kto' => $this->getValue($xml->getElementsByTagName('paymentAdvice')->item(0)->childNodes, 'nationalBankAccountNumber'),
            'blz' => $this->getValue($xml->getElementsByTagName('paymentAdvice')->item(0)->childNodes, 'nationalBankCode'),
            'requestid' => $xml->getElementsByTagName('requestId')->item(0)->nodeValue,
            'responsecode' => $xml->getElementsByTagName('responseResultCode')->item(0)->nodeValue);
        $bankdata['zweck'] = 'BESTELLNR' . $this->getValue($xml->getElementsByTagName('order')->item(0)->childNodes, 'orderId');
        $bankdata['zweck'].=' KD' . $piPaymorrowCustomerNumber;
        return $bankdata;
    }

    /**
     * Updates Bankdata of current order
     *
     * @param Array  $bankdata    Bankdata-array
     * @param String $_orderId    Current Orderid
     *
     * @return Bool
     */
    public function setBankdata($bankdata, $_orderId) {
        $sql = "UPDATE `pi_paymorrow_orders`
                SET
                    `bic` = ?,
                    `iban` = ?,
                    `nationalBankCode` = ?,
                    `nationalBankAccountNumber` = ?,
                    `nationalBankName` = ?,
                    `paymentReference` = ?,
                    `requestid` = ?,
                    `responseResultCode` = ?
                WHERE ordernumber = ?";
        Shopware()->Db()->query($sql, array(
            $bankdata['bic'], 
            $bankdata['iban'], 
            $bankdata['blz'],
            $bankdata['kto'],
            $bankdata['bank'],
            $bankdata['zweck'],
            $bankdata['requestid'],
            $bankdata['responsecode'],
            $_orderId
        ));
        return true;
    }

    /**
     * Updates Transaction ID of current order
     *
     * @param Object $xml         Current XML Object
     * @param String $_orderId    Current Orderid
     *
     * @return Array
     */
    public function setTransactionID($xml, $ordernumber) {
        $sql = "UPDATE `s_order` SET `transactionID` =  ? WHERE ordernumber = ?";
        Shopware()->Db()->query($sql, array(
            $xml->getElementsByTagName('paymorrowTransactionId')->item(0)->nodeValue,
            $ordernumber
        ));
        $sql = "UPDATE `pi_paymorrow_orders` SET `transactionid` = ? WHERE ordernumber = ?";
        Shopware()->Db()->query($sql, array(
            $xml->getElementsByTagName('paymorrowTransactionId')->item(0)->nodeValue,
            $ordernumber
        ));
    }

    /**
     * Writes Textlogs
     *
     * @param Object $xml         Current XML Object
     */
    public function writeNotifyLog($xml) {
        $fout = @fopen(dirname(__FILE__) . '/../log/notifyResponse.txt', 'a+');
        @fwrite($fout, "Called   " . date('Y-m-d  H:i:s') . "\r");
        @fwrite($fout, "-----------------------------\r");
        @fwrite($fout, "Get			  " . print_r($_GET, true) . "\r");
        @fwrite($fout, "-----------------------------\r");
        @fwrite($fout, "Server_Argv	  " . print_r($_SERVER['argv'], true) . "\r");
        @fwrite($fout, "-----------------------------\r");
        @fwrite($fout, "Server-Url	  " . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "\r");
        @fwrite($fout, "-----------------------------\n\r");
        @fwrite($fout, "XML			  " . print_r($xml, true) . "\r");
        @fwrite($fout, "------------------------------------------------------------------------------\n\r");
        @fclose($fout);
    }

    /**
     * Writes Textlogs
     * @param Object $xml         Current XML Object
     */
    public function writeIframeResponseLog($xml) {;
        if(Shopware()->Plugins()->Frontend()->PiPaymorrowPayment()->Config()->sandbox_mode)
        {
            $fout = @fopen(dirname(__FILE__) . '/../log/iframeResponse.txt', 'a+');
            @fwrite($fout, "Called   " . date('Y-m-d  H:i:s') . "\r");
            @fwrite($fout, "-----------------------------\r");
            @fwrite($fout, "Get			  " . print_r($_GET, true) . "\r");
            @fwrite($fout, "-----------------------------\r");
            @fwrite($fout, "Server_Argv	  " . print_r($_SERVER['argv'], true) . "\r");
            @fwrite($fout, "-----------------------------\r");
            @fwrite($fout, "Server-Url	  " . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "\r");
            @fwrite($fout, "-----------------------------\n\r");
            @fwrite($fout, "XML			  " . print_r($xml, true) . "\r");
            @fwrite($fout, "------------------------------------------------------------------------------\n\r");
            @fclose($fout);
        }
    }

    /**
     * Writes Error
     *
     * @param Object $xml         Current XML Object
     */
    public function writeErrorLog($xml) {
        $fout = @fopen(dirname(__FILE__) . '/../log/errorlog.txt', 'a+');
        @fwrite($fout, "Called   " . date('Y-m-d  H:i:s') . "\r");
        @fwrite($fout, "-----------------------------\r");
        @fwrite($fout, "Get			  " . print_r($_GET, true) . "\r");
        @fwrite($fout, "-----------------------------\r");
        @fwrite($fout, "Server_Argv	  " . print_r($_SERVER['argv'], true) . "\r");
        @fwrite($fout, "-----------------------------\r");
        @fwrite($fout, "Server-Url	  " . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'] . "\r");
        @fwrite($fout, "-----------------------------\n\r");
        @fwrite($fout, "Error:			  " . print_r($xml, true) . "\r");
        @fwrite($fout, "------------------------------------------------------------------------------\n\r");
        @fclose($fout);
    }

    /**
     * Get shipping name of the current order
     *
     * @param Object $xml         Current XML Object
     *
     * @return String
     */
    public function getShippingName($xml) {
        $addressContact = $this->getValue($xml->getElementsByTagName('orderShippingAddress')->item(0)->childNodes, 'addressContact');

        if (strlen($addressContact) == 0) {
            $addressContact[0] = $this->getValue($xml->getElementsByTagName('customerPersonalDetails')->item(0)->childNodes, 'customerGivenName');
            $addressContact[1] = $this->getValue($xml->getElementsByTagName('customerPersonalDetails')->item(0)->childNodes, 'customerSurname');
            return $addressContact;
        }
        else {
            $addressContact = explode(' ', $addressContact);
            return $addressContact;
        }
    }

    /**
     * Get store owner
     *
     * @return String
     */
    public function getStoreowner() {
        $shopname = Shopware()->Config()->Shopname;
        return $shopname;
    }

    /**
     * Get email address of the store owner
     *
     * @return String
     */
    public function getStoreownerEmail() {
        $mail = Shopware()->Config()->Mail;
        return $mail;
    }

    /**
     * Get billing name of the current order
     *
     * @param Object $xml         Current XML Object
     *
     * @return String
     */
    public function getBillingName($xml) {
        $addressContact = $this->getValue($xml->getElementsByTagName('customerAddress')->item(0)->childNodes, 'addressContact');
        if (strlen($addressContact) == 0) {
            $addressContact[0] = $this->getValue($xml->getElementsByTagName('customerPersonalDetails')->item(0)->childNodes, 'customerGivenName');
            $addressContact[1] = $this->getValue($xml->getElementsByTagName('customerPersonalDetails')->item(0)->childNodes, 'customerSurname');
            return $addressContact;
        }
        else {
            $addressContact = explode(' ', $addressContact);
            return $addressContact;
        }
    }

    /**
     * Updates shippingaddress of the current user
     *
     * @param Object $xml         Current XML Object
     * @param String $_orderId    Current Order id
     *
     * @return Bool
     */
    public function changeOrderShippingAddress($_orderId, $xml) {
        $address = $this->getValue($xml->getElementsByTagName('orderShippingAddress')->item(0)->childNodes, 'addressStreet');
        $housenr = $this->getValue($xml->getElementsByTagName('orderShippingAddress')->item(0)->childNodes, 'addressHouseNo');
        $city = $this->getValue($xml->getElementsByTagName('orderShippingAddress')->item(0)->childNodes, 'addressLocality');
        $postcode = $this->getValue($xml->getElementsByTagName('orderShippingAddress')->item(0)->childNodes, 'addressPostalCode');
        $state = $this->getValue($xml->getElementsByTagName('orderShippingAddress')->item(0)->childNodes, 'addressProvince');
        $salutation = $this->getValue($xml->getElementsByTagName('customerPersonalDetails')->item(0)->childNodes, 'customerGender');
        if ($salutation == 'M') $salutation = 'mr';
        else $salutation = 'ms';
        $sql = "SELECT id FROM s_order WHERE ordernumber = ?";
        $_orderId = Shopware()->Db()->fetchOne($sql, array($_orderId));
        $customer = $this->getShippingName($xml);
        $sql = "UPDATE s_order_shippingaddress
                SET
                    salutation = ?,
                    firstname = ?,
                    lastname = ?,
                    street = ?,
                    streetnumber = ?,
                    zipcode = ?,
                    city = ?
                WHERE orderID = ?";
        Shopware()->Db()->query($sql, array($salutation, utf8_encode($customer[0]), utf8_encode($customer[1]), utf8_encode($address), $housenr, $postcode, utf8_encode($city), $_orderId));
        return true;
    }

    /**
     * Updates billingaddress of the current user
     *
     * @param Object $xml         Current XML Object
     * @param String $_orderId    Current Order id
     *
     * @return Bool
     */
    public function changeOrderBillingAddress($_orderId, $xml) {
        $address = $this->getValue($xml->getElementsByTagName('customerAddress')->item(0)->childNodes, 'addressStreet');
        $housenr = $this->getValue($xml->getElementsByTagName('customerAddress')->item(0)->childNodes, 'addressHouseNo');
        $city = $this->getValue($xml->getElementsByTagName('customerAddress')->item(0)->childNodes, 'addressLocality');
        $postcode = $this->getValue($xml->getElementsByTagName('customerAddress')->item(0)->childNodes, 'addressPostalCode');
        $state = $this->getValue($xml->getElementsByTagName('customerAddress')->item(0)->childNodes, 'addressProvince');
        $salutation = $this->getValue($xml->getElementsByTagName('customerPersonalDetails')->item(0)->childNodes, 'customerGender');
        $phone = $this->getValue($xml->getElementsByTagName('customerPersonalDetails')->item(0)->childNodes, 'customerPhoneNo');
        if ($salutation == 'M') $salutation = 'mr';
        else $salutation = 'ms';
        $customer = $this->getBillingName($xml);
        $sql = "SELECT id FROM s_order WHERE ordernumber = ?";
        $_orderId = Shopware()->Db()->fetchOne($sql, array($_orderId));       
        $sql = "UPDATE s_order_billingaddress
                SET
                    salutation = ?,
                    firstname = ?,
                    lastname = ?,
                    street = ?,
                    streetnumber = ?,
                    zipcode = ?,
                    city = ?,
                    phone = ?
                WHERE orderID = ?";
        Shopware()->Db()->query($sql, array($salutation, utf8_encode($customer[0]), utf8_encode($customer[1]), utf8_encode($address), $housenr, $postcode, utf8_encode($city), $phone, $_orderId));
        return true;
    }

    /**
     * Updates personal details of the current user as comment to the current order
     *
     * @param Object $xml        Current XML Object
     * @param String $oid        Current Order id
     *
     * @return Bool
     */
    public function checkCustomerPersonalDetails($oid, $xml) {
        $sql = "SELECT userID FROM s_order WHERE ordernumber = ?";
        $userid = Shopware()->Db()->fetchOne($sql, array($oid));
        $email = $this->getValue($xml->getElementsByTagName('customerPersonalDetails')->item(0)->childNodes, 'customerEmail');
        $mobile = $this->getValue($xml->getElementsByTagName('customerPersonalDetails')->item(0)->childNodes, 'customerMobileNo');
        $birthday = explode("+", $this->getValue($xml->getElementsByTagName('customerPersonalDetails')->item(0)->childNodes, 'customerDateOfBirth'));
        $sql = "SELECT email FROM s_user WHERE id = ?";
        $shop_email = Shopware()->Db()->fetchOne($sql, array($userid));
        $sql = "SELECT birthday FROM s_user_billingaddress WHERE userID = ?";
        $shop_birthday = Shopware()->Db()->fetchOne($sql, array((int)$userid));
        $internalcomment = 'CHANGED USERDATA:\n';
        if ($email != $shop_email) $internalcomment.='EMAIL:' . $email . '\n';
        if ($mobile) $internalcomment.='MOBILE:' . $mobile . '\n';
        if ($birthday[0] != $shop_birthday) $internalcomment.='BITHDAY:' . $birthday[0];
        if ($internalcomment != 'CHANGED USERDATA:\n') {
            $sql = "UPDATE s_order SET internalcomment = ? WHERE ordernumber = ?";
            Shopware()->Db()->query($sql, array($internalcomment, $oid));
        }
        return true;
    }

    /**
     * Gets current orderstate
     *
     * @param String $oid        Current Order id
     *
     * @return Int
     */
    public function getCurrentOrderStatus($_oid) {
        $sql = "SELECT cleared FROM s_order WHERE ordernumber = ?";
        $orderstatus = Shopware()->Db()->fetchOne($sql, array($_oid));
        return $orderstatus;
    }

    /**
     * Gets ID of Paymorrow pending state
     *
     * @return Int
     */
    public function getPaymorrowPendingStatusId() {
        $sql = "SELECT id FROM s_core_states WHERE description like '%Paymorrow Pending%'";
        $orderstatus = Shopware()->Db()->fetchOne($sql);
        return $orderstatus;
    }

    /**
     * Gets ID of Paymorrow declined state
     *
     * @return Int
     */
    public function getPaymorrowDeclinedStatusId() {
        $sql = "SELECT id FROM s_core_states WHERE description like '%Paymorrow Declined%'";
        $orderstatus = Shopware()->Db()->fetchOne($sql);
        return $orderstatus;
    }

    /**
     * Gets ID of Paymorrow accepted state
     *
     * @return Int
     */
    public function getPaymorrowGoStatusId() {
        $sql = "SELECT id FROM s_core_states WHERE description like '%Paymorrow Accepted%'";
        $orderstatus = Shopware()->Db()->fetchOne($sql);
        return $orderstatus;
    }

    /**
     * Gets all orderdata
     *
     * @param String $_orderId     Current Order id
     *
     * @return Array
     */
    public function getOrder($_orderId) {
        $sql = "SELECT * FROM s_order WHERE ordernumber = ?";
        $order = Shopware()->Db()->fetchRow($sql, array($_orderId));
        return $order;
    }

    /**
     * Gets current request id
     *
     * @param Object $xml     Current XML Response
     *
     * @return String
     */
    public function getRequestId($xml) {
        return $xml->getElementsByTagName('requestId')->item(0)->nodeValue;
    }

    /**
     * Checks if order is modified
     *
     * @param Object $xml     Current XML Response
     *
     * @return Bool
     */
    public function isOrderRequestModified($xml) {
        return $xml->getElementsByTagName('paymorrowOrderRequestModified')->item(0)->nodeValue;
    }

    /**
     *  Saves current order
     *
     * @return String
     */
    public function mySaveOrder() {
        $myComment = stripslashes(Shopware()->Session()->sOrderVariables['sComment']);
        $myComment = stripcslashes($myComment);
        $shopid = Shopware()->Shop()->getId();
        $sql = "SELECT isocode FROM s_core_multilanguage WHERE id = ?";
        $isocode = Shopware()->Db()->fetchOne($sql, array((int)$shopid));
        $sql = "SELECT defaultcurrency FROM s_core_multilanguage WHERE id = ?";
        $currencyid = Shopware()->Db()->fetchOne($sql, array((int)$shopid));
        $sql = "SELECT currency FROM s_core_currencies WHERE id = ?";
        $currency = Shopware()->Db()->fetchOne($sql, array((int)$currencyid));
        $sql = "SELECT factor FROM s_core_currencies WHERE id = ?";
        $faktor = Shopware()->Db()->fetchOne($sql, array((int)$currencyid));
        $pi_paymorrow_user = $this->getUser();
        $pi_paymorrow_basket = $this->getBasket();
        if (!$pi_paymorrow_basket["sShippingcostsWithTax"]) $pi_paymorrow_basket["sShippingcostsWithTax"] = "0";
        if (!$pi_paymorrow_basket["sShippingcostsNet"]) $pi_paymorrow_basket["sShippingcostsNet"] = "0";
        if (!$pi_paymorrow_basket["AmountNumeric"]) $pi_paymorrow_basket["AmountNumeric"] = $pi_paymorrow_basket["AmountNetNumeric"];
        $pi_paymorrow_basket["AmountNetNumeric"] = round($pi_paymorrow_basket["AmountNetNumeric"], 2);
        // Check if tax-free        
        $sqlData = "SELECT id, tax FROM s_core_customergroups WHERE groupkey = ?";
        $dataUserGroups = Shopware()->Db()->fetchRow($sqlData, array($pi_paymorrow_user["additional"]["user"]["customergroup"]));
        $net = $dataUserGroups['tax'];
        $id = $dataUserGroups['id'];
        
        
        if ((Shopware()->Config()->Articlesoutputnetto && !$net) || (!$net && $id)) {
            $taxfree = "0";
            if (!$net) {
                // Complete net delivery
                $net = "1";
                $pi_paymorrow_basket["AmountNumeric"] = $pi_paymorrow_basket["AmountNetNumeric"];
                $pi_paymorrow_basket["sShippingcostsWithTax"] = $pi_paymorrow_basket["sShippingcostsNet"];
                $taxfree = "1";
            }
        }
        else {
            $net = "0";
            $taxfree = "0";
        }

        //unset($this->sSYSTEM->_SESSION["sPartner"]);
        if (empty(Shopware()->Session()->sOrderVariables['sPartner'])) {
            //"additional"]["user"]
            $pid = $pi_paymorrow_user["additional"]["user"]["affiliate"];

            if (!empty($pid) && $pid != "0") {
                // Get Partner code
                $partner = Shopware()->Db()->fetchOne("
                    SELECT idcode FROM s_emarketing_partner WHERE id = ?
                    ", array($pid)
                );
            }
            else {
                $partner = "";
            }
        }
        else {
            $partner = Shopware()->Session()->sOrderVariables['sPartner'];
        }
        if (Shopware()->Session()->sOrderVariables['sDispatch']['id']) {
            $dispatchId = Shopware()->Session()->sOrderVariables['sDispatch']['id'];
        }
        else {
            $dispatchId = "0";
        }
       if(!Shopware()->Session()->sOrderVariables['sReferer'])Shopware()->Session()->sOrderVariables['sReferer']="";

       $sql = "
          INSERT INTO s_order (
            ordernumber, 
            userID, 
            invoice_amount,
            invoice_amount_net, 
            invoice_shipping,
            invoice_shipping_net, 
            ordertime, 
            status, 
            cleared, 
            paymentID, 
            transactionID, 
            customercomment, 
            net,
            taxfree, 
            partnerID,
            temporaryID,
            referer,
            language,
            dispatchID,
            currency,
            currencyFactor,
            subshopID,
            remote_addr)
          VALUES (
          ".Shopware()->Db()->quote((string) Shopware()->Session()->orderNumber).",
           ".$pi_paymorrow_user["additional"]["user"]["id"].",
           ".$pi_paymorrow_basket["AmountNumeric"].",
           ".$pi_paymorrow_basket["AmountNetNumeric"].",
           ".floatval($pi_paymorrow_basket["sShippingcostsWithTax"]).",
           ".floatval($pi_paymorrow_basket["sShippingcostsNet"]).",
           now(),
           -1,
           -1,
           ".$pi_paymorrow_user["additional"]["user"]["paymentID"].",
           '',
           ".Shopware()->Db()->quote($myComment).",
           $net,
           $taxfree,
           ".Shopware()->Db()->quote((string) $partner).",
           ".Shopware()->Db()->quote((string) Shopware()->Session()->hash).",
           ".Shopware()->Db()->quote((string) Shopware()->Session()->sOrderVariables['sReferer']).",
           '".$isocode."',
           '$dispatchId',
           'EUR',
           '".$faktor."',
           '".Shopware()->Shop()->getId()."',
           ".Shopware()->Db()->quote((string) $_SERVER['REMOTE_ADDR'])."
          )
        ";
        Shopware()->Db()->query($sql);
        $insertedOrderId = Shopware()->Db()->lastInsertId('s_order');
        //new attribute table with shopware 4
        $attributeSql = "INSERT INTO s_order_attributes (orderID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
                VALUES (
                    " . $insertedOrderId  .",
                    ".Shopware()->Db()->quote((string) '').",
                    ".Shopware()->Db()->quote((string) '').",
                    ".Shopware()->Db()->quote((string) '').",
                    ".Shopware()->Db()->quote((string) '').",
                    ".Shopware()->Db()->quote((string) '').",
                    ".Shopware()->Db()->quote((string) '')."
                )";
        $attributeSql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveOrderAttributes_FilterSQL', $attributeSql, array('subject'=>$this));
            
        Shopware()->Db()->exec($attributeSql);
        
        $position = 0;
        foreach ($pi_paymorrow_basket["content"] as $basketRow) {
            $position++;
            $amountRow = $this->myFormatPrice($basketRow["priceNumeric"] * $basketRow["quantity"]);

            if (!$basketRow["price"]) $basketRow["price"] = "0,00";
            if (!$amountRow) $amountRow = "0,00";

            $basketRow["articlename"] = str_replace("<br />", "\n", $basketRow["articlename"]);
            $basketRow["articlename"] = html_entity_decode($basketRow["articlename"]);
            $basketRow["articlename"] = strip_tags($basketRow["articlename"]);

            if (!$basketRow["itemInfo"]) {
                $priceRow = $basketRow["price"];
            }
            else {
                $priceRow = $basketRow["itemInfo"];
            }

            //Bundle-Article
            if ($basketRow["modus"] == 10) {
                $sqlBundleTax = "
                    SELECT `taxID`
                    FROM `s_articles_bundles`
                    WHERE `ordernumber` = ?
                ";
                $bundleTax = Shopware()->Db()->fetchOne($sqlBundleTax, array($basketRow["ordernumber"]));
                if (!empty($bundleTax)) $basketRow["taxID"] = $bundleTax;
            }

            $basketRow["articlename"] = $this->myOptimizeText($basketRow["articlename"]);

            if (!$basketRow["esdarticle"]) {
                $basketRow["esdarticle"] = "0";
            }
            if (!$basketRow["modus"]) {
                $basketRow["modus"] = "0";
            }
            if (!$basketRow["taxID"]) {
                $basketRow["taxID"] = "0";
            }
            if ($net == true) {
                $basketRow["taxID"] = "0";
            }
            $orderIdSql = "SELECT id FROM s_order WHERE ordernumber  = ?";
            $orderID = Shopware()->Db()->fetchOne($orderIdSql, array(Shopware()->Session()->orderNumber));
            $orderNumber = Shopware()->Session()->orderNumber;
            $sql = "
               INSERT INTO s_order_details
                (orderID,
                ordernumber,
                articleID,
                articleordernumber,
                price,
                quantity,
                name,
                status,
                releasedate,
                modus,
                esdarticle,
                taxID
                )
                VALUES (
                $orderID,
                ".Shopware()->Db()->quote((string) $orderNumber).",
                {$basketRow["articleID"]},
                '{$basketRow["ordernumber"]}',
                {$basketRow["priceNumeric"]},
                {$basketRow["quantity"]},
                '".addslashes($basketRow["articlename"])."',
                0,
                '0000-00-00',
                {$basketRow["modus"]},
                {$basketRow["esdarticle"]},
                {$basketRow["taxID"]}
               )";
            $sql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveOrder_FilterDetailsSQL', $sql, array(
                'subject' => $this,
                'row' => $basketRow,
                'user' => $pi_paymorrow_user,
                'order' => array(
                    "id" => $orderID,
                    "number" => $orderNumber
            )));
                        
            // Check for individual voucher - code
            if ($basketRow["modus"] == 2) {
                // $basketRow["articleID"] => s_emarketing_voucher_codes.id
                // $basketRow["ordernumber"] => Check mode
                $getVoucher = Shopware()->Db()->fetchRow("
                    SELECT modus,id FROM s_emarketing_vouchers
                    WHERE ordercode=?
                    ", array($basketRow["ordernumber"])
                );

                if ($getVoucher["modus"] == 1) {
                    // Update Voucher - Code
                    $updateVoucher = Shopware()->Db()->query("
                        UPDATE s_emarketing_voucher_codes
                        SET cashed = 1, userID= ?
                        WHERE id = ?
                        ", array($pi_paymorrow_user["additional"]["user"]["id"], $basketRow["articleID"])
                    );
                }
            }

            if ($basketRow["esdarticle"]) $esdOrder = true;

            Shopware()->Db()->query($sql);
            
            $orderdetailsID = Shopware()->Db()->lastInsertId('s_order_details');
            //new attribute tables
            $attributeSql = "INSERT INTO s_order_details_attributes (detailID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
                             VALUES ("
                             .$orderdetailsID. "," .
                             Shopware()->Db()->quote((string) $basketRow["ob_attr1"]).",".
                             Shopware()->Db()->quote((string) $basketRow["ob_attr2"]).",".
                             Shopware()->Db()->quote((string) $basketRow["ob_attr3"]).",".
                             Shopware()->Db()->quote((string) $basketRow["ob_attr4"]).",".
                             Shopware()->Db()->quote((string) $basketRow["ob_attr5"]).",".
                             Shopware()->Db()->quote((string) $basketRow["ob_attr6"]).
            ")";
            $attributeSql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveOrderAttributes_FilterDetailsSQL', 
                $attributeSql, 
                array('subject'=>$this,'row'=>$basketRow,'user'=>$pi_paymorrow_user,'order'=>array("id"=>$orderID,"number"=>$orderNumber)));
            Shopware()->Db()->exec($attributeSql);

            // Update sales and stock
            if ($basketRow["priceNumeric"] >= 0 && !$basketRow["esdarticle"]) {
                $sql =  "UPDATE s_articles_details
                    SET sales   = sales + ?,
                        instock = instock - ?
                    WHERE ordernumber = ?";
                Shopware()->Db()->query($sql, array( $basketRow["quantity"], $basketRow["quantity"], $basketRow["ordernumber"]));
            }
            $deactivateInstock = Shopware()->Config()->deactivateNoInStock;
            
            if (!empty($basketRow["laststock"]) && !empty($deactivateInstock) && !empty($basketRow['articleID'])) {
                $sql = 'SELECT MAX(instock) as max_instock FROM s_articles_details WHERE articleID=?';
                $max_instock = Shopware()->Db()->fetchOne($sql, array($basketRow['articleID']));
                $max_instock = (int) $max_instock;
                if ($max_instock <= 0) {
                    $sql = 'UPDATE s_articles SET active=0 WHERE id=?';
                    Shopware()->Db()->query($sql, array($basketRow['articleID']));
                    // Ticket #5517
                    Shopware()->Db()->query("
                        UPDATE s_articles_details SET active = 0 WHERE ordernumber = ?
                        ", array($basketRow['ordernumber'])
                    );
                }
            }

            // For esd-articles, assign serialnumber if needed
            // Check if this article is esd-only (check in variants, too -> later)
            if ($basketRow["esdarticle"]) {
                Shopware()->Modules()->Order()->sManageEsdOrder($basketRow, $orderID, $orderdetailsID);
            } // If article is marked as esd-article
        } // For every artice in basket
        Enlight()->Events()->notify('Shopware_Modules_Order_SaveOrder_ProcessDetails', array(
            'subject' => $this,
            'details' => $pi_paymorrow_basket["content"]
        ));
    }
    
    /**
     * Creates and send Email with bankdata
     *
     * @param String $orderid     Current Order id
     *
     * @return bool
     */
   
    public function myCreateMail($orderid) {
        
        $order = $this->myGetOrders(array("orderID" => $orderid));
        $user = $this->myOrderCustomers(array("orderID" => $orderid));
       
        $ordertime = explode(' ', $order[$orderid]["ordertime"]);
        $piPaymorrowConfig = Shopware()->Plugins()->Frontend()->PiPaymorrowPayment()->Config();
        $date = explode('-', $ordertime[0]);
        $sql = "SELECT * FROM s_order WHERE id = ?";
        $paymorrowOrder = Shopware()->Db()->fetchRow($sql, array($orderid));
        $sql = "SELECT * FROM pi_paymorrow_orders WHERE ordernumber = ?";
        $pi_Paymorrow_bankdata = Shopware()->Db()->fetchRow($sql, array($paymorrowOrder['ordernumber']));
        $sql = "SELECT * FROM s_order_details WHERE orderID = ?";
        $OrderDetails = Shopware()->Db()->fetchAll($sql, array($orderid));
        for ($i = 0; $i < count($OrderDetails); $i++) {
            $articleamount = $OrderDetails[$i]['price'] * $OrderDetails[$i]['quantity'];
            $sql = "SELECT * FROM s_articles_img  WHERE articleID = ? AND main = 1 AND article_detail_id IS NULL";
            $articleimg = Shopware()->Db()->fetchRow($sql, array($OrderDetails[$i]['articleID']));
            $imgString="/".$articleimg['img'] . "_1." . $articleimg['extension'];
            $OrderDetails[$i]['image']['src'][1] ="http://". Shopware()->Config()->Basepath.Shopware()->Config()->Articleimages.$imgString;
            if(htmlentities($OrderDetails[$i]["articlename"])== "Zuschlag f&uuml;r Zahlungsart") unset($OrderDetails[$i]['image']);
            $OrderDetails[$i]["articlename"] = trim(html_entity_decode($OrderDetails[$i]["name"]));
            $OrderDetails[$i]["articlename"] = str_replace(array("<br />","<br>"),"\n",$OrderDetails[$i]["articlename"]);
            $OrderDetails[$i]["articlename"] = str_replace("&euro;","€",$OrderDetails[$i]["articlename"]);
            $OrderDetails[$i]["articlename"] = str_replace("&quot;","\"",$OrderDetails[$i]["articlename"]);
            $OrderDetails[$i]["articlename"] = trim($OrderDetails[$i]["articlename"]);
            while(strpos($OrderDetails[$i]["articlename"],"\n\n")!==false)
            $OrderDetails[$i]["articlename"] = str_replace("\n\n","\n",$OrderDetails[$i]["articlename"]);
            $OrderDetails[$i]["ordernumber"] = trim(html_entity_decode($OrderDetails[$i]["articleordernumber"]));
            $OrderDetails[$i]['price'] = number_format($OrderDetails[$i]['price'], 2, ",", "");
            $OrderDetails[$i]['amount'] = number_format($articleamount, 2, ",", "");
        }
        
        $this->View()->setTemplate();
        $this->View()->sOrderDetails = $OrderDetails;
        if ($paymorrowOrder['dispatchID']) {
            $sql = "SELECT * FROM s_premium_dispatch WHERE id =?";
            $dispatch = Shopware()->Db()->fetchRow($sql, array($paymorrowOrder['dispatchID']));
            $dispatchArray = Array(
                "name" => $dispatch['name'],
                "description" => $dispatch['description'],
            );

            $this->View()->sDispatch = $dispatchArray;
        }
        if ($paymorrowOrder['transactionID']) {
            $variables['sBookingID'] = $paymorrowOrder['transactionID'];
        }
        $sql = "SELECT * FROM s_order_billingaddress  WHERE orderID =?";
        $this->View()->billingaddress = $billingAddress= Shopware()->Db()->fetchRow($sql, array($orderid));
        $sql = "SELECT * FROM s_order_shippingaddress  WHERE orderID =?";
        $this->View()->shippingaddress = $shippingAddress = Shopware()->Db()->fetchRow($sql, array($orderid));
        $sql = "SELECT *
                FROM s_core_paymentmeans
                WHERE id =?";
        $payment = Shopware()->Db()->fetchRow($sql, array($paymorrowOrder['paymentID']));
        $additional = Array(
            "payment" => $payment,
            "countryShipping" => Array("countryname" => htmlentities($user[$orderid]["shipping_country"])),
            "country" => Array("countryname" => htmlentities($user[$orderid]["shipping_country"]))
        );
        
        // View daten in $context array
        $this->View()->additional = $additional;
        $this->View()->sShippingCosts = number_format($paymorrowOrder["invoice_shipping"], 2, ",", "") . ' ' . $paymorrowOrder["currency"];
        $this->View()->sAmount = number_format($paymorrowOrder["invoice_amount"], 2, ",", "") . ' ' . $paymorrowOrder["currency"];
        $this->View()->sAmountNet = number_format($paymorrowOrder["invoice_amount_net"], 2, ",", "") . ' ' . $paymorrowOrder["currency"];
        $this->View()->sOrderNumber = htmlentities($paymorrowOrder["ordernumber"]);
        $this->View()->sOrderDay = $date[2] . '.' . $date[1] . '.' . $date[0];
        $this->View()->sOrderTime = $ordertime[1] . 'Uhr';
        $this->View()->sComment = htmlentities($paymorrowOrder["comment"]);
        $this->View()->sCurrency = htmlentities($paymorrowOrder["currency"]);
        $this->View()->sLanguage = htmlentities($paymorrowOrder["language"]);
        $this->View()->sSubShop = $paymorrowOrder["subshopID"];
        $this->View()->sEsd = $paymorrowOrder["taxfree"];
        $this->View()->sNet = $paymorrowOrder["net"];
        $this->View()->sConfig = Shopware()->Config();
        $paymorrowBankData = Array(
            "nationalBankName" => htmlentities($pi_Paymorrow_bankdata["nationalBankName"]),
            "nationalBankCode" => htmlentities($pi_Paymorrow_bankdata["nationalBankCode"]),
            "nationalBankAccountNumber" => htmlentities($pi_Paymorrow_bankdata["nationalBankAccountNumber"]),
            "paymentReference" => htmlentities($pi_Paymorrow_bankdata["paymentReference"]),
            "paymentReference2" => htmlentities(Shopware()->Config()->Shopname),
            "bic" => htmlentities($pi_Paymorrow_bankdata["bic"]),
            "iban" => htmlentities($pi_Paymorrow_bankdata["iban"])
        );
        $this->View()->paymorrowBankData = $paymorrowBankData;
        
        // add attributes to order
        $sql = 'SELECT * FROM s_order_attributes WHERE orderID = :orderId;';
        $attributes = Shopware()->Db()->fetchRow($sql, array('orderId' => $orderid));
        unset($attributes['id']);
        unset($attributes['orderID']);
        $orderAttributes = $attributes;

        
        $context = array(
            'sOrderDetails' => $OrderDetails,

            'billingaddress'  => $billingAddress,
            'shippingaddress' => $shippingAddress,
            'additional'      => $additional,
            'sShippingCosts' => number_format($paymorrowOrder["invoice_shipping"], 2, ",", "") . ' ' . $paymorrowOrder["currency"],
            'sAmount'        => number_format($paymorrowOrder["invoice_amount"], 2, ",", "") . ' ' . $paymorrowOrder["currency"],
            'sAmountNet'     => number_format($paymorrowOrder["invoice_amount_net"], 2, ",", "") . ' ' . $paymorrowOrder["currency"],

            'sOrderNumber' => htmlentities($paymorrowOrder["ordernumber"]),
            'sOrderDay'    => $date[2] . '.' . $date[1] . '.' . $date[0],
            'sOrderTime'   => $ordertime[1] . 'Uhr',
            'sComment'     => htmlentities($paymorrowOrder["comment"]),

            'attributes'     => $orderAttributes,
            'sCurrency'    => $paymorrowOrder["currency"],

            'sLanguage'    => 'de_DE',

            'sSubShop'     => 1,

            'sEsd'    => $paymorrowOrder["taxfree"],
            'sNet'    => $paymorrowOrder["net"],

        );
        
        $mail = Shopware()->TemplateMail()->createMail('sORDER', $context);

        $attachment = Shopware()->Config()->Templates->sORDER->attachment;
        if (!empty($attachment)) {
            $attachments = explode("/", $attachment);
            if (empty($attachments[0])) {
                $attachments[0] = $attachment;
            }
            foreach ($attachments as $attachment) {
                $file = explode(";", $attachment);
                $path = Shopware()->Config()->Basepath;
                $path = str_replace(Shopware()->Config()->Host, "", $path);
                $path = $_SERVER['DOCUMENT_ROOT'] . $path . "/uploads/" . $file[0];
                if (is_file($path)) {
                    $mail->addAttachment($path, $file[1]);
                }
            }
        }
//        $mail->ClearAddresses(); // Vorherige Adressen entfernen
//        $mail->AddAddress($user[$orderid]['email'], '');
//        if (Shopware()->Config()->NO_ORDER_MAIL == '0') $mail->AddBCC(Shopware()->Config()->Mail, "");
        $mail->addTo($user[$orderid]['email']);
        if (Shopware()->Config()->NO_ORDER_MAIL == '0') $mail->AddBCC(Shopware()->Config()->Mail, "");
        $mail->Send();

        return true;
    }


    /**
     * Formats Price
     *
     * @param Float   $price     Current price
     *
     * @return Float
     */
    public function myFormatPrice($price) {
        $price = str_replace(",", ".", $price);
        $price = $this->myRound($price);
        $price = str_replace(".", ",", $price); // Replaces points with commas
        $commaPos = strpos($price, ",");
        if ($commaPos) {

            $part = substr($price, $commaPos + 1, strlen($price) - $commaPos);
            switch (strlen($part)) {
                case 1:
                    $price .= "0";
                    break;
                case 2:
                    break;
            }
        }
        else {
            if (!$price) {
                $price = "0";
            }
            else {
                $price .= ",00";
            }
        }

        return $price;
    }

    /**
     * Formats Text
     *
     * @param Float   $price     Current price
     *
     * @return Float
     */
    public function myOptimizeText($text) {

        $text = html_entity_decode($text);
        $text = preg_replace('!<[^>]*?>!', ' ', $text);
        $text = str_replace(chr(0xa0), " ", $text);
        $text = preg_replace('/\s\s+/', ' ', $text);
        $text = htmlspecialchars($text, ENT_COMPAT, 'ISO-8859-1', false);
        $text = trim($text);

        return $text;
    }

    /**
     * Rounds Price
     *
     * @param Float   $price     Current price
     *
     * @return Float
     */
    public function myRound($moneyfloat = null) {

        $money_str = explode(".", $moneyfloat);
        if (empty($money_str[1])) $money_str[1] = 0;
        $money_str[1] = substr($money_str[1], 0, 3); // convert to rounded (to the nearest thousandth) string

        $money_str = $money_str[0] . "." . $money_str[1];

        return round($money_str, 2);
    }
    /**
     * Auslesen von Bestellungen (ein oder mehrere)
     * @param array $order
     *   - [orderID] ID der Bestellung oder
     *   - [orderIDs] mehre IDs [orderIDs] = array (x,y,z)
     *   - [where] eine SQL-Bedingung z.b. "status=0 OR status=1"
     * @access public
     * @return array|bool Gibt bei Erfolg die Bestellungen zurück
     */
    function myGetOrders ($order = null)
    {
        if(!empty($order['orderID']))
            $order['orderIDs'] = array($order['orderID']);
        if(!empty($order['orderIDs'])&&is_array($order['orderIDs']))
        {
            $order['where'] = '`o`.`id` IN ('.implode(',',$order['orderIDs']).')';
        }
        if(empty($order['where'])) 
        {
            $sql_where = '';
        }
        else 
        {
            $sql_where = 'WHERE '.$order['where'];
        }
        if (!empty(Shopware()->Config()->premiumShipping))
        {
            $dispatch_table = 's_premium_dispatch';
        }
        else
        {
            $dispatch_table = 's_premium_dispatch';
        }
        $sql = "
            SELECT
                `o`.`id`,
                `o`.`id` as `orderID`,
                `o`.`ordernumber`,
                `o`.`ordernumber` as `order_number`, 
                `o`.`userID`,
                `o`.`userID` as `customerID`,
                `o`.`invoice_amount`, 
                `o`.`invoice_amount_net`, 
                `o`.`invoice_shipping`, 
                `o`.`invoice_shipping_net`, 
                `o`.`ordertime` as `ordertime`, 
                `o`.`status`,
                `o`.`status` as `statusID`,
                `o`.`cleared` as `cleared`,
                `o`.`cleared` as `clearedID`,
                `o`.`paymentID` as `paymentID`, 
                `o`.`transactionID` as `transactionID`, 
                `o`.`comment`, 
                `o`.`customercomment`, 
                `o`.`net`,
                `o`.`net` as `netto`,
                `o`.`partnerID`, 
                `o`.`temporaryID`,
                `o`.`referer`, 
                o.cleareddate,
                o.cleareddate as cleared_date,
                o.trackingcode,
                o.language,
                o.currency,
                o.currencyFactor,
                o.subshopID,
                o.dispatchID,
                cu.id as currencyID,
                `c`.`description` as `cleared_description`, 
                `s`.`description` as `status_description`,
                `p`.`description` as `payment_description`,
                `d`.`name` 		  as `dispatch_description`,
                `cu`.`name` 	  as `currency_description`
            FROM 
                `s_order` as `o`
            LEFT JOIN `s_core_states` as `s`
            ON	(`o`.`status` = `s`.`id`)
            LEFT JOIN `s_core_states` as `c`
            ON	(`o`.`cleared` = `c`.`id`)
            LEFT JOIN `s_core_paymentmeans` as `p`
            ON	(`o`.`paymentID` = `p`.`id`)
            LEFT JOIN `$dispatch_table` as `d`
            ON	(`o`.`dispatchID` = `d`.`id`)
            LEFT JOIN `s_core_currencies` as `cu`
            ON	(`o`.`currency` = `cu`.`currency`)
            $sql_where
        ";
        
        $rows = Shopware()->Db()->fetchAssoc($sql);
        if(empty($rows)||!is_array($rows)||!count($rows))
            return false;
        foreach ($rows as $row)
            $orders[intval($row['orderID'])] = $row;
        return $orders;
    }
    /**
     * Gibt die Kundendaten für angegebene Bestellung(en) zurück
     * @param array $order
     *   - [orderID] ID der Bestellung oder
     *   - [orderIDs] mehrere IDs in Array-Form
     * @access public
     * @return array|bool Gibt bei Erfolg die Kundendaten und bei einem Fehler false zurück
     */
    function myOrderCustomers ($order)
    {
        if (empty($order['orderIDs'])||!is_array($order['orderIDs']))
            $order['orderIDs'] = array();
        if(!empty($order['orderID']))
        {
            $order['orderIDs'][] = $order['orderID'];
        }
        $order['orderIDs'] = array_map("intval",$order['orderIDs']);

        if(!count($order['orderIDs']))
            return false;
            
        $where = "`b`.`orderID`=".implode(" OR `b`.`orderID`=",$order['orderIDs'])."\n";
        $sql = "
            SELECT
                `b`.`company` AS `billing_company`,
                `b`.`department` AS `billing_department`,
                `b`.`salutation` AS `billing_salutation`,
                `ub`.`customernumber`,
                `b`.`firstname` AS `billing_firstname`,
                `b`.`lastname` AS `billing_lastname`,
                `b`.`street` AS `billing_street`,
                `b`.`streetnumber` AS `billing_streetnumber`,
                `b`.`zipcode` AS `billing_zipcode`,
                `b`.`city` AS `billing_city`,
                `b`.`phone` AS `phone`,
                `b`.`phone` AS `billing_phone`,
                `b`.`fax` AS `fax`, 
                `b`.`fax` AS `billing_fax`, 
                `b`.`countryID` AS `billing_countryID`, 
                `bc`.`countryname` AS `billing_country`,
                `bc`.`countryiso` AS `billing_countryiso`,
                `bca`.`name` AS `billing_countryarea`,
                `bc`.`countryen` AS `billing_countryen`,
                `b`.`ustid`,
                `ba`.`text1` AS `billing_text1`,
                `ba`.`text2` AS `billing_text2`,
                `ba`.`text3` AS `billing_text3`,
                `ba`.`text4` AS `billing_text4`,
                `ba`.`text5` AS `billing_text5`,
                `ba`.`text6` AS `billing_text6`,
                `b`.`orderID` as `orderID`,
                `s`.`company` AS `shipping_company`,
                `s`.`department` AS `shipping_department`,
                `s`.`salutation` AS `shipping_salutation`,
                `s`.`firstname` AS `shipping_firstname`,
                `s`.`lastname` AS `shipping_lastname`,
                `s`.`street` AS `shipping_street`,
                `s`.`streetnumber` AS `shipping_streetnumber`,
                `s`.`zipcode` AS `shipping_zipcode`,
                `s`.`city` AS `shipping_city`,
                `s`.`countryID` AS `shipping_countryID`,
                `sc`.`countryname` AS `shipping_country`,
                `sc`.`countryiso` AS `shipping_countryiso`,
                `sca`.`name` AS `shipping_countryarea`,
                `sc`.`countryen` AS `shipping_countryen`,
                `sa`.`text1` AS `shipping_text1`,
                `sa`.`text2` AS `shipping_text2`,
                `sa`.`text3` AS `shipping_text3`,
                `sa`.`text4` AS `shipping_text4`,
                `sa`.`text5` AS `shipping_text5`,
                `sa`.`text6` AS `shipping_text6`,
                `u`.*,
                ub.birthday,
                `g`.`id` AS `preisgruppe`,
                `g`.`tax` AS `billing_net`
            FROM
                `s_order_billingaddress` as `b`
            LEFT JOIN `s_order_shippingaddress` as `s`
                ON `s`.`orderID` = `b`.`orderID`
            LEFT JOIN `s_user_billingaddress` as `ub`
                ON `ub`.`userID` = `b`.`userID`
            LEFT JOIN `s_user` as `u`
                ON `b`.`userID` = `u`.`id`
            LEFT JOIN `s_core_countries` as `bc`
                ON `bc`.`id` = `b`.`countryID`
            LEFT JOIN `s_core_countries` as `sc`
                ON `sc`.`id` = `s`.`countryID`
            LEFT JOIN `s_core_customergroups` as `g`
                ON `u`.`customergroup` = `g`.`groupkey`
            LEFT JOIN s_core_countries_areas bca
                ON bc.areaID = bca.id
            LEFT JOIN s_core_countries_areas sca
                ON sc.areaID = sca.id
            LEFT JOIN s_order_billingaddress_attributes ba
                ON b.id = ba.billingID
            LEFT JOIN s_order_shippingaddress_attributes sa
                ON s.id = sa.shippingID
            WHERE
                $where
        ";  // Fix #5830 backported from github
        
        $rows = Shopware()->Db()->fetchAssoc($sql);
        if(empty($rows)||!is_array($rows)||!count($rows))
            return false;
        $customers = array();
        foreach ($rows as $row)
            $customers[intval($row['orderID'])] = $row;
        return 	$customers;	
    }

}
