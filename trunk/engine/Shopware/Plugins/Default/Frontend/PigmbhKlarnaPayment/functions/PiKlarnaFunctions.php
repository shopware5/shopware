<?php

/**
 * Creates an instance of the Klarna Object
 *
 * @param   String $myordernumber   Current ordernumber
 *
 * @return  Object $k               Klarnaobject
 */
function piKlarnaCreateKlarnaInstance($myordernumber=false,$isoCode=false)
{
    $piKlarnaConfig = array();
    $shopId = false;
    $shopesecret = false;
    $countryIso = "";
    $piKlarnaMyMode = 0;
    $piKlarnaConfig = Shopware()->Plugins()->Frontend()->PigmbhKlarnaPayment()->Config();
    require_once dirname(__FILE__) . '/../api/Klarna.php';
    require_once dirname(__FILE__) . '/../api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc';
    require_once dirname(__FILE__) . '/../api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
    curl_setopt($ch, CURLOPT_CAINFO, "pathto/cacert.pem");
    if ($myordernumber) {
        $sql = "Select shop_id FROM Pi_klarna_payment_multistore WHERE order_number = ?";
        $shopId = Shopware()->Db()->fetchOne($sql, array($myordernumber));
        $sql = "Select secret FROM Pi_klarna_payment_multistore WHERE order_number = ?";
        $shopesecret = Shopware()->Db()->fetchOne($sql, array($myordernumber));
        $sql = "Select liveserver FROM Pi_klarna_payment_multistore WHERE order_number = ?";
        $piKlarnaLivemode = Shopware()->Db()->fetchOne($sql, array($myordernumber));
        $sql = "SELECT id FROM s_order WHERE ordernumber = ?";
        $orderId = Shopware()->Db()->fetchOne($sql, array($myordernumber));
        $sql = "SELECT countryID FROM s_order_billingaddress WHERE orderID = ?";
        $countryId = Shopware()->Db()->fetchOne($sql, array((int)$orderId));
        $sql = "SELECT countryiso FROM s_core_countries WHERE id = ?";
        $countryIso = Shopware()->Db()->fetchOne($sql, array((int)$countryId));
        if ($piKlarnaLivemode == true) $piKlarnaMyMode = Klarna::LIVE;
        else $piKlarnaMyMode = Klarna::BETA;
    }
    else {
        if ($piKlarnaConfig->pi_klarna_liveserver == true) $piKlarnaMyMode = Klarna::LIVE;
        else $piKlarnaMyMode = Klarna::BETA;
        $shopId = $piKlarnaConfig->pi_klarna_Merchant_ID;
        $shopesecret = $piKlarnaConfig->pi_klarna_Secret;
    }
    $k = new Klarna();
    if(!$shopId) throw new Exception("Es ist keine PClass vorhanden. Bitte kontaktieren Sie den H&auml;ndler");
    if(!$shopesecret) throw new Exception("Es ist kein Secret vorhanden. Bitte kontaktieren Sie den H&auml;ndler");
    $k->config(
        $shopId,
        $shopesecret,
        KlarnaCountry::DE,
        KlarnaLanguage::DE,
        KlarnaCurrency::EUR,
        $piKlarnaMyMode,
        'json',
        dirname(__FILE__) . '/../classes/Pclass_' .  $shopId . '.php',
        true,
        true
    );
    if($myordernumber){
        $k->setCountry($countryIso);
    }
    return $k;
}

/**
 * Fetches PClass
 *
 * @param   String $piKlarnaEid     ShopId
 * @param   String $piKlarnaSecret  ShopSecret
 *
 * @return  String                  Result message
 */
function piKlarnaFetchKlarnaPClass($piKlarnaEid, $piKlarnaSecret)
{
    if ($piKlarnaEid && $piKlarnaSecret) {
        require_once dirname(__FILE__) . '/../api/Klarna.php';
        require_once dirname(__FILE__) . '/../api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc';
        require_once dirname(__FILE__) . '/../api/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        $piKlarnaConfig = Shopware()->Plugins()->Frontend()->PigmbhKlarnaPayment()->Config();
        
//        $sql = "SELECT shopID FROM s_core_plugin_configs WHERE name LIKE 'pi_klarna_Merchant_ID' AND value like ?";
//        $piKlarnaShopId = Shopware()->Db()->fetchOne($sql, array("%" . $piKlarnaEid . "%"));
//        if($piKlarnaShopId){
//        $sql                  = "SELECT value FROM s_core_plugin_configs WHERE name LIKE 'pi_klarna_liveserver' AND shopID = ?";
//        $piKlarnaLivecheck    = $piKlarnaConfig->pi_klarna_liveserver;
//        $piKlarnaLivecheckSub = substr($piKlarnaLivecheck, -3, -2);

        
        if ($piKlarnaConfig->pi_klarna_liveserver) {
            $mymode = Klarna::LIVE;
        } else {
            $mymode = Klarna::BETA;
        }

        $countrys=Array(81,59,73,154,164,209);
        $currencys=Array(2,3,2,2,1,0);
        $languages=Array(28,37,97,101,27,138);
        $countryNames=Array('Deutschland','D&auml;nemark','Finnland','Niederlande','Norwegen','Schweden');
        $counter=0;
        $errortext="";
        $piKlarnaErrorText="";
        for($i=0;$i<count($countrys);$i++){
            $k = new Klarna();
            $k->config(
                $piKlarnaEid,
                $piKlarnaSecret,
                KlarnaCountry::DE,
                KlarnaLanguage::DE,
                KlarnaCurrency::EUR,
                $mymode,
                'json',
                dirname(__FILE__) . '/../classes/Pclass_' . $piKlarnaEid . '.php',
                true,
                true
            );
            try{
                if($i==0)$piKlarnaErrorText='Ratenzahlungsmodalit&auml;ten f&uuml;r folgende Länder gespeichert: <br />';
                $k->fetchPClasses($countrys[$i],$languages[$i],$currencys[$i]);
                $counter++;
                $piKlarnaErrorText .= "<b>" . $countryNames[$i] . "</b>";
                $piKlarnaErrorText .=", ";
            }
            catch(Exception $e){
                $errortext= $e->getMessage(). ' (#' . $e->getCode() . ')';
            }
        }
        $rest = substr($piKlarnaErrorText, 0, -2);
        if($counter==0)
            return $errortext;
        else
            return $rest;
    }

}
/**
 * get language pack
 *
 * @param    String $klarnaShopLang     Current Shop language
 * @return   Array  $pi_Klarna_lang     Array with texts
 */
function piKlarnaGetLanguage($klarnaShopLang)
{
    $pi_Klarna_lang = array();
    if ($klarnaShopLang == "sv") $klarnaShopLang = "se";
    $filename = dirname(__FILE__) . '/../language/' . $klarnaShopLang . '/locale.php';
    if (file_exists($filename)) {
        require $filename;
    }
    else {
        require dirname(__FILE__) . '/../language/de/locale.php';
    }
    return $pi_Klarna_lang;
}



/**
 * after every change the orerprice is calculated new and updated in the database.
 *
 * @param  String $piKlarnaInvoiceOrderNumber  Ordernumber where the changes are made.
 */
function piKlarnaCalculateNewAmount($piKlarnaInvoiceOrderNumber)
{
    $sql = "SELECT `net` FROM `s_order` WHERE ordernumber = ? LIMIT 1";
    $net = Shopware()->Db()->fetchOne($sql, array($piKlarnaInvoiceOrderNumber));
    $invoiceAmount = array();
    $dbAmount = "";
    $dbAmountNet = "";
    $data = array();
    if ($net == 0) {
        $sql = "SELECT
            SUM(det.price*det.`quantity`)+ord.invoice_shipping AS new_invoice_amount
            FROM `s_order_details` AS det
            LEFT JOIN `s_order` AS ord ON(ord.`ordernumber` = det.`ordernumber`)
            WHERE det.ordernumber = ?";
        $invoiceAmount = Shopware()->Db()->fetchAll($sql, array($piKlarnaInvoiceOrderNumber));
        $dbAmount = $invoiceAmount[0]['new_invoice_amount'];
        $invoiceAmountNet = 0;
        $sql = "SELECT (details.price * details.quantity) AS total,
            tax.tax,
            `order`.invoice_shipping_net
            FROM `s_order_details` AS details
            LEFT JOIN `s_core_tax` AS tax ON ( tax.id = details.taxID )
            LEFT JOIN `s_order` AS `order` ON ( `order`.ordernumber = details.ordernumber )
            WHERE details.ordernumber = ?";
        $data = Shopware()->Db()->fetchAll($sql, array($piKlarnaInvoiceOrderNumber));
        $invoiceShippingNet = $data[0]['invoice_shipping_net'];
        $data[0]['tax'] == null ? $tax = 19 : $tax = $data[0]['tax'];
        $plus = $data[0]['total'] / (100 + $tax) * 100;
        $invoiceAmountNet = $invoiceAmountNet + $plus;
        $invoiceAmountNet = $invoiceAmountNet + $invoiceShippingNet;
        $dbAmountNet = $invoiceAmountNet;
    }
    else {
        $sql = "SELECT
            SUM(det.price*det.`quantity`)+ord.invoice_shipping_net AS new_invoice_amount_net
            FROM `s_order_details` AS det
            LEFT JOIN `s_order` AS ord ON(ord.`ordernumber` = det.`ordernumber`)
            WHERE det.ordernumber = ?";
        $invoiceAmountNet = Shopware()->Db()->fetchAll($sql, array($piKlarnaInvoiceOrderNumber));
        $dbAmountNet = $invoiceAmountNet[0]['new_invoice_amount_net'];
        $invoiceAmountNet = 0;
        $sql = "SELECT (details.price * details.quantity) AS total,
            tax.tax,
            `order`.invoice_shipping
            FROM `s_order_details` AS details
            LEFT JOIN `s_core_tax` AS tax ON ( tax.id = details.taxID )
            LEFT JOIN `s_order` AS `order` ON ( `order`.ordernumber = details.ordernumber )
            WHERE details.ordernumber = ?";
        $data = Shopware()->Db()->fetchAll($sql, array($piKlarnaInvoiceOrderNumber));
        $invoiceShipping = $data[0]['invoice_shipping'];
        $data[0]['tax'] == null ? $tax = 19 : $tax = $data[0]['tax'];
        $plus = $data[0]['total'] / 100 * (100 + $tax);
        $invoiceAmountNet = $invoiceAmountNet + $plus;
        $invoiceAmountNet = $invoiceAmountNet + $invoiceShipping;
        $dbAmount = $invoiceAmountNet;
    }
    Shopware()->Db()->query(sprintf("
        UPDATE `s_order` SET
        `invoice_amount` = '%s',
        `invoice_amount_net` = '%s'
        WHERE `ordernumber` = '%s' LIMIT 1", $dbAmount, $dbAmountNet, $piKlarnaInvoiceOrderNumber
    ));
}

/**
 * Changes the amount of the reservation when the article quantity changed,
 * articles are deleted or articles and vouchers are addet to the order
 *
 * @param  Float	$gesamtRechnungsPreisNeu	The amount the invoice is set to.
 * @param  String	$myTransactionId		The transaction id from the changed order.
 * @param  String	$partFlag			Checks if the amount is added or removed from the order and if the whole order is changed or only a part.
 *
 * @return Array	$myerror 			configured Klarna object with error message.
 */
function piKlarnaChangeReservation($gesamtRechnungsPreisNeu, $myTransactionId, $partFlag) {
    $sql = "SELECT ordernumber FROM s_order WHERE transactionID = ?";
    $piKlarnaOrderNumber = Shopware()->Db()->fetchOne($sql, array($myTransactionId));
    $k = piKlarnaCreateKlarnaInstance($piKlarnaOrderNumber);
    $rno = $myTransactionId;
    $myerror = array();
    $myerror['error'] = false;
    $myerror['errormessage'] = "";
    if ($partFlag == 'part') $myflag = KlarnaFlags::ADD_AMOUNT;
    elseif ($partFlag == 'deletearticle') {
        $gesamtRechnungsPreisNeu = $gesamtRechnungsPreisNeu * (-1);
        $myflag = KlarnaFlags::ADD_AMOUNT;
    }
    else $myflag = 0;
    try {
        $k->changeReservation($rno, $gesamtRechnungsPreisNeu, $myflag);
        return $myerror;
    }
    catch (Exception $e) {
        $myerror['error'] = true;
        $myerror['errormessage'] = $e->getMessage() . " (#" . $e->getCode() . ")";
        return $myerror;
    }
}

/**
 * Activates the reservation when the order or a part of it is send.
 * Saves the invoice on the webserver.
 *
 * @param  Object	$k			Configured Klarna object.
 * @param  String	$ordernumber		The ordernumber from the order the items are send from.
 * @param  String	$action			The transaction id from the changed order.
 * @param  Array	$articles		Array of articles that are send.
 *
 * @throws KlarnaException
 *
 * @return Array	$myerror 		Configured Klarna object with error message.
 */
function piKlarnaActivateReservation($k, $ordernumber, $action, $articles) {
    $piKlarnaConfig = array();
    $sql = "SELECT id FROM s_order WHERE ordernumber = ?";
    $orderId = Shopware()->Db()->fetchOne($sql, array($ordernumber));
    $sql = "SELECT * FROM s_order_billingaddress WHERE orderID = ?";
    $myuser = Shopware()->Db()->fetchRow($sql, array((int)$orderId));
    $sql = "SELECT countryiso FROM s_core_countries WHERE id = ?";
    $piKlarnaCountryIso = Shopware()->Db()->fetchOne($sql, array((int)$myuser["countryID"]));
    $piKlarnaConfig = Shopware()->Plugins()->Frontend()->PigmbhKlarnaPayment()->Config();
    $sql = "SELECT * FROM Pi_klarna_payment_user_data WHERE ordernumber = ?";
    $myKlarnaUser = Shopware()->Db()->fetchRow($sql, array($ordernumber));
    if ($piKlarnaCountryIso == 'DE' || $piKlarnaCountryIso == 'NL') {
        $piKlarnaStreet = $myuser["street"];
    } else {
        $piKlarnaStreet = $myuser["street"] . ' ' . $myuser["streetnumber"];
    }
    $myerror = array();
    $myerror['error'] = false;
    $myerror['errormessage'] = "&nbsp;";
    $addr = new KlarnaAddr(
        $myKlarnaUser["mail"],
        '',
        $myKlarnaUser["cellphone"],
        utf8_decode($myKlarnaUser["firstname"]),
        utf8_decode($myKlarnaUser["lastname"]),
        '',
        utf8_decode($piKlarnaStreet),
        $myKlarnaUser["zip"],
        utf8_decode($myKlarnaUser["city"]),
        getCountryCode($piKlarnaCountryIso),
        $myKlarnaUser["housenr"],
        utf8_decode($myuser["text4"])
    );
    
    if($piKlarnaCountryIso=='DE' || $piKlarnaCountryIso=='NL'){
      	$addr->setHouseNumber($myuser["streetnumber"]);
       	if($piKlarnaCountryIso=='NL' && $myuser["text4"]){
       	    $addr->setHouseExt(utf8_decode($myuser["text4"]));
        }
    }
    elseif($myuser["company"]){
     	$addr->setCompanyName(utf8_decode($myuser["company"]));
      	$addr->isCompany=true;
    }
    $k->setAddress(KlarnaFlags::IS_BILLING, $addr);
    $k->setAddress(KlarnaFlags::IS_SHIPPING, $addr);
    $k->setEstoreInfo($ordernumber);
    $sql = "SELECT transactionid FROM Pi_klarna_payment_order_data WHERE order_number = ?"; 
    $rno = Shopware()->Db()->fetchOne($sql, array($ordernumber));
    $sql = "SELECT payment_name FROM Pi_klarna_payment_order_data WHERE order_number = ?"; 
    $PigmbhKlarnaPaymentName = Shopware()->Db()->fetchOne($sql, array($ordernumber));
    if ($PigmbhKlarnaPaymentName == 'KlarnaInvoice') $PigmbhKlarnaPaymentFlag = KlarnaPClass::INVOICE;
    else {
        try {
            $pclasses = $k->getPClasses(null);
            $PigmbhKlarnaPaymentFlag = $pclasses[0]->getId();
        }
        catch (Exception $e) {
            $myerror['error'] = true;
            $myerror['errormessage'] = $e->getMessage() . " (#" . $e->getCode() . ")";
            return $myerror;
        }
    }
    if ($piKlarnaConfig->pi_klarna_Testmode == true) $piKlarnaTestmode = KlarnaFlags::TEST_MODE;
    else $piKlarnaTestmode = 0;
    if ($myuser["salutation"] == "mr") $mygender = KlarnaFlags::MALE;
    else $mygender = KlarnaFlags::FEMALE;
    $sql = "SELECT birthday FROM Pi_klarna_payment_user_data WHERE ordernumber = ?"; 
    $myBirthday = Shopware()->Db()->fetchOne($sql, array($ordernumber));
    try {
        $result = $k->activateReservation(
            $myBirthday ,
            $rno,
            $mygender,
            '',
            $piKlarnaTestmode,
            $PigmbhKlarnaPaymentFlag
        );
        $invno = $result[1];
        $invNo = $invno;
        $k2 = piKlarnaCreateKlarnaInstance($ordernumber);
        if ($piKlarnaConfig->pi_klarna_liveserver == true) $testvar = 'true';
        else $testvar = 'false';
        try {
            $result = $k2->invoiceAmount($invNo);
            if ($action == 'last') $method = 'Letzte Rechnung';
            elseif ($action == 'complete') $method = 'Komplette Rechnung';
            else $method = 'Teilrechnung';
            $sql = "INSERT INTO `Pi_klarna_payment_bills`(`method`, `order_number`, `invoice_amount`, `invoice_number`, `liveserver`)
                    VALUES(?, ?, ?, ?, ?)";
            Shopware()->Db()->query($sql, array($method, $ordernumber, $result, $invno, $testvar));
            for ($i = 0; $i < sizeof($articles); $i++) {
                $myarticlename = $articles[$i]['name'];
//                $myarticlename = str_replace("'", "\'", $articles[$i]['name']);
                $sql = "INSERT INTO `Pi_klarna_payment_bills_articles`
                        (`order_number`, `invoice_number`, `name`, `bestell_nr`, `anzahl`, `einzelpreis`)
                        VALUES(?, ?, ?, ?, ? ,?)";
                Shopware()->Db()->query($sql, array(
                    $ordernumber, 
                    $invno, 
                    $myarticlename, 
                    $articles[$i]['bestell_nr'], 
                    (int)$articles[$i]['anzahl'], 
                    $articles[$i]['einzelpreis']
               ));
            }
            $ch = "";
            if ($piKlarnaConfig->pi_klarna_liveserver == true) {
                $ch = curl_init('https://online.klarna.com/invoices/' . $invno . '.pdf');
            }
            else {
                $ch = curl_init('https://beta-test.klarna.com/invoices/' . $invno . '.pdf');
            }
            $fp = fopen('files/documents/' . $invno . '.pdf', 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            return $myerror;
        }
        catch (Exception $e) {
            $myerror['error'] = true;
            $myerror['errormessage'] = $e->getMessage() . " (#" . $e->getCode() . ")";
            return $myerror;
        }
    }
    catch (Exception $e) {
        $myerror['error'] = true;
        $myerror['errormessage'] = $e->getMessage() . " (#" . $e->getCode() . ")";
        return $myerror;
    }
}

function piKlarnaCheckPendingOrders(){
    $piKlarnaPendingId=piKlarnaGetPendingStatusId();
    $sql                   = "SELECT transactionID, ordernumber FROM s_order WHERE cleared = ?";
    $piKlarnaPendingOrders = Shopware()->Db()->fetchAll($sql, array((int)$piKlarnaPendingId));
    $cleared = $piKlarnaPendingId; 
    for ($i = 0; $i < sizeof($piKlarnaPendingOrders); $i++) {
        try {
            $k = piKlarnaCreateKlarnaInstance($piKlarnaPendingOrders[$i]['ordernumber']);
            $result = $k->checkOrderStatus($piKlarnaPendingOrders[$i]['transactionID'],0);
            if ($result == KlarnaFlags::ACCEPTED) {
                $cleared=piKlarnaGetAcceptedStatusId();
            }
            else if ($result == KlarnaFlags::DENIED) {
                $cleared=piKlarnaGetDeclinedStatusId();
            }
            // otherwise use the default value

            $sql = "UPDATE s_order SET cleared = ? WHERE transactionID = ?";
            Shopware()->Db()->query($sql, array((int)$cleared,$piKlarnaPendingOrders[$i]['transactionID']));
        }
        catch (Exception $e) {
            $cleared=piKlarnaGetDeclinedStatusId();
            $sql = "UPDATE s_order SET cleared = ? WHERE transactionID = ?";
            Shopware()->Db()->query($sql, array((int)$cleared,$piKlarnaPendingOrders[$i]['transactionID']));
        }
    }
}

/**
 * Gets ordernumber with transaction id
 *
 * @param String $transactionId     Current transaction id
 *
 * @return String $ordernumber      Ordernumber
 */
function piKlarnaGetOrdernumberByTransactionId($transactionId)
{
   $sql = "SELECT ordernumber FROM s_order WHERE transactionID = ?";
   $ordernumber = Shopware()->Db()->fetchOne($sql, array($transactionId));
   return $ordernumber;
}

/**
 * Gets Transaction Id with ordernumber
 *
 * @param  String $ordernumber       Ordernumber
 *
 * @return String $transactionId     Current transaction id
 */
function piKlarnaGetTransactionIdByOrdernumber($ordernumber)
{
   $sql = "SELECT transactionID FROM s_order WHERE ordernumber = ?";
   $transactionId = Shopware()->Db()->fetchOne($sql, array($ordernumber));
   return $transactionId;
}


/**
* Gets ID of Klarna pending state
*
* @return Int $status  Status
*/
function piKlarnaGetPendingStatusId() {
    $sql = "SELECT id FROM s_core_states WHERE description LIKE '<span style=\"color:orange\">Zahlung wird von Klarna gepr&uuml;ft</span>'";
    $status = Shopware()->Db()->fetchOne($sql);
    return $status;
}

/**
* Gets ID of Klarna accepted state
*
* @return Int $status  Status
*/
function piKlarnaGetAcceptedStatusId() {
    $sql = "SELECT id FROM s_core_states WHERE description LIKE '<span style=\"color:green\">Zahlung von Klarna akzeptiert</span>'";
    $status = Shopware()->Db()->fetchOne($sql);
    return $status;
}

 /**
* Gets ID of Klarna declined state
*
* @return Int $status  Status
*/
function piKlarnaGetDeclinedStatusId() {
    $sql = "SELECT id FROM s_core_states WHERE description LIKE '<span style=\"color:red\">Zahlung von Klarna nicht akzeptiert</span>'";
    $status = Shopware()->Db()->fetchOne($sql);
    return $status;
}

/**
* Gets ID of Klarna reservation canceled state
*
* @return Int $status  Status
*/
function piKlarnaGetReserverationCanceledStatusId() {
    $sql = "SELECT id FROM s_core_states WHERE description LIKE '<span style=\"color:red\">Reservierung abgebrochen</span>'";
    $status = Shopware()->Db()->fetchOne($sql);
    return $status;
}

 /**
* Gets ID of Klarna part reserved state
*
* @return Int $status  Status
*/
function piKlarnaGetPartReservedStatusId() {
    $sql = "SELECT id FROM s_core_states WHERE description LIKE '<span style=\"color:orange\">Reservierung teilweise aktiviert</span>'";
    $status = Shopware()->Db()->fetchOne($sql);
    return $status;
}

/**
* Gets ID of Klarna complete reserved state
*
* @return Int $status  Status
*/
function piKlarnaGetCompleteReservedStatusId() {
    $sql = "SELECT id FROM s_core_states WHERE description LIKE '<span style=\"color:green\">Reservierung komplett aktiviert</span>'";
    $status = Shopware()->Db()->fetchOne($sql);
    return $status;
}

/**
* Gets ID of Klarna part return state
*
* @return Int $status  Status
*/
function piKlarnaGetPartReturnStatusId() {
    $sql = "SELECT id FROM s_core_states WHERE description LIKE '<span style=\"color:orange\">Teilweise retourniert</span>'";
    $status = Shopware()->Db()->fetchOne($sql);
    return $status;
}

/**
* Gets ID of Klarna complete return state
*
* @return Int $status  Status
*/
function piKlarnaGetCompleteReturnStatusId() {
    $sql = "SELECT id FROM s_core_states WHERE description LIKE '<span style=\"color:red\">Komplett retourniert</span>'";
    $status = Shopware()->Db()->fetchOne($sql);
    return $status;
}

/**
* Gets ID of Klarna part canceled state
*
* @return Array $statusArray  Status
*/
function piKlarnaGetAllStatusIds() {
    $statusArray=Array(
    	'pending' => piKlarnaGetPendingStatusId(),
        'accepted' => piKlarnaGetAcceptedStatusId(),
        'declined' => piKlarnaGetDeclinedStatusId(),
        'partSent' => piKlarnaGetPartReservedStatusId(),
    	'completeSent' => piKlarnaGetCompleteReservedStatusId(),
    	'partCanceled' => piKlarnaGetPartCanceledStatusId(),
    	'completeCanceled' => piKlarnaGetCompleteCancelStatusId(),
    	'partReturned' => piKlarnaGetPartReturnStatusId(),
    	'completeReturned' => piKlarnaGetCompleteReturnStatusId(),
    );
    return $statusArray;
}

/**
* Gets ID of Klarna part canceled state
*
* @return Int $status  Status
*/
function piKlarnaGetPartCanceledStatusId() {
    $sql = "SELECT id FROM s_core_states WHERE description LIKE '<span style=\"color:orange\">Teilweise storniert</span>'";
    $status = Shopware()->Db()->fetchOne($sql);
    return $status;
}

/**
* Gets ID of Klarna complete canceled state
*
* @return Int $status  Status
*/
function piKlarnaGetCompleteCancelStatusId() {
    $sql = "SELECT id FROM s_core_states WHERE description LIKE '<span style=\"color:red\">Komplett storniert</span>'";
    $status = Shopware()->Db()->fetchOne($sql);
    return $status;
}

/**
* Gets ID of Klarna invoice payment
*
* @return Int $piKlarnaInvoiceId  ID
*/
function piKlarnaGetInvoicePaymentId() {
    $sql = "SELECT id FROM s_core_paymentmeans WHERE name LIKE 'KlarnaInvoice'";
    $piKlarnaInvoiceId = Shopware()->Db()->fetchOne($sql);
    return $piKlarnaInvoiceId;
}

/**
* Gets ID of Klarna rate payment
*
* @return Int $piKlarnaRateId        ID
*/
function piKlarnaGetRatePaymentId() {
    $sql = "SELECT id FROM s_core_paymentmeans WHERE name LIKE 'KlarnaPartPayment'";
    $piKlarnaRateId = Shopware()->Db()->fetchOne($sql);
    return $piKlarnaRateId;
}

/**
* Gets ID of Klarna payments
*
* @return Array $PigmbhKlarnaPaymentIds     Payments IDs
*/
function piKlarnaGetPaymentIds() {
    $sql = "SELECT id FROM s_core_paymentmeans WHERE name IN ('KlarnaInvoice','KlarnaPartPayment')";
    $PigmbhKlarnaPaymentIds = Shopware()->Db()->fetchAll($sql);
    return $PigmbhKlarnaPaymentIds;
}

/**
* Gets ID of Klarna plugin
*
* @return Int $piKlarnaPluginId         Plugin ID
*/
function piKlarnaGetPluginId() {
    $sql = "SELECT id FROM s_core_plugins WHERE name LIKE 'PigmbhKlarnaPayment'";
    $piKlarnaPluginId = Shopware()->Db()->fetchOne($sql);
    return $piKlarnaPluginId;
}

/**
* Gets Dispatch ID of current order
*
* @param String $piKlarnaOrderNumber    Ordernumber
*
* @return Int   $piKlarnaDispatchId     Dispatch ID
*/
function piKlarnaGetDispatchId($piKlarnaOrderNumber) {
    $sql = "SELECT dispatchID FROM s_order WHERE ordernumber = ?";
    $piKlarnaDispatchId = Shopware()->Db()->fetchOne($sql, array($piKlarnaOrderNumber));
    return $piKlarnaDispatchId;
}

	/**
     * Returns the Klarna Invoice surcharge
     *
     * @param Array $piKlarnaUserdata Current User
     *
     * @return String
     */
function piKlarnaGetInvoiceSurcharge($piKlarnaUserdata){
    $sql = "SELECT surcharge FROM s_core_paymentmeans WHERE name LIKE 'KlarnaInvoice'";
    $piKlarnaSurcharge = Shopware()->Db()->fetchOne($sql);
    $piKlarnaBasket = Shopware()->Modules()->Basket()->sGetBasket();
    $PigmbhKlarnaPaymentprice = 0;
    for ($i = 0; $i < sizeof($piKlarnaBasket['content']); $i++) {
        if ($piKlarnaBasket['content'][$i]['ordernumber'] == 'sw-payment')
             $PigmbhKlarnaPaymentprice = $piKlarnaBasket['content'][$i]['priceNumeric'];
    }
    if ($piKlarnaSurcharge == 0) {
        $sql = "SELECT debit_percent FROM s_core_paymentmeans WHERE name LIKE 'KlarnaInvoice'";
        $piKlarnaDebitPercent = Shopware()->Db()->fetchOne($sql); 
        $piKlarnaAmount = Shopware()->Modules()->Basket()->sgetAmount();
        $piKlarnaSurchargePrice = (($piKlarnaAmount["totalAmount"] - $PigmbhKlarnaPaymentprice) / 100 ) * $piKlarnaDebitPercent;
        $piKlarnaSurcharge = number_format($piKlarnaSurchargePrice, 2, ',', '.');
    }
    return $piKlarnaSurcharge;
}


	/**
     * Returns the surcharge
     *
     *
     * @return double
     */
function piKlarnaGetSurcharge(){
    $piKlarnaBasket = Shopware()->Modules()->Basket()->sGetBasket();
    for ($i = 0; $i < sizeof($piKlarnaBasket['content']); $i++) {
        if ($piKlarnaBasket['content'][$i]['ordernumber'] == 'sw-payment' 
            || $piKlarnaBasket['content'][$i]['ordernumber'] == 'sw-payment-absolute')
        {
             return $piKlarnaBasket['content'][$i]['priceNumeric'];
        }
    }
}

    /**
     * Returns the tax of the current article
     *
     * @param Array $klarnaUser Current User
     *
     * @return String
     */
    function getBillingCountry($klarnaUser)
    {
    	$sql = "SELECT countryiso FROM s_core_countries WHERE id = ?";
        $piKlarnaCountryIso = Shopware()->Db()->fetchOne($sql,array((int)$klarnaUser["billingaddress"]["countryID"]));
    	return $piKlarnaCountryIso;
    }

    /**
     * Returns the country as a two letter representation.
     *
     * @param $piKlarnaCountryIso Country iso
     * @throws KlarnaException
     * @return string  E.g. 'de', 'dk', ...
     */
    function getCountryCode($piKlarnaCountryIso) {
        switch($piKlarnaCountryIso) {
            case 'DE':
                return KlarnaCountry::DE;
            case 'DK':
                return KlarnaCountry::DK;
            case 'FI':
                return KlarnaCountry::FI;
            case 'NL':
                return KlarnaCountry::NL;
            case 'NO':
                return KlarnaCountry::NO;
            case 'SE':
                return KlarnaCountry::SE;
            default:
                throw new KlarnaException('Error in' . __METHOD__ . ': Unknown country! ('.$piKlarnaCountryIso.')');
        }
    }

    /**
     * Returns the country as a two letter representation.
     *
     * @param $klarnaShopLang 	shop language
     * @return Bool
     */
    function checkKlarnaCountrys($klarnaShopLang) {
    	if( $klarnaShopLang == 'de'
            || $klarnaShopLang == 'dk'
            || $klarnaShopLang == 'fi'
            || $klarnaShopLang == 'nl'
            || $klarnaShopLang == 'no'
            || $klarnaShopLang == 'se'
    	) return true;
    	else return false;
    }

    /**
     * Returns the country from currency
     *
     * @param $klarnaShopLang 	shop language
     * @return Bool
     */
    function checkKlarnaCountryCurrencys() {
        switch(Shopware()->Currency()->getShortName()) {
            case 'EUR':
                return 'de';
            case 'DKK':
                return 'dk';
            case 'NOK':
                return 'no';
            case 'SEK':
                return 'se';
            default:
                return false;
        }
    }

    /**
     * checks if selected currency fits to billing country
     *
     * @param $klarnaUserCountry 	user language
     * @return Bool
     */
    function checkKlarnaCurrency($klarnaUserCountry) {
    	$currency=Shopware()->Currency()->getShortName();
    	if(
            (($klarnaUserCountry == 'de' || $klarnaUserCountry == 'nl' || $klarnaUserCountry == 'fi') && $currency=='EUR')
            || ($klarnaUserCountry == 'dk'  && $currency=='DKK')
            || ($klarnaUserCountry == 'no'  && $currency=='NOK')
            || ($klarnaUserCountry == 'se'  && $currency=='SEK')
    	){
            return true;
    	}
    	else return false;
    }


/**
* Checks if billing address eqal shipping address and returns the differenzes
*
* @param Array  $piKlarnaUser           Userdata
*
* @return Array $piKlarnaDiff           Differences in Arrays
*/
function piKlarnaCheckBillingEqalShipping($piKlarnaUser) {
    $piKlarnaUserBillingAddress=array(
        'salutation'    => $piKlarnaUser['billingaddress']['salutation'],
        'firstname'     => utf8_decode($piKlarnaUser['billingaddress']['firstname']),
        'lastname'      => utf8_decode($piKlarnaUser['billingaddress']['lastname']),
        'street'        => $piKlarnaUser['billingaddress']['street'],
        'streetnumber'  => $piKlarnaUser['billingaddress']['streetnumber'],
        'zipcode'       => $piKlarnaUser['billingaddress']['zipcode'],
        'city'          => utf8_decode($piKlarnaUser['billingaddress']['city']),
        'countryID'     => $piKlarnaUser['billingaddress']['countryID']
    );
    $piKlarnaUserShippingAddress=array(
        'salutation'    => $piKlarnaUser['shippingaddress']['salutation'],
        'firstname'     => utf8_decode($piKlarnaUser['shippingaddress']['firstname']),
        'lastname'      => utf8_decode($piKlarnaUser['shippingaddress']['lastname']),
        'street'        => $piKlarnaUser['shippingaddress']['street'],
        'streetnumber'  => $piKlarnaUser['shippingaddress']['streetnumber'],
        'zipcode'       => $piKlarnaUser['shippingaddress']['zipcode'],
        'city'          => utf8_decode($piKlarnaUser['shippingaddress']['city']),
        'countryID'     => $piKlarnaUser['shippingaddress']['countryID']
    );
    $piKlarnaDiff = array_diff($piKlarnaUserBillingAddress,$piKlarnaUserShippingAddress);
    return $piKlarnaDiff;
}