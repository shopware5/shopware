<?php

/**
 * get Surcharge from current payment method
 *
 * @params  String $payment     Payment method
 * @params  String $basket      Current basket
 *
 * @return  float  $surcharge   Surcharge for payment method
 */
function getSurcharge($payment,$basket) {

    $sql = "SELECT `surcharge` FROM `s_core_paymentmeans` WHERE `name` = ?";
    $surcharge = Shopware()->Db()->fetchOne($sql, array("RatePAY". $payment));

    $sql = "SELECT `debit_percent` FROM `s_core_paymentmeans` WHERE `name` = ?";
    $surchargePercent = Shopware()->Db()->fetchOne($sql, array("RatePAY". $payment));

    if($surchargePercent != 0 && $surcharge == 0){
        return $surchargePercent.'%';
        $surcharge += $surchargePercent;
    } else if($surchargePercent !=0 && $surcharge != 0){
        return number_format($surcharge,2, ',', '.') . '&euro;&nbsp;+' . $surchargePercent.'%';
        $surcharge += $surchargePercent;
    } else if($surcharge && $surcharge != 0){
        return number_format($surcharge,2, ',', '.') . '&euro;';
    }else{
        return false;
    }
}


/**
 * Saves userdata entered in payment fieldset
 *
 * @param   Enlight_Event_EventArgs     $args            Arguments
 * @param   Array                       $userBirthday    Userbirthday
 */
function saveUserData(Enlight_Event_EventArgs $args) {
    $userData = array();
    $post = $args->getSubject()->Request()->getPost();
    $userData = $args->getSubject()->View()->sUserData;
    $paymentId = 0;
    if ($post['saveRatepayRateData']) {
        saveUserDataInDB($args, 'rate');
        $paymentId = getRatePaymentId();
    }elseif($post['saveRatepayDebitData']){
        saveUserDataInDB($args, 'debit');
        $paymentId = getDebitPaymentId();
    }else{
        saveUserDataInDB($args, 'invoice');
        $paymentId = getInvoicePaymentId();
    }
    $sql = "SELECT `birthday`, `phone`, `company`, `ustid` FROM `s_user_billingaddress` WHERE `id` = ?";
    $newData = Shopware()->Db()->fetchRow($sql, array((int)$userData['billingaddress']['id']));
    if (($newData['company'] && !$newData['ustid']) || (!$newData['phone'] && $newData['ustid'])) $companyFlag = true;
    if ($newData['birthday'] != "0000-00-00" && $newData['birthday'] && !$companyFlag && !$args->getSubject()->View()->pi_ratepay_toyoung) {
        $sql = "UPDATE `s_user` SET `paymentID` = ? WHERE `id` = ?";
        Shopware()->Db()->query($sql, array((int)$post['register']['payment'],(int)$userData['billingaddress']['userID']));
    }
}

/**
 * Saves data in DBt
 *
 * @param   Enlight_Event_EventArgs     $args            Arguments
 * @param   Array                       $userBirthday    Userbirthday
 */
function saveUserDataInDB($args, $paymentMethod) {
    $post = array();
    $userData = array();
    $post = $args->getSubject()->Request()->getPost();
    $userData = $args->getSubject()->View()->sUserData;
    if ($post['registerRatePAY']['personal']['ustid_'.$paymentMethod]) {
        $sql = "UPDATE `s_user_billingaddress` SET `ustid` = ? WHERE `id`= ?";
        Shopware()->Db()->query($sql, array(
            $post['registerRatePAY']['personal']['ustid_'.$paymentMethod],
            (int)$userData['billingaddress']['id']
        ));
    }
    if ($post['registerRatePAY']['personal']['phone_'.$paymentMethod]) {
        $sql = "UPDATE `s_user_billingaddress` SET `phone` = ? WHERE `id` = ?";
        Shopware()->Db()->query($sql, array(
            $post['registerRatePAY']['personal']['phone_'.$paymentMethod],
            (int)$userData['billingaddress']['id']
        ));
    }
    if ($post['registerRatePAY']['personal']['company_'.$paymentMethod]) {
        $sql = "UPDATE `s_user_billingaddress` SET `company` = ? WHERE `id` = ?";
        Shopware()->Db()->query($sql, array(
            $post['registerRatePAY']['personal']['company_'.$paymentMethod],
            (int)$userData['billingaddress']['id']
        ));
        $sql = "UPDATE `s_user_shippingaddress` SET `company` = ? WHERE `id` = ?";
        Shopware()->Db()->query($sql, array(
            $post['registerRatePAY']['personal']['company_'.$paymentMethod],
            (int)$userData['shippingaddress']['id']
        ));
    }
    $birthday = $post['registerRatePAY']['personal']['birthyear_'.$paymentMethod] . "-"
              . $post['registerRatePAY']['personal']['birthmonth_'.$paymentMethod] . "-"
              . $post['registerRatePAY']['personal']['birthday_'.$paymentMethod];
    if ($userData["billingaddress"]["birthday"] == "0000-00-00" || !$userData["billingaddress"]["birthday"] == "0000-00-00") {
        $sql = "UPDATE `s_user_billingaddress` SET `birthday` = ? WHERE `id` = ?";
        Shopware()->Db()->query($sql, array($birthday, (int)$userData['billingaddress']['id']));
        $args->getSubject()->View()->pi_ratepay_birthdayflag = true;
    }
}

/**
 * Saves Debit entered in payment fieldset
 *
 * @param   Enlight_Event_EventArgs     $args            Arguments
 */
function saveDebitData(Enlight_Event_EventArgs $args) {
    require_once  dirname(__FILE__) . '/../Encryption/PiEncryption.php';
    $piEncryption= new Encryption_PiEncryption();
    $sql = "SELECT `key` FROM `pi_ratepay_private_key` WHERE `id` = 1";
    $key = $piEncryption->getDecodedString(Shopware()->Db()->fetchOne($sql));
    $post = $args->getSubject()->Request()->getPost();
    $userId = $args->getSubject()->View()->sUserData['billingaddress']['userID'];
    $sql = "SELECT * FROM pi_ratepay_debit_details WHERE userid = ?";
    $check = Shopware()->Db()->fetchRow($sql, array((int)$userId,));
    if(!empty($post['ratepayRateDebit']['owner']) && $post['ratepayRateDebit']['owner'] != $check['owner']){
        $post['ratepayDebit']['owner']=$post['ratepayRateDebit']['owner'];
    }
    if(!empty($post['ratepayRateDebit']['accountnumber']) && $post['ratepayRateDebit']['accountnumber'] != $check['accountnumber']){
        $post['ratepayDebit']['accountnumber']=$post['ratepayRateDebit']['accountnumber'];
    }
    if(!empty($post['ratepayRateDebit']['bankcode']) && $post['ratepayRateDebit']['bankcode'] != $check['bankcode']){
        $post['ratepayDebit']['bankcode']=$post['ratepayRateDebit']['bankcode'];
    }
    if(!empty($post['ratepayRateDebit']['bankname']) && $post['ratepayRateDebit']['bankname'] != $check['bankname']){
        $post['ratepayDebit']['bankname']=$post['ratepayRateDebit']['bankname'];
    }
    $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
    if(($config->bankdata_debit && (isset($post['saveRatepayBankData']) || $post['register']['payment'] == getDebitPaymentId())
        || ($config->bankdata_rate && (isset($post['saveDebitData']) || $post['register']['payment']== getRatePaymentId())))
    ){
        if(!$check){

            $sql = "INSERT INTO `pi_ratepay_debit_details`
                       (`userid`, `owner`, `accountnumber`, `bankcode`, `bankname`)
                    VALUES
                    (
                        ?,
                        AES_ENCRYPT(?, ?),
                        AES_ENCRYPT(?, ?),
                        AES_ENCRYPT(?, ?),
                        AES_ENCRYPT(?, ?)
                    )";

            Shopware()->Db()->query($sql, array(
                (int)$userId,
                $piEncryption->getEncodedString($post['ratepayDebit']['owner']), $key,
                $piEncryption->getEncodedString($post['ratepayDebit']['accountnumber']), $key,
                $piEncryption->getEncodedString($post['ratepayDebit']['bankcode']), $key,
                $piEncryption->getEncodedString($post['ratepayDebit']['bankname']), $key
            ));
        }
        else{
            $sql = "UPDATE `pi_ratepay_debit_details`
                    SET
                        `owner` =  AES_ENCRYPT(?, ?),
                        `accountnumber` = AES_ENCRYPT(?, ?),
                        `bankcode` = AES_ENCRYPT(?, ?),
                        `bankname` = AES_ENCRYPT(?, ?)
                    WHERE `userid` = ?";
            Shopware()->Db()->query($sql, array(
                $piEncryption->getEncodedString($post['ratepayDebit']['owner']), $key,
                $piEncryption->getEncodedString($post['ratepayDebit']['accountnumber']), $key,
                $piEncryption->getEncodedString($post['ratepayDebit']['bankcode']), $key,
                $piEncryption->getEncodedString($post['ratepayDebit']['bankname']), $key,
               (int)$userId
            ));
        }
        $sql = "SELECT * FROM `pi_ratepay_debit_details` WHERE `userid` = ?";
        $newData= Shopware()->Db()->fetchRow($sql, array((int)$userId));
        if($newData['owner'] && $newData['accountnumber'] && $newData['bankcode'] && $newData['bankname']){
            $sql = "UPDATE `s_user` SET `paymentID` = ? WHERE `id` = ?";
            if(isset($post['saveRatepayBankData'])) {
                 Shopware()->Db()->query($sql, array((int)getDebitPaymentId(), (int)$userId));
            }
            elseif(isset($post['saveDebitData'])){
                 Shopware()->Db()->query($sql, array((int)getRatePaymentId(), (int)$userId));
            }
            $args->getSubject()->View()->debitDetailsSaved = true;
        }
    }
    else{
         Shopware()->Session()->RatepayOwner = $post['ratepayDebit']['owner'];
         Shopware()->Session()->RatepayAccountnumber = $post['ratepayDebit']['accountnumber'];
         Shopware()->Session()->RatepayBankcode = $post['ratepayDebit']['bankcode'];
         Shopware()->Session()->RatepayBankname = $post['ratepayDebit']['bankname'];
         $sql = "UPDATE `s_user` SET `paymentID` = ? WHERE `id` = ?";
         if(isset($post['saveRatepayBankData']))
            Shopware()->Db()->query($sql, array((int)getDebitPaymentId(), (int)$userId));
         elseif(isset($post['saveDebitData']))
            Shopware()->Db()->query($sql, array((int)getRatePaymentId(), (int)$userId));
         $args->getSubject()->View()->debitDetailsSaved = true;
    }
}

/**
 * checks userdata and sets payment warnings
 *
 * @return   Array   $debitData    debit data
 */
function getDebitData()
{
    $debitData = array();
    $debitData['owner'] = Shopware()->Session()->RatepayOwner;
    $debitData['accountnumber'] =  Shopware()->Session()->RatepayAccountnumber;
    $debitData['bankcode'] =  Shopware()->Session()->RatepayBankcode;
    $debitData['bankname'] =  Shopware()->Session()->RatepayBankname;
    return $debitData;
}

function getEncodedDebitData($userId)
{

    require_once  dirname(__FILE__) . '/../Encryption/PiEncryption.php';
    $piEncryption= new Encryption_PiEncryption();
    $debitData = array();
    $sql = "SELECT `key` FROM `pi_ratepay_private_key` WHERE `id` = 1";
    $key = $piEncryption->getDecodedString(Shopware()->Db()->fetchOne($sql));
    $sql = "SELECT AES_DECRYPT(`owner`, ?),AES_DECRYPT(`accountnumber`, ?) ,AES_DECRYPT(`bankcode`, ?) ,AES_DECRYPT(`bankname`,?)
            FROM `pi_ratepay_debit_details` WHERE `userid` = ?";
    $debitRawData = Shopware()->Db()->fetchRow($sql, array($key, $key, $key, $key, (int)$userId));
    $encodedData = array();
    if(empty($debitRawData)){
         $debitData['owner'] = Shopware()->Session()->RatepayOwner;
         $debitData['accountnumber'] = Shopware()->Session()->RatepayAccountnumber;
         $debitData['bankcode'] = Shopware()->Session()->RatepayBankcode;
         $debitData['bankname'] = Shopware()->Session()->RatepayBankname;
    }
    else{
        $index=0;
        foreach($debitRawData as $key => $data){
            $encodedData[$index] = $piEncryption->getDecodedString($data);
            $index++;
        }
        $debitData['owner'] = $encodedData[0];
        $debitData['accountnumber'] =  $encodedData[1];
        $debitData['bankcode'] =  $encodedData[2];
        $debitData['bankname'] =  $encodedData[3];
    }
    return $debitData;

}

/**
 * checks userdata and sets payment warnings
 *
 * @param   Array   $userData    Userdata
 * @param   Array   $view        Current View
 * @param   Int     $userAge     Userage
 */
function checkUserData($userData, $view, $userAge) {
    $sql = "SELECT `ustid` FROM `s_user_billingaddress` WHERE `id` = ?";
    $newUstid = Shopware()->Db()->fetchOne($sql, array((int)$userData['billingaddress']['id']));
    if ($userData["billingaddress"]["birthday"] == "0000-00-00") {
        if (!$userData["billingaddress"]["phone"]) {
            if (!$userData['billingaddress']['company'] && $newUstid) {
                $view->piRatepayInvoiceWarning = 'all_company';
                $view->piRatepayRateWarning = 'all_company';
                $view->piRatepayDebitWarning = 'all_company';
            }
            elseif ($userData['billingaddress']['company'] && !$newUstid) {
                $view->piRatepayInvoiceWarning = 'all_ustid';
                $view->piRatepayRateWarning = 'all_ustid';
                $view->piRatepayDebitWarning = 'all_ustid';
            }
            elseif ($userData['billingaddress']['company'] && $newUstid) {
                $view->piRatepayInvoiceWarning = 'both';
                $view->piRatepayRateWarning = 'both';
                $view->piRatepayDebitWarning = 'both';
            }
            elseif (!$userData['billingaddress']['company'] && !$newUstid) {
                $view->piRatepayInvoiceWarning = 'both';
                $view->piRatepayRateWarning = 'both';
                $view->piRatepayDebitWarning = 'both';
            }
        }
        else {
            if (!$userData['billingaddress']['company'] && $newUstid) {
                $view->piRatepayInvoiceWarning = 'birthday_company';
                $view->piRatepayRateWarning = 'birthday_company';
                $view->piRatepayDebitWarning = 'birthday_company';
            }
            elseif ($userData['billingaddress']['company'] && !$newUstid) {
                $view->piRatepayInvoiceWarning = 'birthday_ustid';
                $view->piRatepayRateWarning = 'birthday_ustid';
                $view->piRatepayDebitWarning = 'birthday_ustid';
            }
            elseif ($userData['billingaddress']['company'] && $newUstid) {
                $view->piRatepayInvoiceWarning = 'birthday';
                $view->piRatepayRateWarning = 'birthday';
                $view->piRatepayDebitWarning = 'birthday';
            }
            elseif (!$userData['billingaddress']['company'] && !$newUstid) {
                $view->piRatepayInvoiceWarning = 'birthday';
                $view->piRatepayRateWarning = 'birthday';
                $view->piRatepayDebitWarning = 'birthday';
            }
        }
    }
    else {
        if (!$userData["billingaddress"]["phone"]) {
            if ($userData['billingaddress']['company'] && !$newUstid) {
                $view->piRatepayInvoiceWarning = 'phone_ustid';
                $view->piRatepayRateWarning = 'phone_ustid';
                $view->piRatepayDebitWarning = 'phone_ustid';
            }
            elseif (!$userData['billingaddress']['company'] && $newUstid) {
                $view->piRatepayInvoiceWarning = 'phone_company';
                $view->piRatepayRateWarning = 'phone_company';
                $view->piRatepayDebitWarning = 'phone_company';
            }
            elseif ($userData['billingaddress']['company'] && $newUstid) {
                $view->piRatepayInvoiceWarning = 'phone';
                $view->piRatepayRateWarning = 'phone';
                $view->piRatepayDebitWarning = 'phone';
            }
            elseif (!$userData['billingaddress']['company'] && !$newUstid) {
                $view->piRatepayInvoiceWarning = 'phone';
                $view->piRatepayRateWarning = 'phone';
                $view->piRatepayDebitWarning = 'phone';
            }
        }
        else {
            if (!$userData['billingaddress']['company'] && $newUstid) {
                $view->piRatepayInvoiceWarning = 'company';
                $view->piRatepayRateWarning = 'company';
                $view->piRatepayDebitWarning = 'company';
            }
            elseif ($userData['billingaddress']['company'] && !$newUstid) {
                $view->piRatepayInvoiceWarning = 'ustid';
                $view->piRatepayRateWarning = 'ustid';
                $view->piRatepayDebitWarning = 'ustid';
            }
        }
    }
    if ($userData["billingaddress"]["streetnumber"] != $userData["shippingaddress"]["streetnumber"]
        || $userData["billingaddress"]["countryID"] != $userData["shippingaddress"]["countryID"]   ) {
        $view->piRatepayInvoiceWarning = 'address';
        $view->piRatepayRateWarning = 'address';
        $view->piRatepayDebitWarning = 'address';
        $view->pi_ratepay_address = true;
    }
    if (!$userData["billingaddress"]["company"] && $userData["billingaddress"]["ustid"]) {
        $view->pi_ratepay_company = true;
        Shopware()->Session()->ratepayCompanyDiff = true;
    } else{
        Shopware()->Session()->ratepayCompanyDiff = false;
    }

    if (!$userData["billingaddress"]["ustid"] && $userData["billingaddress"]["company"]) {
        $view->pi_ratepay_ustid = true;
        Shopware()->Session()->ratepayUstidDiff = true;
    } else{
        Shopware()->Session()->ratepayUstidDiff = false;
    }
    if (Shopware()->Session()->pi_ratepay_no_ratepay) {
        $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
        $view->piRatepayInvoiceWarning = 'notaccepted';
        $view->datenschutzRatepayInvoice = $config->datenschutz_ratepay_invoice;
        $view->piRatepayRateWarning = 'notaccepted';
        $view->datenschutzRatepayRate = $config->datenschutz_ratepay_rate;
        $view->piRatepayDebitWarning = 'notaccepted';
        $view->datenschutzRatepayDebit = $config->datenschutz_ratepay_debit;
        $view->pi_ratepay_no_ratepay = true;
    }
}

/**
 * Calcluates age
 *
 * @param int   $day    Birthday
 * @param int   $month  Birthmonth
 * @param int   $year   Birthyear
 * @return int
 */
function calculateAge($day, $month, $year) {
    if (!checkdate($month, $day, $year)) return 0;
    $cur_day = date("d");
    $cur_month = date("m");
    $cur_year = date("Y");
    $calc_year = $cur_year - $year;
    if ($month > $cur_month) return $calc_year - 1;
    elseif ($month == $cur_month && $day > $cur_day) return $calc_year - 1;
    else return $calc_year;
}

/**
 *  Sets template vars for current view
 *
 * @param Object    $view        Current view
 * @param Object    $request     Current request
 * @param Object    $config      Plugin configobject
 * @param Array     $userdata    Userdata
 */
function setTemplateVars($view, $request, $config, $userdata) {
    $configArray = array();
    $ratepayVars = array();
    if($userdata["additional"]["payment"]["name"]=="RatePAYInvoice"){
        $configArray = array($config->basket_min_invoice, $config->basket_max_invoice, $config->datenschutz_ratepay_invoice,
            $config->datenschutz_merchant_invoice, $config->widerruf_invoice, $config->due_date_invoice);
    }
    elseif($userdata["additional"]["payment"]["name"]=="RatePAYRate"){
        $configArray = array($config->basket_min_rate, $config->basket_max_rate, $config->datenschutz_ratepay_rate,
            $config->datenschutz_merchant_rate, $config->widerruf_rate, "");
    }
    elseif($userdata["additional"]["payment"]["name"]=="RatePAYDebit"){
        $configArray = array($config->basket_min_debit, $config->basket_max_debit, $config->datenschutz_ratepay_debit,
            $config->datenschutz_merchant_debit, $config->widerruf_debit, $config->due_date_debit);
    }
    if(isset($configArray)){
        $ratepayVars['basketMin'] = str_replace(",", ".", $configArray[0]);
        if ($ratepayVars['basketMin'] < 0) $ratepayVars['basketMin'] = 0.01;
        $ratepayVars['basketMin'] = str_replace(".", ",", $ratepayVars['basketMin']);
        $ratepayVars['basketMax'] = str_replace(",", ".", $configArray[1]);
        $ratepayVars['ratepayDataText'] = $configArray[2];
        $ratepayVars['merchantDataText'] = $configArray[3];
        $ratepayVars['wiederruf'] = $configArray[4];
        $ratepayVars['dueDate'] = $configArray[5];
        $view->piRatepayVars=$ratepayVars;
    }
}

/**
 *  sets session if direct debit is selected for installment
 *
 * @param Array    $post        Postdata
 */
function setDirectDebitSession($post){
    if($post['registerRatePAY']['personal']['debitPayment']=="directDebit"){
        Shopware()->Session()->RatepayDirectDebit=true;
    } else {
        unset(Shopware()->Session()->RatepayDirectDebit);
    }
}


/**
 *  Init payment request
 *
 * @param Object    $pi_RatePAY_request     Current request
 * @param Object    $config      Plugin configobject
 * @param Array     $userData    Userdata
 */
function initPayment($config, $userData) {
    include_once dirname(__FILE__) . '/../Views/Frontend/Ratenrechner/php/pi_ratepay_xml_service.php';
    $liveMode = checkSandboxMode($userData["additional"]["payment"]["name"]);
    $profileId = "";
    $securityCode = "";
    if ($userData["additional"]["payment"]["name"] == "RatePAYInvoice") {
        $profileId = $config->profile_id;
        $securityCode = $config->security_code;
    }
    elseif ($userData["additional"]["payment"]["name"] == "RatePAYRate") {
        $profileId = $config->profile_id_rate;
        $securityCode = $config->security_code_rate;
    }
    elseif ($userData["additional"]["payment"]["name"] == "RatePAYDebit") {
        $profileId = $config->profile_id_debit;
        $securityCode = $config->security_code_debit;
    }
    $customer = $userData['billingaddress']['firstname'] . ' ' . $userData['billingaddress']['lastname'];
    $systemId = $_SERVER['REMOTE_ADDR'];
    $ratepay = new pi_ratepay_xml_service;
    $ratepay->live = $liveMode;
    $request = $ratepay->getXMLObject();
    $head = $request->addChild('head');
    $head->addChild('system-id', $systemId);
    $head->addChild('operation', 'PAYMENT_INIT');
    $credential = $head->addChild('credential');
    $credential->addChild('profile-id', $profileId);
    $credential->addChild('securitycode', $securityCode);
    $response = $ratepay->paymentOperation($request);
    if ($response) {
        writeLog(
            "",
            (string) $response->head->{'transaction-id'},
            "PAYMENT_INIT",
            "",
            $request,
            $response,
            $customer,
            $userData["additional"]["payment"]["name"]
        );
    }
    else writeLog("", "", "PAYMENT_INIT", "", $request, false, $customer, $userData["additional"]["payment"]["name"]);
    Shopware()->Session()->pi_ratepay_ClientIp = $_SERVER['REMOTE_ADDR'];
    Shopware()->Session()->pi_ratepay_transactionID = (string) $response->head->{'transaction-id'};
    Shopware()->Session()->pi_ratepay_transactionShortID = (string) $response->head->{'transaction-short-id'};
    Shopware()->Session()->pi_ratepay_Init = true;
    if ($response && ((string) $response->head->processing->status->attributes()->code == "OK"
            && (string) $response->head->processing->result->attributes()->code == "350")) return true;
    else return false;
}

/**
 *  Confirm payment request
 *
 * @param Object    $pi_RatePAY_request     Current request
 * @param Object    $config      Plugin     configobject
 * @param Array     $userData    Userdata
 */
function confirmPayment($config, $userData) {
    if(!Shopware()->Session()->pi_ratepay_Confirm){
        Shopware()->Session()->pi_ratepay_Confirm = true;
        $liveMode = checkSandboxMode($userData["additional"]["payment"]["name"]);
        include_once dirname(__FILE__) . '/../Views/Frontend/Ratenrechner/php/pi_ratepay_xml_service.php';
        $operation = 'PAYMENT_CONFIRM';
        $ratepay = new pi_ratepay_xml_service();
        $ratepay->live = $liveMode;
        $request = $ratepay->getXMLObject();
        $sql = "SELECT `ordernumber` FROM `s_order` WHERE `transactionID` = ?";
        $myordernumber = Shopware()->Db()->fetchOne($sql, array(Shopware()->Session()->pi_ratepay_transactionID));
        $customer = $userData['billingaddress']['firstname'] . ' ' . $userData['billingaddress']['lastname'];
        setRatepayHead($request, $operation, false, $myordernumber);
        $response = $ratepay->paymentOperation($request);
        if ($response) {
            writeLog(
                Shopware()->Session()->pi_ratepay_ordernumber,
                Shopware()->Session()->pi_ratepay_transactionID,
                "PAYMENT_CONFIRM",
                "",
                $request,
                $response,
                $customer,
                $userData["additional"]["payment"]["name"]
            );
            $sql = "UPDATE `s_order` SET `cleared` = ? WHERE `ordernumber` = ?";
            if ((string) $response->head->processing->status->attributes()->code == "OK"
            && (string) $response->head->processing->result->attributes()->code == "400") {
                Shopware()->Db()->query($sql, array((int)getAcceptedStatusId(), $myordernumber));
            }
            else {
                Shopware()->Db()->query($sql, array((int)getDeclinedStatusId(), $myordernumber));
            }
        }
        else {
            writeLog("", Shopware()->Session()->pi_ratepay_transactionID,
                    "PAYMENT_CONFIRM", "", $request, "", $customer,$userData["additional"]["payment"]["name"]);
            return true;
        }
    }

}

/**
 * Confirm delivery request
 *
 * @param String    $ordernumber    Current ordernumber
 * @param Array     $articles       Current articles
 * @return String
 */
function confirmDelivery($ordernumber, $articles) {
    include_once dirname(__FILE__) . '/../Views/Frontend/Ratenrechner/php/pi_ratepay_xml_service.php';
    $method = checkPaymentMethod($ordernumber);
    $liveMode = checkSandboxMode($method);
    $operation = 'CONFIRMATION_DELIVER';
    $ratepay = new pi_ratepay_xml_service();
    $ratepay->live = $liveMode;
    $request = $ratepay->getXMLObject();
    $userData = getRatepayUserdata($ordernumber);
    $customer = $userData['firstname'] . ' ' . $userData['lastname'];
    setRatepayHead($request, $operation, false, $ordernumber);
    setRatepayContent($request, $operation, false, $ordernumber, $articles);
    $response = $ratepay->paymentOperation($request);
    if ($response) {
        $sql = "SELECT `transactionID` FROM `s_order` WHERE `ordernumber` = ?";
        $transactionID = Shopware()->Db()->fetchOne($sql,  array($ordernumber));
        writeLog($ordernumber, $transactionID, "CONFIRMATION_DELIVER", "", $request, $response, $customer, $method);
        if ((string) $response->head->processing->status->attributes()->code == "OK"
                && (string) $response->head->processing->result->attributes()->code == "404")
            return true;
        else return false;
    }
    else return false;
}

/**
 * Payment change request
 *
 * @param   String  $ordernumber   Current ordernumber
 * @param   Array   $articles      Current articles
 * @param   String  $subtype        Request subtype
 * @return  boolean
 */
function paymentChange($ordernumber, $articles, $subtype) {
    $method = checkPaymentMethod($ordernumber);
    $liveMode = checkSandboxMode($method);
    $userData = getRatepayUserdata($ordernumber);
    $customer = $userData['firstname'] . ' ' . $userData['lastname'];
    include_once dirname(__FILE__) . '/../Views/Frontend/Ratenrechner/php/pi_ratepay_xml_service.php';
    $operation = 'PAYMENT_CHANGE';
    $ratepay = new pi_ratepay_xml_service();
    $ratepay->live = $liveMode;
    $request = $ratepay->getXMLObject();
    setRatepayHead($request, $operation, $subtype, $ordernumber);
    setRatepayContent($request, $operation, $subtype, $ordernumber, $articles);
    $response = $ratepay->paymentOperation($request);
    if ($response) {
        $sql = "SELECT `transactionID` FROM `s_order` WHERE `ordernumber` = ?";
        $transactionID = Shopware()->Db()->fetchOne($sql,  array($ordernumber));
        writeLog($ordernumber, $transactionID, "PAYMENT_CHANGE", $subtype, $request, $response, $customer,$method);
        if ((string) $response->head->processing->status->attributes()->code == "OK"
             && (string) $response->head->processing->result->attributes()->code == "403")
            return true;
        else throw new Exception((string) $response->head->processing->status->attributes()->code);
    }
     else throw new Exception('Fehler bei der Bearbeitung: Keine Response');
}

/**
 * Sets Head for request
 *
 * @param Object $request       Request object
 * @param String $operation     Request operation
 * @param String $subtype       Request subtype
 * @param String $ordernumber   Current ordernumber
 * @return Object
 */
function setRatepayHead($request, $operation, $subtype=false, $ordernumber=false) {
    $systemId = "";
    $transid = "";
    $transshortid = "";
    if ($operation == "CONFIRMATION_DELIVER" || $operation == 'PAYMENT_CHANGE') {
        $systemId = $_SERVER["SERVER_ADDR"];
        $sql = "SELECT `transactionid` FROM `pi_ratepay_orders` WHERE `order_number` = ?";
        $transid = Shopware()->Db()->fetchOne($sql,  array($ordernumber));
        $sql = "SELECT `transaction_short_id` FROM `pi_ratepay_orders` WHERE `order_number` = ?";
        $transshortid = Shopware()->Db()->fetchOne($sql,  array($ordernumber));
    }
    else {
        $systemId = Shopware()->Session()->pi_ratepay_ClientIp;
        $transid = Shopware()->Session()->pi_ratepay_transactionID;
        $transshortid = Shopware()->Session()->pi_ratepay_transactionShortID;
    }
    $head = $request->addChild('head');
    $head->addChild('system-id', $systemId);
    $head->addChild('transaction-id', $transid);
    $head->addChild('transaction-short-id', $transshortid);
    if ($operation == "PAYMENT_CHANGE") {
        $operation = $head->addChild('operation', $operation);
        $operation->addAttribute('subtype', $subtype);
    }
    else $head->addChild('operation', $operation);
    setRatepayHeadCredentials($head, $operation, $subtype, $ordernumber);

    if ($operation == "PAYMENT_REQUEST")
        setRatepayHeadCustomerDevice($head);
    elseif ($operation == "CONFIRMATION_DELIVER" || $operation == 'PAYMENT_CHANGE' || $operation == 'PAYMENT_CONFIRM')
        setRatepayHeadExternal($head, $transid);
    setRatepayHeadMeta($head);
    return($head);
}

/**
 * Sets Customer device for request
 *
 * @param Object $head  Current request head
 */
function setRatepayHeadCustomerDevice($head) {
    $customerDevice = $head->addChild('customer-device');
    setRatepayHeadCustomerDeviceHttpHeader($customerDevice);
}

/**
 * Sets http header list in Customer device for request
 *
 * @param Object $customerDevice  Current customer device Object
 */
function setRatepayHeadCustomerDeviceHttpHeader($customerDevice) {
    $httpHeaderList = $customerDevice->addChild('http-header-list');
    $httpHeaderListAttr = $httpHeaderList->addChild('header', 'text/xml');
    $httpHeaderListAttr->addAttribute('name', 'Accept');
    $httpHeaderListAttr = $httpHeaderList->addChild('header', 'utf-8');
    $httpHeaderListAttr->addAttribute('name', 'Accept-Charset');
    $httpHeaderListAttr = $httpHeaderList->addChild('header', 'x86');
    $httpHeaderListAttr->addAttribute('name', 'UA-CPU');
}

/**
 * Sets credentials for request
 *
 * @param Object $head          Request head object
 * @param String $operation     Payment operation
 * @param String $subtype       Payment subtype
 * @param String $ordernumber   Current ordernumber
 */
function setRatepayHeadCredentials($head, $operation, $subtype, $ordernumber)
{
    $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
    $profileId = "";
    $securityCode = "";
    $operation == 'PAYMENT_REQUEST'? $method = $subtype["additional"]["payment"]["name"]: $method =checkPaymentMethod($ordernumber);
    if ($method == "RatePAYInvoice") {
        $profileId = $config->profile_id;
        $securityCode = $config->security_code;
    } elseif ($method == "RatePAYDebit") {
        $profileId = $config->profile_id_debit;
        $securityCode = $config->security_code_debit;
    } else {
        $profileId = $config->profile_id_rate;
        $securityCode = $config->security_code_rate;
    }
    $credential = $head->addChild('credential');
    $credential->addChild('profile-id', $profileId);
    $credential->addChild('securitycode', $securityCode);
}

/**
 * Sets external for request
 *
 * @param Object $head      Request head object
 * @param String $transid   Current transaction id
 */
function setRatepayHeadExternal($head, $transid)
{
    $external = $head->addChild('external');
    $sql = "SELECT `ordernumber` FROM `s_order` WHERE `transactionID` = ?";
    $myordernumber = Shopware()->Db()->fetchOne($sql, array($transid));
    $external->addChild('order-id', $myordernumber);
}

/**
 * Sets content for request
 *
 * @param Object $request       Request object
 * @param String $operation     Payment operation
 * @param String $subtype       Payment subtype
 * @param String $ordernumber   Current ordernumber
 * @param Array  $articles      Current articles
 * @return object
 */
function setRatepayContent($request, $operation=false, $subtype=false, $ordernumber=false, $articles=false)
{
    $content = $request->addChild('content');
    if ($operation == "CONFIRMATION_DELIVER"){
        setRatepayContentCustomerInvoicing($content, $ordernumber);
        setRatepayContentBasket($content, $operation, $ordernumber, $articles);
    } elseif ($operation == "PAYMENT_CHANGE") {
        setRatepayContentCustomer($content, $operation, $ordernumber);
        setRatepayContentBasketChange($content, $subtype, $operation, $ordernumber, $articles);
        setRatepayContentPayment($content, $operation, $ordernumber, $articles);
    } elseif ($operation == "ratenzahlung" || $operation == "directDebit") {
        setRatepayContentCustomer($content, $operation);
        setRatepayContentBasket($content);
        setRatepayContentPayment($content, $operation);
    } else {
        setRatepayContentCustomer($content, $operation);
        setRatepayContentBasket($content);
        setRatepayContentPayment($content);
    }
    return($content);
}

/**
 * Sets content customer for request
 *
 * @param Object $content       Request content object
 * @param String $operation     Payment operation
 * @param String $ordernumber   Payment ordernumbre
 */
function setRatepayContentCustomer($content, $operation=false, $ordernumber=false)
{
    $customer = $content->addChild('customer');
    $user = array();
    $paymentMethod = "";
    if ($operation == "PAYMENT_CHANGE") {
        $sql = "SELECT `id` FROM `s_order` WHERE `ordernumber` = ?";
        $orderId = Shopware()->Db()->fetchOne($sql, array($ordernumber));
        $sql = "SELECT `userID` FROM `s_order` WHERE `ordernumber` = ?";
        $userId = Shopware()->Db()->fetchOne($sql, array($ordernumber));
        $sql = "SELECT * FROM `s_order_billingaddress` WHERE `orderID` = ?";
        $user['billingaddress'] = Shopware()->Db()->fetchRow($sql, array((int)$orderId));
        $sql = "SELECT `birthday` FROM `s_user_billingaddress` WHERE `userID` = ?";
        $user['billingaddress']['birthday'] = Shopware()->Db()->fetchOne($sql, array((int)$userId));
        $sql = "SELECT `ustid` FROM `s_user_billingaddress` WHERE `userID` = ?";
        $user['billingaddress']['ustid'] = Shopware()->Db()->fetchOne($sql, array((int)$userId));
        $sql = "SELECT `company` FROM `s_user_billingaddress` WHERE `userID` = ?";
        $user['billingaddress']['company'] = Shopware()->Db()->fetchOne($sql, array((int)$userId));
        $sql = "SELECT `email` FROM `s_user` WHERE `id` = ?";
        $user['additional']['user']['email'] = Shopware()->Db()->fetchOne($sql, array((int)$userId));
        $sql = "SELECT `paymentID` FROM `s_user` WHERE `id` = ?";
        $user['additional']['payment']['id'] = Shopware()->Db()->fetchOne($sql, array((int)$userId));
        $sql = "SELECT * FROM `s_order_shippingaddress` WHERE `orderID` = ?";
        $user['shippingaddress'] = Shopware()->Db()->fetchOne($sql, array((int)$orderId));
        $paymentMethod = checkPaymentMethod($ordernumber);

    } else $user = Shopware()->Session()->sOrderVariables['sUserData'];
    $gender = $user['billingaddress']['salutation'] == "mr"? "M": "F";
    $sql = "SELECT `countryiso` FROM `s_core_countries` WHERE `id` = ?";
    $country = Shopware()->Db()->fetchOne($sql, array((int)$user['billingaddress']['countryID']));
    $fname = removeSpecialChars(html_entity_decode($user['billingaddress']['firstname']));
    $customer->addCDataChild('first-name', $fname);
    $lname = removeSpecialChars(html_entity_decode($user['billingaddress']['lastname']));
    $customer->addCDataChild('last-name', $lname);
    $customer->addChild('gender', $gender);
    $customer->addChild('date-of-birth', $user['billingaddress']['birthday']);
    if ($operation != "PAYMENT_CHANGE"){
        $customer->addChild('ip-address', Shopware()->Session()->pi_ratepay_ClientIp);
    }
    if ($user['billingaddress']['ustid'] && $user['billingaddress']['company']) {
        $customer->addCDataChild('company-name', $user['billingaddress']['company']);
        $customer->addChild('vat-id', $user['billingaddress']['ustid']);
    }
    setRatepayContentCustomerContacts($customer, $user);
    setRatepayContentCustomerAddress($customer, $user);
    if(($operation=="ratenzahlung" && Shopware()->Session()->RatepayDirectDebit && $operation != "PAYMENT_CHANGE")
            || $operation == "directDebit"
    ){
        setRatepayContentCustomerBankData($customer, $user);
    }
    $customer->addChild('nationality', $country);
    $customer->addChild('customer-allow-credit-inquiry', 'yes');
}

/**
 * Sets content customer contact for request
 *
 * @param Object $customer          Request customer object
 * @param Array  $user              Current user
 */
function setRatepayContentCustomerContacts($customer, $user)
{
    $contacts = $customer->addChild('contacts');
    $contacts->addChild('email', $user['additional']['user']['email']);
    if (!empty($user['billingaddress']['phone'])){
        setRatepayContentCustomerContactsPhone($contacts, $user);
    }
    if (!empty($user['billingaddress']['fax'])){
        setRatepayContentCustomerContactsFax($contacts, $user);
    }
}

/**
 * Sets content customer contact phone for request
 *
 * @param Object $contacts          Request contacts object
 * @param Array  $user              Current user
 */
function setRatepayContentCustomerContactsPhone($contacts, $user)
{
    $phone = $contacts->addChild('phone');
    $phone->addChild('direct-dial', $user['billingaddress']['phone']);
}

/**
 * Sets content customer contact fax for request
 *
 * @param Object $contacts          Request contacts object
 * @param Array  $user              Current user
 */
function setRatepayContentCustomerContactsFax($contacts, $user)
{
    $phone = $contacts->addChild('fax');
    $phone->addChild('direct-dial', $user['billingaddress']['fax']);
}

/**
 * Sets content customer address for request
 *
 * @param Object $customer          Request customer object
 * @param Array  $user   Current user
 */
function setRatepayContentCustomerAddress($customer, $user)
{
    $addresses = $customer->addChild('addresses');
    setRatepayContentCustomerAddressBilling($addresses, $user);
    setRatepayContentCustomerAddressShipping($addresses, $user);
}

/**
 * Sets content invoice for request
 *
 * @param Object $content       Request content object
 */
function setRatepayContentCustomerInvoicing($content, $ordernumber)
 {
    $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
    $sql = "SELECT `payment_name` FROM `pi_ratepay_orders` WHERE `order_number` = ?";
    $method = Shopware()->Db()->fetchOne($sql, array($ordernumber));
    if ($method == 'RatePAYInvoice') $dueDate = $config->due_date_invoice;
    elseif ($method == 'RatePAYDebit' ) $dueDate = $config->due_date_debit;
    $invoicing = $content->addChild('invoicing');
    $invoicing->addChild('delivery-date', date(DATE_ATOM, mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"))));
    if($method != 'RatePAYRate'){
        $invoicing->addChild('due-date', date(DATE_ATOM, mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $dueDate, date("Y"))));
    }
}

/**
 * Sets content customer bank data for request
 *
 * @param Object $customer          Request customer object
 * @param Array  $user   Current user
 */
function setRatepayContentCustomerBankData($customer, $user)
{
    $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
    $debitData = array();
    if(($user['additional']['payment']['id'] == getRatePaymentId() && $config->bankdata_rate )
        ||($user['additional']['payment']['id'] == getDebitPaymentId() && $config->bankdata_debit)
    ) {
        $debitData= getEncodedDebitData($user['billingaddress']['userID']);
    }
    else{
       $debitData  = getDebitData();
    }

    $bankData = $customer->addChild('bank-account');
    $bankData->addCDataChild('owner', $debitData['owner']);
    $bankData->addChild('bank-account-number', $debitData['accountnumber']);
    $bankData->addChild('bank-code', $debitData['bankcode']);
    $bankData->addCDataChild('bank-name', $debitData['bankname']);
}

/**
 * Sets Bankdata to XXX
 *
 * @param Array   $request   Current request
 * @param Array  $user   Current user
 * @return Array  $request   Changed request
 */
function checkBankDataSave($request, $user)
{
    $request->content->customer->{'bank-account'}->{'owner'}='XXX';
    $request->content->customer->{'bank-account'}->{'bank-account-number'}='XXX';
    $request->content->customer->{'bank-account'}->{'bank-code'}='XXX';
    $request->content->customer->{'bank-account'}->{'bank-name'}='XXX';
    return $request;
}

/**
 * Sets Bankdata to XXX
 *
 * @param Array   $post   Current request
 * @return boolean
 */
function checkDebitData($post)
{
    if($post['register']['payment'] == getRatePaymentId()){
        Shopware()->Session()->RatepayDebitMissingBankData = false;
        if(empty($post['ratepayRateDebit']['owner'])){
            Shopware()->Session()->RatepayRateMissingBankData = true;
            Shopware()->Session()->RatepayOwner = false;
            return false;
        }
        else{
             Shopware()->Session()->RatepayOwner = $post['ratepayDebit']['owner'];
        }
        if(empty($post['ratepayRateDebit']['accountnumber'])){
            Shopware()->Session()->RatepayRateMissingBankData = true;
            Shopware()->Session()->RatepayAccountnumber = false;
            return false;
        }
        else{
             Shopware()->Session()->RatepayAccountnumber = $post['ratepayDebit']['accountnumber'];
        }
        if(empty($post['ratepayRateDebit']['bankcode'])){
            Shopware()->Session()->RatepayRateMissingBankData = true;
            Shopware()->Session()->RatepayBankcode = false;
            return false;
        }
        else{
             Shopware()->Session()->RatepayBankcode = $post['ratepayDebit']['bankcode'];
        }
        if(empty($post['ratepayRateDebit']['bankname'])){
            Shopware()->Session()->RatepayRateMissingBankData = true;
            Shopware()->Session()->RatepayBankname = false;
            return false;
        }
        else{
             Shopware()->Session()->RatepayBankname = $post['ratepayDebit']['bankname'];
        }
    }
    elseif($post['register']['payment'] == getDebitPaymentId()){
        Shopware()->Session()->RatepayRateMissingBankData = false;
        if(empty($post['ratepayDebit']['owner'])){
            Shopware()->Session()->RatepayDebitMissingBankData = true;
            Shopware()->Session()->RatepayOwner = false;
            return false;
        }
        else{
             Shopware()->Session()->RatepayOwner = $post['ratepayDebit']['owner'];
        }
        if(empty($post['ratepayDebit']['accountnumber'])){
            Shopware()->Session()->RatepayDebitMissingBankData = true;
            Shopware()->Session()->RatepayAccountnumber = false;
            return false;
        }
        else{
             Shopware()->Session()->RatepayAccountnumber = $post['ratepayDebit']['accountnumber'];
        }
        if(empty($post['ratepayDebit']['bankcode'])){
            Shopware()->Session()->RatepayDebitMissingBankData = true;
            Shopware()->Session()->RatepayBankcode = false;
            return false;
        }
        else{
             Shopware()->Session()->RatepayBankcode = $post['ratepayDebit']['bankcode'];
        }
        if(empty($post['ratepayDebit']['bankname'])){
            Shopware()->Session()->RatepayDebitMissingBankData = true;
            Shopware()->Session()->RatepayBankname = false;
            return false;
        }
        else{
             Shopware()->Session()->RatepayBankname = $post['ratepayDebit']['bankname'];
        }
    }
    return true;
}

/**
 * Sets content customer billing address for request
 *
 * @param Object $addresses         Request address object
 * @param Array  $user   Current user
 */
function setRatepayContentCustomerAddressBilling($addresses, $user)
{
    $billingAddress = $addresses->addChild('address');
    $billingAddress->addAttribute('type', 'BILLING');
    $street = removeSpecialChars(html_entity_decode($user['billingaddress']['street']));
    $billingAddress->addCDataChild('street', $street);
    $billingAddress->addChild('street-number', $user['billingaddress']['streetnumber']);
    $billingAddress->addChild('zip-code', $user['billingaddress']['zipcode']);
    $city = removeSpecialChars(html_entity_decode($user['billingaddress']['city']));
    $billingAddress->addCDataChild('city', $city);
    $sql = "SELECT `countryiso` FROM `s_core_countries` WHERE `id` = ?";
    $countryIso = Shopware()->Db()->fetchOne($sql, array((int)$user['billingaddress']['countryID']));
    $billingAddress->addChild('country-code', $countryIso);
}

/**
 * Sets content customer shipping address for request
 *
 * @param Object $addresses         Request address object
 * @param Array  $user   Current user
 */
function setRatepayContentCustomerAddressShipping($addresses, $user)
{
    $shippingAddress = $addresses->addChild('address');
    $shippingAddress->addAttribute('type', 'DELIVERY');
    $street = removeSpecialChars(html_entity_decode($user['shippingaddress']['street']));
    $shippingAddress->addCDataChild('street', $street);
    $shippingAddress->addChild('street-number', $user['shippingaddress']['streetnumber']);
    $shippingAddress->addChild('zip-code', $user['shippingaddress']['zipcode']);
    $city = removeSpecialChars(html_entity_decode($user['shippingaddress']['city']));
    $shippingAddress->addCDataChild('city', $city);
    $sql = "SELECT `countryiso` FROM `s_core_countries` WHERE `id` = ?";
    $countryIso = Shopware()->Db()->fetchOne($sql, array((int)$user['shippingaddress']['countryID']));
    $shippingAddress->addChild('country-code', $countryIso);
}

/**
 * Sets content basket for request
 *
 * @param Object $content       Request content object
 * @param String $operation     Payment operation
 * @param String $ordernumber   Current ordernumber
 * @param Array  $articles      Current articles
 */
function setRatepayContentBasket($content, $operation=false, $ordernumber=false, $articles=false)
{
    if ($operation == "CONFIRMATION_DELIVER" || $operation == "PAYMENT_CHANGE") {
        $total = 0;
        foreach ($articles as $article) {
            $total +=($article['einzelpreis'] * $article['anzahl']);
        }
        $shoppingBasket = $content->addChild('shopping-basket');
        $shoppingBasket->addAttribute('amount', number_format($total, 2, ".", ""));
        $shoppingBasket->addAttribute('currency', 'EUR');
        setRatepayContentBasketItems($shoppingBasket, $articles, $operation);
    } else {
        $basket = Shopware()->Session()->sOrderVariables['sBasket'];
        $shoppingBasket = $content->addChild('shopping-basket');
        $shoppingBasket->addAttribute('amount', number_format($basket['AmountNumeric'], 2, ".", ""));
        $shoppingBasket->addAttribute('currency', 'EUR');
        setRatepayContentBasketItems($shoppingBasket, $basket);
    }
}

/**
 * Sets content basket items request
 *
 * @param Object $shoppingBasket    Request basket object
 * @param Array  $basket            Current shopping basket
 * @param String $operation         Payment operation
 */
function setRatepayContentBasketItems($shoppingBasket, $basket, $operation=false)
{
    $items = $shoppingBasket->addChild('items');
    setRatepayContentBasketItemsItem($items, $basket, $operation);
}

/**
 * Sets content basket articles request
 *
 * @param Object $items             Request basket items object
 * @param Array  $basket            Current shopping basket
 * @param String $operation         Payment operation
 */
function setRatepayContentBasketItemsItem($items, $basket, $operation=false)
{
    if ($operation == "CONFIRMATION_DELIVER") {
        setRatepayContentBasketItemsItemDelivery($items, $basket);
    } else {
        $articles = $basket['content'];
        foreach ($articles as $article) {
            $title = removeSpecialChars(html_entity_decode($article['articlename']));
            $tax = str_replace(",", ".", $article['tax']);
            $item = $items->addCDataChild('item', $title);
            $item->addAttribute('article-number', $article['ordernumber']);
            $item->addAttribute('quantity', $article['quantity']);
            $item->addAttribute('unit-price', number_format($article['netprice'],2,".",""));
            $item->addAttribute('total-price', number_format(($article['priceNumeric']*$article['quantity']) - $tax,2,".",""));
            $item->addAttribute('tax', number_format($tax,2,".",""));
        }
        if ($basket['sShippingcostsNet'] > 0) {
            $item = $items->addCDataChild('item', 'Versandkosten');
            $item->addAttribute('article-number', 'versand');
            $item->addAttribute('quantity', 1);
            $item->addAttribute('unit-price', $basket['sShippingcostsNet']);
            $item->addAttribute('total-price', $basket['sShippingcostsNet']);
            $item->addAttribute('tax', $basket['sShippingcostsWithTax'] - $basket['sShippingcostsNet']);
        }
    }
}

/**
 * Sets content basket articles for delivery request
 *
 * @param Object $items             Request basket items object
 * @param Array  $basket Current shopping basket
 */
function setRatepayContentBasketItemsItemDelivery($items, $basket)
{
    foreach ($basket as $article) {
        $title = removeSpecialChars(html_entity_decode($article['name']));
        $tax = ($article['einzelpreis'] * $article['anzahl']) - (round($article['einzelpreis_net'] * $article['anzahl'],2));
        $item = $items->addCDataChild('item', $title);
        $item->addAttribute('article-number', $article['bestell_nr']);
        $item->addAttribute('quantity', $article['anzahl']);
        $item->addAttribute('unit-price', number_format($article['einzelpreis_net'],2,".",""));
        $item->addAttribute('total-price', number_format($article['einzelpreis_net']*$article['anzahl'],2,".",""));
        $item->addAttribute('tax', number_format($tax,2,".",""));
    }
}

/**
 * Sets content basket for payment change request
 *
 * @param Object $content       Request content object
 * @param String $subtype       Payment subtype
 * @param String $operation     Payment operation
 * @param String $ordernumber   Current ordernumber
 * @param Array  $articles      Current articles
 */
function setRatepayContentBasketChange($content, $subtype, $operation, $ordernumber, $articles)
{
    $total = 0;
    if ($subtype != "full-cancellation" && $subtype != "full-return") {
        foreach ($articles as $article) {
            $total = $total + (($article['bestellt'] - $article['storniert'] - $article['retourniert']) * $article['einzelpreis']);
        }
    }
    $shoppingBasket = $content->addChild('shopping-basket');
    $shoppingBasket->addAttribute('amount', number_format($total, 2, ".", ""));
    $shoppingBasket->addAttribute('currency', 'EUR');
    setRatepayContentBasketItemsChange($shoppingBasket, $subtype, $articles);
}

/**
 * Sets content basket items for payment change request
 *
 * @param Object  $shoppingBasket   Request basket object
 * @param String  $subtype          Payment subtype
 * @param Array   $articles         Current articles
 */
function setRatepayContentBasketItemsChange($shoppingBasket, $subtype, $articles)
{
    $items = $shoppingBasket->addChild('items');
    if ($subtype != "full-cancellation" && $subtype != "full-return") {
        setRatepayContentBasketItemsItemChange($items, $subtype, $articles);
    }
}

/**
 * Sets content basket articles for payment change request
 *
 * @param Object  $items            Request items object
 * @param String  $subtype          Payment subtype
 * @param Array   $articles         Current articles
 */
function setRatepayContentBasketItemsItemChange($items, $subtype, $articles)
{
    foreach ($articles as $article) {
        $quant = $article['bestellt'] - $article['storniert'] - $article['retourniert'];
        if ($quant > 0) {
            $tax = ($article['einzelpreis'] * $quant) - (round($article['einzelpreis_net'] * $quant,2));
            $title = '';
            $title = removeSpecialChars(html_entity_decode($article['name']));
            $item = $items->addCDataChild('item', $title);
            $item->addAttribute('article-number', $article['bestellnr']);
            $item->addAttribute('quantity', $quant);
            $item->addAttribute('unit-price', number_format($article['einzelpreis_net'],2,".",""));
            $item->addAttribute('total-price', number_format($article['einzelpreis_net']*$quant,2,".",""));
            $item->addAttribute('tax', number_format($tax,2,".",""));
        }
    }
}

/**
 * Sets content basket articles for payment change request
 *
 * @param Object  $content          Request content object
 * @param String  $operation        Payment operation
 * @param String  $ordernumber      Current ordernumber
 * @param Array   $articles         Current articles
 */
function setRatepayContentPayment($content, $operation=false, $ordernumber=false, $articles=false)
{
    if ($operation == 'ratenzahlung') setRatepayContentPaymentRequestRate($content);
    elseif($operation == 'directDebit') setRatepayContentPaymentRequestDebit($content);
    else {
        if ($operation == "PAYMENT_CHANGE") {
            $sql = "SELECT `payment_name` FROM `pi_ratepay_orders` WHERE `order_number` = ?";
            $method = Shopware()->Db()->fetchOne($sql, array($ordernumber));
            if ($method == 'RatePAYInvoice') setRatepayContentPaymentChangeInvoice($content, $articles);
            elseif ($method == 'RatePAYRate' ) setRatepayContentPaymentChangeRate($content, $articles);
            elseif ($method == 'RatePAYDebit' ) setRatepayContentPaymentChangeDebit($content, $articles);
        }
        else setRatepayContentPaymentRequestInvoice($content);
    }
}

/**
 * Sets content payment request for invoice
 *
 * @param Object  $content          Request content object
 */
function setRatepayContentPaymentRequestInvoice($content)
{
    $pi_ratepay_basket = Shopware()->Session()->sOrderVariables['sBasket'];
    $payment = $content->addChild('payment');
    $payment->addAttribute('method', 'INVOICE');
    $payment->addAttribute('currency', 'EUR');
    $payment->addChild('amount', number_format($pi_ratepay_basket['AmountNumeric'], 2, ".", ""));
}


/**
 * Sets content payment request for rate
 *
 * @param Object  $content          Request content object
 */
function setRatepayContentPaymentRequestDebit($content)
{
    $pi_ratepay_basket = Shopware()->Session()->sOrderVariables['sBasket'];
    $payment = $content->addChild('payment');
    $payment->addAttribute('method', 'ELV');
    $payment->addAttribute('currency', 'EUR');
    $payment->addChild('amount', number_format($pi_ratepay_basket['AmountNumeric'], 2, ".", ""));
}

/**
 * Sets content payment request for rate
 *
 * @param Object  $content          Request content object
 */
function setRatepayContentPaymentRequestRate($content)
{
    $payment = $content->addChild('payment');
    $payment->addAttribute('method', 'INSTALLMENT');
    $payment->addAttribute('currency', 'EUR');
    $payment->addChild('amount', number_format(Shopware()->Session()->pi_ratepay_total_amount, 2, ".", ""));
    $installment = $payment->addChild('installment-details');
    $installment->addChild('installment-number', Shopware()->Session()->pi_ratepay_number_of_rates);
    $installment->addChild('installment-amount', number_format(Shopware()->Session()->pi_ratepay_rate, 2, ".", ""));
    $installment->addChild('last-installment-amount', number_format(Shopware()->Session()->pi_ratepay_last_rate, 2, ".", ""));
    $installment->addChild('interest-rate', number_format(Shopware()->Session()->pi_ratepay_interest_rate, 2, ".", ""));
    $installment->addChild('payment-firstday', (int) Shopware()->Session()->dueDate);
    if(Shopware()->Session()->RatepayDirectDebit){
        $payment->addChild('debit-pay-type', 'DIRECT-DEBIT');
    } else{
        $payment->addChild('debit-pay-type', 'BANK-TRANSFER');
    }
}

/**
 * Sets content payment for payment change request at invoice
 *
 * @param Object  $content          Request content object
 * @param Array   $articles         Current articles
 */
function setRatepayContentPaymentChangeInvoice($content, $articles)
{
    $payment = $content->addChild('payment');
    $payment->addAttribute('method', 'INVOICE');
    $payment->addAttribute('currency', 'EUR');
}

/**
 * Sets content payment for payment change request at rate
 *
 * @param Object  $content          Request content object
 * @param Array   $articles         Current articles
 */
function setRatepayContentPaymentChangeRate($content, $articles)
{
    $payment = $content->addChild('payment');
    $payment->addAttribute('method', 'INSTALLMENT');
    $payment->addAttribute('currency', 'EUR');
}

/**
 * Sets content payment for payment change request at dabit
 *
 * @param Object  $content          Request content object
 * @param Array   $articles         Current articles
 */
function setRatepayContentPaymentChangeDebit($content, $articles)
{

    $payment = $content->addChild('payment');
    $payment->addAttribute('method', 'ELV');
    $payment->addAttribute('currency', 'EUR');
}

    /**
     * Sets meta for request
     *
     * @param Object $head      Request head object
     */
    function setRatepayHeadMeta($head)
    {
        $sql = "SELECT `version` FROM `s_core_plugins` WHERE name ='PigmbhRatePAYPayment'";
        $pluginVersion =  Shopware()->Db()->fetchOne($sql);
        $meta = $head->addChild('meta');
        $systems = $meta->addChild('systems');
        $system = $systems->addChild('system');
        $system->addAttribute('version', Shopware()->Config()->Version .'_' . $pluginVersion);
        $system->addAttribute('name', 'Shopware');
    }

/**
 * Gets user from current order
 *
 * @param String  $ordernumber      Current Ordernumber
 *
 * @return Array
 */
function getRatepayUserdata($ordernumber)
{
    $sql = "SELECT `id` FROM `s_order` WHERE `ordernumber` = ? LIMIT 1";
    $orderid = Shopware()->Db()->fetchOne($sql, array($ordernumber));
    $sql = "SELECT * FROM `s_order_billingaddress` WHERE `orderID` = ?";
    $Userdata = Shopware()->Db()->fetchRow($sql, array((int)$orderid));
    return $Userdata;
}

/**
 * Replaces special chars from string
 *
 * @param String  $str        String
 * return function
 */
function removeSpecialChars($str)
{
    $search = array("–", "´", "‹", "›", "‘", "’", "‚", "“", "”", "„", "‟", "•", "‒", "―", "—", "™", "¼", "½", "¾", "%", "©", "ó" );
    $replace = array("-", "'", "<", ">", "'", "'", ",", '"', '"', '"', '"', "-", "-", "-", "-", "TM", "1/4", "1/2", "3/4", "prozent","&copy;","o", "&ograve;");
    return removeSpecialChar($search, $replace, $str);
}

/**
 * Replaces special chars from string
 *
 * @param String  $str        String
 * return function
 */
function removeSpecialCharsforLog($str)
{
      $search = array("&lt;", "&gt;", "'",);
      $replace = array("<", ">", "&acute;");
      return removeSpecialChar($search, $replace, $str);
}


/**
 *
 * @param Array $search     Search Array
 * @param Array $replace    Replace Array
 * @param String $subject   String where chars are replaces
 * @return String
 */
function removeSpecialChar($search, $replace, $subject)
{
    $str = str_replace($search, $replace, $subject);
    return $str;
}

/**
 * Checks if current order is paid per invoice or rate
 *
 * @param String $ordernumber   Current ordernumber
 * @return String $pi_ratepay_method
 */
function checkPaymentMethod($ordernumber)
{
    $sql = "SELECT `payment_name` FROM `pi_ratepay_orders` WHERE `order_number` = ?";
    $paymentMethod = Shopware()->Db()->fetchOne($sql, array($ordernumber));
    return $paymentMethod;
}

/**
 * Checks if testmode is enabled
 *
 * @param String $method Invoice or rate
 * @return boolean
 */
function checkSandboxMode($method)
{
    $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
    if ($method == 'RatePAYInvoice') {
        return $config->sandbox_mode == true ? false : true;
    } elseif ($method == 'RatePAYDebit') {
        return $config->sandbox_mode_debit == true ? false : true;
    } else {
        return $config->sandbox_mode_rate == true ? false : true;
    }
}

/**
 * login function
 *
 * @param	String $orderId			Ordernumber
 * @param	String $transactionId		Transaction ID
 * @param	String $payment_type		Type of the call
 * @param	String $payment_subtype		Subtype of the call
 * @param	String $request			The request sent
 * @param	String $response		The response returned
 */
function writeLog($orderId, $transactionId, $paymentType, $paymentSubtype, $request, $response = false, $customer= false, $paymentMethod= false)
{
    if (!$paymentMethod) {
        $paymentmethod = checkPaymentMethod($orderId);
        if (!$paymentmethod) $paymentmethod = '';
    }
    $result = "";
    $reason = "";
    $resultCode = "";
    $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
    $logging = $config->logging;
    if ($logging) {
        $requestXml = $request->asXML();
        $responseXml = '';
        if ($response) {
            $result = (string) $response->head->processing->result;
            $resultCode = (string) $response->head->processing->result->attributes()->code;
            $reason = (string) $response->head->processing->reason;
            $responseXml = $response->asXML();
        } else {
            $result = "service unavaible.";
            $reason = "service unavaible.";
            $resultCode = "service unavaible.";
        }
        if(!$paymentSubtype)$paymentSubtype=" ";
        if (!$paymentMethod) {
            if ($paymentSubtype == 'ratenzahlung') $paymentmethod = "Installment";
            else $paymentmethod = "Invoice";
        }
        elseif ($paymentMethod == 'RatePAYInvoice') $paymentmethod = "Invoice";
        elseif ($paymentMethod == 'RatePAYDebit') $paymentmethod = "ELV";
        else $paymentmethod = "Installment";
        $sql = "INSERT INTO `pi_ratepay_log`
                (
                    `order_number`,
                    `transaction_id`,
                    `payment_method`,
                    `payment_type`,
                    `payment_subtype`,
                    `result`,
                    `request`,
                    `response`,
                    `result_code`,
                    `response_reason`,
                    `customer`
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        Shopware()->Db()->query($sql, array(
            $orderId,
            $transactionId,
            $paymentmethod,
            $paymentType,
            $paymentSubtype,
            $result,
            htmlspecialchars($requestXml),
            htmlspecialchars($responseXml),
            $resultCode,
            $reason,
            $customer
        ));
    }
}

/**
 * after every change the orerprice is calculated new and updated in the database.
 *
 * @param  String $pi_RatePAY_invoice_ordernumber  Ordernumber where the changes are made.
 */
function berechneGesamtpreis($pi_RatePAY_invoice_ordernumber)
{
    $sql = "SELECT `net` FROM `s_order` WHERE ordernumber = ? LIMIT 1";
    $net = Shopware()->Db()->fetchOne($sql, array($pi_RatePAY_invoice_ordernumber));
    $invoice_amount = 0;
    $db_amount = 0;
    $db_amount_net = 0;
    $data = array();
    if ($net == 0) {
        $sql = "SELECT SUM(det.price*det.`quantity`)+ord.invoice_shipping AS new_invoice_amount
                FROM `s_order_details` AS det
                LEFT JOIN `s_order` AS ord ON(ord.`ordernumber` = det.`ordernumber`)
                WHERE det.ordernumber = ?";
        $invoice_amount = Shopware()->Db()->fetchAll($sql, array($pi_RatePAY_invoice_ordernumber));
        $db_amount = $invoice_amount[0]['new_invoice_amount'];
        $invoice_amount_net = 0;
        $sql = "SELECT (details.price * details.quantity) AS total, tax.tax, `order`.invoice_shipping_net
                FROM `s_order_details` AS details
                LEFT JOIN `s_core_tax` AS tax ON ( tax.id = details.taxID )
                LEFT JOIN `s_order` AS `order` ON ( `order`.ordernumber = details.ordernumber )
            WHERE details.ordernumber = ?";
        $data = Shopware()->Db()->fetchAll($sql, array($pi_RatePAY_invoice_ordernumber));
        $invoice_shipping_net = $data[0]['invoice_shipping_net'];
        $data[0]['tax'] == null ? $tax = 19 : $tax = $data[0]['tax'];
        $plus = $data[0]['total'] / (100 + $tax) * 100;
        $invoice_amount_net = $invoice_amount_net + $plus;
        $invoice_amount_net = $invoice_amount_net + $invoice_shipping_net;
        $db_amount_net = $invoice_amount_net;
    } else {
        $sql = "SELECT SUM(det.price*det.`quantity`)+ord.invoice_shipping_net AS new_invoice_amount_net
                FROM `s_order_details` AS det
                LEFT JOIN `s_order` AS ord ON(ord.`ordernumber` = det.`ordernumber`)
                WHERE det.ordernumber = ?";
        $invoice_amount_net = Shopware()->Db()->fetchAll($sql, array($pi_RatePAY_invoice_ordernumber));
        $db_amount_net = $invoice_amount_net[0]['new_invoice_amount_net'];
        $invoice_amount_net = 0;
        $sql = "SELECT (details.price * details.quantity) AS total, tax.tax, `order`.invoice_shipping
                FROM `s_order_details` AS details
                LEFT JOIN `s_core_tax` AS tax ON ( tax.id = details.taxID )
                LEFT JOIN `s_order` AS `order` ON ( `order`.ordernumber = details.ordernumber )
                WHERE details.ordernumber ='" . $pi_RatePAY_invoice_ordernumber . "'";
        $data = Shopware()->Db()->fetchAll($sql, array($pi_RatePAY_invoice_ordernumber));
        $invoice_shipping = $data[0]['invoice_shipping'];
        $data[0]['tax'] == null ? $tax = 19 : $tax = $data[0]['tax'];
        $plus = $data[0]['total'] / 100 * (100 + $tax);
        $invoice_amount_net = $invoice_amount_net + $plus;
        $invoice_amount_net = $invoice_amount_net + $invoice_shipping;
        $db_amount = $invoice_amount_net;
    }
    Shopware()->Db()->query(
        "UPDATE `s_order`
        SET `invoice_amount` = ?, `invoice_amount_net` = ?
        WHERE `ordernumber` = ? LIMIT 1", array( $db_amount, $db_amount_net, $pi_RatePAY_invoice_ordernumber )
    );
}

/**
* Gets ID of RatePAY part canceled state
*
* @return Int $status  Status
*/
function getPartCanceledStatusId()
{
    return Shopware()->Db()->fetchOne("
        SELECT id
        FROM s_core_states
        WHERE description like '%ratepaystate%Teilweise storniert%'
    ");
}

/**
* Gets ID of RatePAY complete canceled state
*
* @return Int $status  Status
*/
function getCompleteCancelStatusId()
{
    $sql = "SELECT `id` FROM `s_core_states` WHERE `description` LIKE '%ratepaystate%Komplett storniert%'";
    return Shopware()->Db()->fetchOne($sql);
}

/**
* Gets ID of RatePAY invoice payment
*
* @return Int $piRatePAYInvoiceId  ID
*/
function getInvoicePaymentId()
{
    $sql = "SELECT `id` FROM `s_core_paymentmeans` WHERE `name` = 'RatePAYInvoice'";
    return Shopware()->Db()->fetchOne($sql);
}

/**
* Gets ID of RatePAY rate payment
*
* @return Int $piRatePAYInvoiceId        ID
*/
function getRatePaymentId()
{
    $sql = "SELECT `id` FROM `s_core_paymentmeans` WHERE `name` = 'RatePAYRate'";
    return Shopware()->Db()->fetchOne($sql);
}

/**
* Gets ID of RatePAY debit payment
*
* @return Int $debitId                   ID
*/
function getDebitPaymentId()
{
    $sql = "SELECT `id` FROM `s_core_paymentmeans` WHERE `name` = 'RatePAYDebit'";
    return Shopware()->Db()->fetchOne($sql);
}
/**
* Gets ID of RatePAY accepted state
*
* @return Int $status  Status
*/
function getAcceptedStatusId()
{
    $sql = "SELECT `id` FROM `s_core_states` WHERE `description` LIKE '%Zahlung von RatePAY akzeptiert%'";
    return Shopware()->Db()->fetchOne($sql);
}

 /**
* Gets ID of RatePAY declined state
*
* @return Int $status  Status
*/
function getDeclinedStatusId()
{
    $sql = "SELECT `id` FROM `s_core_states` WHERE `description` LIKE '%Zahlung von RatePAY nicht akzeptiert%'";
    return Shopware()->Db()->fetchOne($sql);
}

/**
* Gets ID of RatePAY part send state
*
* @return Int $status  Status
*/
function getpartSentStatusID()
{
    $sql = "SELECT `id` FROM `s_core_states` WHERE `description` LIKE '%ratepaystate%Teilweise versendet%'";
    return Shopware()->Db()->fetchOne($sql);
}

/**
* Gets ID of RatePAY complete send state
*
* @return Int $status  Status
*/
function getCompleteSentStatusID()
{
    $sql = "SELECT `id` FROM `s_core_states` WHERE `description` LIKE '%ratepaystate%Komplett versendet%'";
    return Shopware()->Db()->fetchOne($sql);
}

/**
* Gets ID of RatePAY part return state
*
* @return Int $status  Status
*/
function getPartReturnStatusId()
{
    $sql = "SELECT `id` FROM `s_core_states` WHERE `description` LIKE '%ratepaystate%Teilweise retourniert%'";
    return Shopware()->Db()->fetchOne($sql);
}

/**
* Gets ID of RatePAY complete return state
*
* @return Int $status  Status
*/
function getCompleteReturnStatusId()
{
    $sql = "SELECT `id` FROM `s_core_states` WHERE `description` LIKE '%ratepaystate%Komplett retourniert%'";
    return Shopware()->Db()->fetchOne($sql);
}
/**
 * Checks if current user has ratepay selected as payment
 *
 * @param  Array $userData Current userdata
 * @return Boolean
 */
function checkRatepayPayment($userData)
{
    if($userData["additional"]["payment"]["name"] == "RatePAYInvoice"
            || $userData["additional"]["payment"]["name"] == "RatePAYRate"
            || $userData["additional"]["payment"]["name"] == "RatePAYDebit"
    ){
        return true;
    }
    else return false;
}

/**
 * sets surcharge for ratepay payments
 *
 * @param  Array $basket    Current basket
 * @param  View $view       Current view
 */

function setSurcharge($basket, $view)
{
    $invoiceSurcharge = getSurcharge('Invoice',$basket);
    $rateSurcharge= getSurcharge('Rate',$basket);
    $debitSurcharge= getSurcharge('Debit',$basket);
    if($invoiceSurcharge!='0,00')$view->ratepayInvoiceSurcharge = $invoiceSurcharge;
    if($rateSurcharge!='0,00')$view->ratepayRateSurcharge = $rateSurcharge;
    if($debitSurcharge!='0,00')$view->ratepayDebitSurcharge = $debitSurcharge;
}

/**
 * gets userage of current user
 *
 * @param   Array $userData   Current userdata
 * @param   View  $view       Current view
 * @return  Int   $userAge    Current userage
 */
function getUserAge($userData, $view)
{
    $userBirthday = explode("-", $userData["billingaddress"]["birthday"]);
    $userAge = calculateAge($userBirthday[2], $userBirthday[1], $userBirthday[0]);
    if ($userAge && $userAge < 18) {
        $view->piRatepayInvoiceWarning = 'toyoung';
        $view->piRatepayRateWarning = 'toyoung';
        $view->piRatepayDebitWarning = 'toyoung';
        $view->pi_ratepay_toyoung = true;
    }
    return $userAge;
}

/**
 * puts an entry into the history db
 *
 * @param  String $orderNumber          Current orderNumber
 * @param  String $event                Current event
 * @param  String $articleName          Current article name
 * @param  String $articleNumber        Current article order number
 * @param  String $articleQuantity      Current article quantity
 */
function historyEntry($orderNumber,$event, $articleName, $articleNumber, $articleQuantity)
{
    $sql = "INSERT INTO `pi_ratepay_history`(`ordernumber`, `event`, `name`, `bestellnr`, `anzahl`)
            VALUES(?, ?, ?, ?, ?)";
    Shopware()->Db()->query($sql, array($orderNumber, $event, $articleName, $articleNumber ,$articleQuantity));
}

/**
 * sets orderstate of current order
 *
 * @param  String $orderNumber Current ordernumber
 * @param  String $status      Current orderstate
 */
function setOrderState($orderNumber, $status)
{
    $sql = "UPDATE `s_order` SET `status` = ? WHERE `ordernumber` = ?";
    Shopware()->Db()->query($sql, array((int)$status, $orderNumber));
}


/**
* Checks if billing address eqal shipping address and returns the differenzes
*
* @param Array  $piRatepayUser           Userdata
*
* @return Array $piRatepayDiff           Differences in Arrays
*/
function checkBillingEqualShipping($user, $view) {
    $userBillingAddress=array(
        'company'       => $user['billingaddress']['company'],
        'salutation'    => $user['billingaddress']['salutation'],
        'firstname'     => $user['billingaddress']['firstname'],
        'lastname'      => $user['billingaddress']['lastname'],
        'street'        => $user['billingaddress']['street'],
        'streetnumber'  => $user['billingaddress']['streetnumber'],
        'zipcode'       => $user['billingaddress']['zipcode'],
        'city'          => $user['billingaddress']['city'],
        'countryID'     => $user['billingaddress']['countryID']
    );
    $userShippingAddress=array(
        'company'       => $user['shippingaddress']['company'],
        'salutation'    => $user['shippingaddress']['salutation'],
        'firstname'     => $user['shippingaddress']['firstname'],
        'lastname'      => $user['shippingaddress']['lastname'],
        'street'        => $user['shippingaddress']['street'],
        'streetnumber'  => $user['shippingaddress']['streetnumber'],
        'zipcode'       => $user['shippingaddress']['zipcode'],
        'city'          => $user['shippingaddress']['city'],
        'countryID'     => $user['shippingaddress']['countryID']
    );
    $diff = array_diff($userBillingAddress,$userShippingAddress);
    if(!empty($diff)){
       $view->piRatepayInvoiceWarning = 'address';
       $view->piRatepayRateWarning = 'address';
       $view->piRatepayDebitWarning = 'address';
       $view->pi_ratepay_address = true;
       Shopware()->Session()->ratePAYadressDiff =true;
    }
    else{
        Shopware()->Session()->ratePAYadressDiff =false;
    }
}

/**
 * Checks if B2B is allowed and if user has entered a company
 *
 * * @param Array  $piRatepayUser Userdata
 */
function checkB2BAllowed($user, $view) {
    $config = Shopware()->Plugins()->Frontend()->PigmbhRatePAYPayment()->Config();
    $b2b_invoice = $config->b2b_invoice;
    $b2b_rate = $config->b2b_rate;
    $b2b_debit = $config->b2b_debit;
    if($user["billingaddress"]["company"] != '' && $b2b_invoice == false) {
        $view->piRatepayInvoiceWarning = 'b2b';
        $view->pi_ratepay_b2b_invoice = true;
        Shopware()->Session()->ratepayB2BInvoice = true;
    } else {
        $view->pi_ratepay_b2b_invoice = false;
        Shopware()->Session()->ratepayB2BInvoice = false;
    }
    if($user["billingaddress"]["company"] != '' && $b2b_rate == false) {
        $view->piRatepayRateWarning = 'b2b';
        $view->pi_ratepay_b2b_rate = true;
        Shopware()->Session()->ratepayB2BRate = true;
    } else {
        $view->pi_ratepay_b2b_rate = false;
        Shopware()->Session()->ratepayB2BRate = false;
    }
    if($user["billingaddress"]["company"] != '' && $b2b_debit == false) {
        $view->piRatepayDebitWarning = 'b2b';
        $view->pi_ratepay_b2b_debit = true;
        Shopware()->Session()->ratepayB2BDebit = true;
    } else {
        $view->pi_ratepay_b2b_debit = false;
        Shopware()->Session()->ratepayB2BDebit = false;
    }
}
