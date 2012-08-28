<?php

/**
 * RatePAY Payment Module
 *
 * @author       PayIntelligent GmbH  <http://www.payintelligent.de/>
 * @package      PiPaymentRatepay
 * @copyright(C) 2011 RatePAY GmbH. All rights reserved. <http://www.ratepay.com/>
 */
class Shopware_Controllers_Frontend_RatepayPayment extends Shopware_Controllers_Frontend_Payment
{

    private $ordernumber;
    private $orderId;

    /**
     * Index action method
     */
    public function indexAction()
    {
        if ($this->getPaymentShortName() == 'RatePAYInvoice'
            || $this->getPaymentShortName() == 'RatePAYRate'
            || $this->getPaymentShortName() == 'RatePAYDebit'
        ) {
            $user = $this->getUser();
            $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
            if (!initPayment($config, $user)) {
                $sql = "UPDATE `s_user` SET `paymentID` = ? WHERE `id` = ?";
                Shopware()->Db()->query($sql, array((int)Shopware()->Config()->Defaultpayment, (int)$user['billingaddress']['userID']));
                Shopware()->Session()->pi_ratepay_no_ratepay = true;
                return $this->redirect(array('controller' => 'account', 'action' => 'payment', 'sTarget' => 'checkout', 'forceSecure' => true));
            }
            return $this->redirect(array('action' => 'RatepayRequest', 'forceSecure' => true));
        }
        else {
            return $this->redirect(array('controller' => 'checkout', 'forceSecure' => true));
        }
    }
    /**
     * RatePAY action method handles payment request
     */
    public function RatepayRequestAction()
    {
        include_once dirname(__FILE__) . '/../../Views/Frontend/Ratenrechner/php/pi_ratepay_xml_service.php';
        Shopware()->Session()->pi_ratepay_Confirm = false;
        $user = $this->getUser();
        $payName = $this->getPaymentShortName();
        $ratepay = new pi_ratepay_xml_service();
        $ratepay->live = checkSandboxMode($payName);
        $request = $ratepay->getXMLObject();
        setRatepayHead($request, 'PAYMENT_REQUEST', $user);
        if ($payName == 'RatePAYInvoice') $content = setRatepayContent($request, 'rechnung');
        elseif ($payName == 'RatePAYDebit') $content = setRatepayContent($request, 'directDebit');
        else $content = setRatepayContent($request, 'ratenzahlung');
        $customer = $user['billingaddress']['firstname'] . ' ' . $user['billingaddress']['lastname'];
        $response = $ratepay->paymentOperation($request);
        if($payName == 'RatePAYDebit' ||($payName == 'RatePAYRate' && Shopware()->Session()->RatepayDirectDebit))
                $request = checkBankDataSave($request, $user);
        writeLog("", Shopware()->Session()->pi_ratepay_transactionID, "PAYMENT_REQUEST", "", $request, $response, $customer, $payName);
        if ($response && (string) $response->head->processing->status->attributes()->code == "OK"
            && (string) $response->head->processing->result->attributes()->code == "402"
        ) {
            Shopware()->Session()->pi_ratepay_rechnung_descriptor = (string) $response->content->payment->descriptor;
            return $this->forward('end');
        } else {
            Shopware()->Session()->pi_ratepay_no_ratepay = true;
            $sql = "SELECT `userID` FROM `s_user_billingaddress` WHERE `id` = ?";
            $userID = Shopware()->Db()->fetchOne($sql, array((int)$user['billingaddress']['userID']));
            $sql = "UPDATE `s_user` SET `paymentID` = ? WHERE `id` = ?";
            Shopware()->Db()->query($sql, array((int)Shopware()->Config()->Defaultpayment, (int)$userID));
            $this->saveStats(false);
            return $this->redirect(array('controller' => 'account', 'action' => 'payment', 'sTarget' => 'checkout', 'forceSecure' => true));
        }
    }

    /**
     * end action method handles payment confirm and saves order
     */
    public function endAction()
    {
        $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
        $secret = $this->getPaymentShortName() == 'RatePAYInvoice'?  $config->security_code: $config->security_code_rate;
        $transactionId = Shopware()->Session()->pi_ratepay_transactionID;
        $hash = $secret . $transactionId;
        $this->saveOrder($transactionId, $hash);
        $sql = "SELECT `ordernumber` FROM `s_order` WHERE `transactionID` = ?";
        $this->ordernumber = Shopware()->Db()->fetchOne($sql, array($transactionId));
        $sql = "SELECT `id` FROM `s_order` WHERE `transactionID` = ?";
        $this->orderId = Shopware()->Db()->fetchOne($sql, array($transactionId));
        Shopware()->Session()->pi_ratepay_ordernumber = $this->ordernumber;
        if ($this->getPaymentShortName() == 'RatePAYRate') $this->saveRateDetails();
        historyEntry($this->ordernumber, '<b class=\"green\">Bestellung ist eingegangen</b>', '', '', '');
        $this->saveRatepayDetails();
        $this->saveStats(true);
        $this->saveOrderDetails();
        $this->saveShippingCosts();
        $sql = "UPDATE `s_order` SET `cleared` = ? WHERE `ordernumber` = ?";
        Shopware()->Db()->query($sql, array((int)getAcceptedStatusId(), $this->ordernumber));
        $this->redirect(array('controller' => 'checkout', 'action' => 'finish', 'sUniqueID' => $hash, 'forceSecure' => true));
    }

    /**
     * Saves installment details in db
     *
     */
    public function saveRateDetails()
    {
        $sql = "INSERT INTO `pi_ratepay_rate_details`
                (
                    `ordernumber`,
                    `total_amount`,
                    `amount`,
                    `interest_amount`,
                    `service_charge`,
                    `annual_percentage_rate`,
                    `monthly_debit_interest`,
                    `number_of_rates`,
                    `rate`,
                    `last_rate`
                )
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        Shopware()->Db()->query($sql, array(
            $this->ordernumber,
            (double)Shopware()->Session()->pi_ratepay_total_amount,
            (double)Shopware()->Session()->pi_ratepay_amount,
            Shopware()->Session()->pi_ratepay_interest_amount,
            (double)Shopware()->Session()->pi_ratepay_service_charge,
            Shopware()->Session()->pi_ratepay_annual_percentage_rate,
            Shopware()->Session()->pi_ratepay_monthly_debit_interest,
            (int)Shopware()->Session()->pi_ratepay_number_of_rates,
            (double)Shopware()->Session()->pi_ratepay_rate,
            (double)Shopware()->Session()->pi_ratepay_last_rate
        ));
    }

     /**
     * Saves Ratepay details in db
     *
     */
    public function saveRatepayDetails()
    {
        $user = array();
        $user = $this->getUser();
        $sql = "INSERT INTO `pi_ratepay_orders`
                (
                    `payment_id`,
                    `payment_name`,
                    `order_number`,
                    `transactionid`,
                    `transaction_short_id`,
                    `descriptor`,
                    `userbirthdate`
                )
                VALUES(?, ?, ?, ?, ?, ?, ?)";
        Shopware()->Db()->query($sql, array(
            (int)$user["additional"]["payment"]["id"],
            $user["additional"]["payment"]["name"],
            $this->ordernumber,
            Shopware()->Session()->pi_ratepay_transactionID,
            Shopware()->Session()->pi_ratepay_transactionShortID,
            Shopware()->Session()->pi_ratepay_rechnung_descriptor,
            $user["billingaddress"]["birthday"]
        ));
    }

    /**
     * Saves if order was accepted or not in db for stats
     *
     * @param boolean $accepted     order state
     *
     */
    public function saveStats($accepted)
    {
        $valueOne = $accepted ?  "1": "0";
        $valueTwo = $accepted ?  "0": "1";
        $sql = "INSERT INTO `pi_ratepay_stats` (`accepted`, `notaccepted`) VALUES (?, ?)";
        Shopware()->Db()->query($sql, array($valueOne, $valueTwo));
    }

     /**
     * Saves order details details in db
     *
     * @param boolean $accepted     order state
     *
     */
    public function saveOrderDetails()
    {
        $basket = $this->getBasket();
        for ($i = 0; $i < count($basket["content"]); $i++) {
            $articlePrice = $basket["content"][$i]["priceNumeric"];
            $articleName = html_entity_decode($basket["content"][$i]["articlename"]);
            $articleName = str_replace("'", "\'", $articleName);
            $articleName = str_replace("`", "\'", $articleName);
            $articleName = str_replace("´", "\'", $articleName);
            $totalPrice = $articlePrice * $basket["content"][$i]["quantity"];
            $sql = "INSERT INTO `pi_ratepay_order_detail`
                    (
                        `ordernumber` ,
                        `artikel_id` ,
                        `bestell_nr`,
                        `anzahl` ,
                        `name` ,
                        `einzelpreis` ,
                        `gesamtpreis` ,
                        `bestellt`,
                        `offen`,
                        `bezahlstatus`,
                        `einzelpreis_net`
                    )
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
           Shopware()->Db()->query($sql, array(
                $this->ordernumber,
                $basket["content"][$i]["articleID"],
                $basket["content"][$i]["ordernumber"],
                (int)$basket["content"][$i]["quantity"],
                $articleName,
                (double)$articlePrice,
                (double)$totalPrice,
                (int)$basket["content"][$i]["quantity"],
                (int)$basket["content"][$i]["quantity"],
                (int)18,
                (double)$basket["content"][$i]["netprice"]
            ));
        }
    }



     /**
     * Saves shipping costs in db
     */
    public function saveShippingCosts()
    {
        $basket = $this->getBasket();
        if ($basket['sShippingcostsTax'] != 0) {
            $sql = "SELECT `invoice_shipping` FROM `s_order` WHERE `ordernumber` = ?";
            $shippingPrice = Shopware()->Db()->fetchOne($sql, array($this->ordernumber));
            $sql = "INSERT INTO `pi_ratepay_order_detail`
                    (
                        `ordernumber` ,
                        `artikel_id` ,
                        `bestell_nr`,
                        `anzahl` ,
                        `name` ,
                        `einzelpreis` ,
                        `gesamtpreis` ,
                        `bestellt`,
                        `offen`,
                        `bezahlstatus`,
                        `einzelpreis_net`
                    )
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            Shopware()->Db()->query($sql, array(
                $this->ordernumber,
                $this->ordernumber.'666',
                'versand',
                (int)1,
                'Versandkosten',
                (double)$shippingPrice,
                (double)$shippingPrice,
                (int)1,
                (int)1,
                (int)18,
                $basket["sShippingcostsNet"]
            ));
        }
    }

    /**
     * Returns the full user data as array
     *
     * @return array
     */
    public function getUser()
    {
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
    public function getBasket()
    {
        if (!empty(Shopware()->Session()->sOrderVariables['sBasket'])) {
            return Shopware()->Session()->sOrderVariables['sBasket'];
        }
        else {
            return null;
        }
    }

    public function calcDesignAction() {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $calcPath = realpath(dirname(__FILE__) . '/../../Views/Frontend/Ratenrechner/php/');
        require_once $calcPath.'/PiRatepayRateCalc.php';
        require_once $calcPath.'/path.php';
        require_once $calcPath.'/PiRatepayRateCalcDesign.php';
    }

    public function calcRequestAction() {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $calcPath = realpath(dirname(__FILE__) . '/../../Views/Frontend/Ratenrechner/php/');
        require_once $calcPath.'/PiRatepayRateCalc.php';
        require_once $calcPath.'/path.php';
        require_once $calcPath.'/PiRatepayRateCalcRequest.php';
    }

}