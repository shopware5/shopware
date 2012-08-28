<?php
class Shopware_Controllers_Frontend_PiPaymentKlarna extends Shopware_Controllers_Frontend_Payment
{
    
    private $ordernumber;
    /**
     * The outgoing client encoding.
     * Please note! All input data strings should be encoded in accordance with ISO-8859-1
     * 
     * @see http://integration.klarna.com/en/api/download-api
     * @var string
     */
    protected $_clientEncoding = 'ISO-8859-1';
    /**
     * The encoding used by the shop system.
     * 
     * @var string
     */
    protected $_encoding = 'UTF-8';
    
    /**
     * Index action method
     */
    public function indexAction()
    {
        if ($this->getPaymentShortName() == 'KlarnaInvoice' || $this->getPaymentShortName() == 'KlarnaPartPayment'){
            if(Shopware()->Session()->klarnaAgb == true){
                return $this->redirect(array('controller' => 'checkout', 'action' => 'confirm'));
            }else{
                return $this->redirect(array('action' => 'KlarnaPayment')); 
            }
        }  
        else{
            return $this->redirect(array('controller' => 'checkout'));
        }
    }
    
    /**
     * Klarna Invoice action method
     */
    public function KlarnaPaymentAction()
    {
        try {
            $pclassId = 0;
            $pclasses = array();
            $k = piKlarnaCreateKlarnaInstance();
            $this->addKlarnaArticles($k);
            $this->addKlarnaShippingCosts($k);
            $this->addKlarnaAddress($k);
            $this->addKlarnaComment($k);
            echo $k->checkoutHTML();
            if($this->getPaymentShortName() == 'KlarnaInvoice'){
                $pclasses = $k->getPClasses(null);
                $pclassId = -1;
                $pclasses = $pclasses[0];
            }
            else{
                $pclasses = $k->getPClasses();   
                if(!$pclasses){
                    $piKlarnaLang = array();
                    $piKlarnaLang = piKlarnaGetLanguage(Shopware()->Locale()->getLanguage());
                    throw new Exception($piKlarnaLang['rate']['noPclass'],777);
                }
                $pclassId = $pclasses[0]->getId();
                $pclasses = $pclasses[0];
                    
            }
            $result = $this->reserveAmount($k,$pclassId);
            $invno = $result[0];
            $hash = $this->createPaymentUniqueId();
            if ($result[1] == KlarnaFlags::PENDING || $result[1] == KlarnaFlags::ACCEPTED) {
                $this->saveOrder($invno, $hash);
                $myordernumber=piKlarnaGetOrdernumberByTransactionId($invno);
                $this->ordernumber = $myordernumber;
                if ($result[1] == KlarnaFlags::ACCEPTED) {
                    $this->setAcceptedStatus();
                } elseif ($result[1] == KlarnaFlags::PENDING) {
                    $this->setPendingStatus();
                }
                $this->saveKlarnaOrder($invno, $pclasses, $hash);
            }
        }
        catch (Exception $e) {
            if ($e->getCode() == 2102) {
                Shopware()->Session()->klarnaDenied = true;
            }
            $this->setDenied();

            Shopware()->Session()->sPaymentError = $this->_convertEncoding(htmlentities($e->getMessage())) . " (#" . $e->getCode() . ")";
            Shopware()->Session()->sPaymentErrorMethod = $this->getPaymentShortName();
            $this->view->sPaymentError = $e->getMessage() . " (#" . $e->getCode() . ")";
            $this->redirect(array('controller' => 'account', 'action' => 'payment', 'sTarget' => 'checkout'));
        }
    }

    /**
     * Adds article to Klarna object
     *
     * @param Object $k         Klarna object
     *
     */
    public function addKlarnaArticles($k)
    {
        $piKlarnaBasket = $this->getBasket();
        for ($i = 0; $i < count($piKlarnaBasket["content"]); $i++) {
            $tax=$this->getTax($piKlarnaBasket["content"][$i]);
            $this->addKlarnaArticle($k,$piKlarnaBasket["content"][$i], $tax );
        }
    }

    /**
     * Adds article to Klarna object
     *
     * @param Object $k         Klarna object
     * @param Array  $article   Current article
     * @param Array  $tax       Current tax
     *
     */
    public function addKlarnaArticle($k,$article,$tax)
    {
        if($this->checkVoucher($article)){
            $sql = "SELECT description FROM s_emarketing_vouchers WHERE ordercode = ?";
            $article["articlename"] = Shopware()->Db()->fetchOne($sql, array($article["ordernumber"]));
        }
        $article["articlename"] = strip_tags($article["articlename"]);
        $k->addArticle(
             $article["quantity"],
             $article["ordernumber"],
             $this->_convertEncoding(html_entity_decode($article["articlename"])),
             $article["priceNumeric"],
             str_replace(",", ".", $tax['taxValue']),
             0,
             $tax['flag']
        );
    }

     /**
     * Checks if current article is a voucher
     *
     * @param Object $article      Current article
     *
     * @return bool
     */
    public function checkVoucher($article)
    {
        $sql = "SELECT ordercode FROM s_emarketing_vouchers";
        $piKlarnaVoucherOrderCode=Shopware()->Db()->fetchAll($sql);
        for ($i = 0; $i < count($piKlarnaVoucherOrderCode); $i++) {
            if( $piKlarnaVoucherOrderCode[$i]['ordercode']==$article["ordernumber"]){
                return true;
            }
        }
        return false;
    }


    /**
     * Adds shipping costs to Klarna object
     *
     * @param Object $k         Klarna object
     *
     */
    public function addKlarnaShippingCosts($k)
    {
       $piKlarnaBasket=$this->getBasket();
        if ($piKlarnaBasket['sShippingcostsTax']) {
            $piKlarnaShippingVat = str_replace(",", ".", $piKlarnaBasket['sShippingcostsTax']);
        } else {
            $piKlarnaShippingVat = 0;
        }
       $k->addArticle(
             1,
             'versand',
             'Versandkosten',
             str_replace(",", ".", $piKlarnaBasket['sShippingcostsWithTax']),
             $piKlarnaShippingVat,
             0,
             KlarnaFlags::INC_VAT + KlarnaFlags::IS_SHIPMENT
       );
    }

    /**
     * Adds address to Klarna object
     *
     * @param Object $k         Klarna object
     *
     */
    public function addKlarnaAddress($k)
    {
        $myuser             = $this->getUser();
        $piKlarnaCountryIso = getBillingCountry($myuser);
        if ($piKlarnaCountryIso == 'DE' || $piKlarnaCountryIso == 'NL') {
            $piKlarnaStreet = $myuser["billingaddress"]["street"];
        } else {
            $piKlarnaStreet = $myuser["billingaddress"]["street"] . ' ' .  $myuser["billingaddress"]["streetnumber"];
        }
        $addr = new KlarnaAddr($myuser["additional"]["user"]["email"], '', 
            $myuser["billingaddress"]["phone"], 
            $this->_convertEncoding($myuser["billingaddress"]["firstname"]), 
            $this->_convertEncoding($myuser["billingaddress"]["lastname"]), 
            '',
            $this->_convertEncoding($piKlarnaStreet), 
            $myuser["billingaddress"]["zipcode"], 
            $this->_convertEncoding($myuser["billingaddress"]["city"]), 
            getCountryCode($piKlarnaCountryIso)
        );
        if ($piKlarnaCountryIso == 'DE' || $piKlarnaCountryIso == 'NL') {
            $addr->setHouseNumber($myuser["billingaddress"]["streetnumber"]);
            if ($piKlarnaCountryIso == 'NL') {
                $addr->setHouseExt($this->_convertEncoding($myuser["billingaddress"]["text4"]));
            }
        } elseif ($myuser["billingaddress"]["company"]) {
            $addr->setCompanyName($this->_convertEncoding($myuser["billingaddress"]["company"]));
            $addr->isCompany = true;
        }

        $k->setCountry($piKlarnaCountryIso);
        $k->setAddress(KlarnaFlags::IS_BILLING, $addr);
        $k->setAddress(KlarnaFlags::IS_SHIPPING, $addr);
    }

    /**
     * Adds comment to Klarna object
     *
     * @param Object $k         Klarna object
     *
     */
    public function addKlarnaComment($k)
    {
       $usercomment = $this->_convertEncoding(Shopware()->Session()->sComment);
       $k->setComment($usercomment);
    }

    /**
     * Returns the tax of the current article
     *
     * @param $article
     *
     * @return array
     */
    public function getTax($article)
    {
        $piKlarnaCheckVoucher=$this->checkVoucher($article);
        $tax = array();
        if($piKlarnaCheckVoucher){
            $sql = "SELECT taxconfig FROM s_emarketing_vouchers WHERE ordercode = ?";
            $article['taxID']=Shopware()->Db()->fetchOne($sql, array($article["ordernumber"]));
            if(! $article['taxID']){
                 $article['taxID']=0;
            }
        }
        $sql = "SELECT tax FROM s_core_tax WHERE id = ?";
        $tax['taxValue'] = Shopware()->Db()->fetchOne($sql, array((int)$article['taxID']));
        $tax['flag'] = KlarnaFlags::INC_VAT;
        if ($article["ordernumber"] == 'sw-payment-absolute'
            || $article["ordernumber"] == 'sw-payment'
            || $article["ordernumber"] == 'PAYMENTSURCHARGEABSOLUTENUMBER'
        ) {
            $tax['flag'] = KlarnaFlags::INC_VAT + KlarnaFlags::IS_HANDLING;
            $tax['taxValue'] = round((($article["priceNumeric"] / $article["netprice"]) * 100) - 100);
        }
        if (!$tax['taxValue']) {
            $tax['taxValue'] = 0;
        }
        return $tax;
    }

    /**
     * Returns basket tax amount as float
     *
     * @return float
     */
    public function getTaxAmount()
    {
        $user = array();
        $user = $this->getUser();
        $basket = $this->getBasket();
        if (!empty($user['additional']['charge_vat'])) {
            return $basket['sAmountTax'];
        }
        else {
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

    /**
     * Sets status to pending
     */
    public function setPendingStatus()
    {
        $cleared=piKlarnaGetPendingStatusId();
        $sql = "UPDATE s_order SET cleared = ? WHERE ordernumber = ?";
        Shopware()->Db()->query($sql, array((int)$cleared, $this->ordernumber));
    }

    /**
     * Sets status to accepted
     */
    public function setAcceptedStatus()
    {
        $cleared=piKlarnaGetAcceptedStatusId();
        $sql = "UPDATE s_order SET cleared = ? WHERE ordernumber = ?";
        Shopware()->Db()->query($sql, array((int)$cleared, $this->ordernumber));
    }

    /**
     * Sets status to accepted
     *
     */
    public function setPaymentFees()
    {
        $sql = "UPDATE s_order_details
                SET quantity = 0
                WHERE ordernumber = ?
                AND
                (
                    articleordernumber ='sw-payment-absolute'
                    OR articleordernumber='sw-payment'
                )";
        Shopware()->Db()->query($sql, array($this->ordernumber));
        piKlarnaCalculateNewAmount($this->ordernumber);
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
        $sql = 'UPDATE s_order SET comment=? WHERE ordernumber=?';
        Shopware()->Db()->query($sql, array( $comment, $orderNumber));
    }

    /**
     * Disables Klarna snd sets payment Id
     */
    public function setDenied()
    {
       $myuser = array();
       $myuser = $this->getUser();
       $sql = "UPDATE s_user SET paymentID = ? WHERE id = ?";
       Shopware()->Db()->query($sql, array((int)Shopware()->Config()->Defaultpayment, (int)$myuser['billingaddress']['userID']));
    }

    /**
     * Saves orderdata in db
     *
     * @param String $invno             Current transaction id
     * @param Int    $pclassId          Current pclass id
     * @param String $hash              Payment hash
     */
    public function saveKlarnaOrder($invno, $pclassId, $hash){
        $this->saveKlarnaOrderData($invno);
        $this->saveKlarnaMultistoreData();
        $this->saveKlarnaHistoryData();
        $this->saveKlarnaUserData();
        $this->saveKlarnaOrderArticles();
        $this->saveKlarnaOrderShippingCosts();
        $this->saveKlarnaPclass($pclassId);
        Shopware()->Session()->sPaymentError = false;
        Shopware()->Session()->sPaymentErroMethod = false;
        $this->redirect(array('controller' => 'checkout', 'action' => 'finish', 'sUniqueID' => $hash));
    }

    /**
     * Saves orderdata in db
     *
     * @param String $invno             Current transaction id
     */
    public function saveKlarnaOrderData($invno)
    {
        $myuser = array();
        $myuser = $this->getUser();
        $sql = "INSERT INTO Pi_klarna_payment_order_data (payment_id, payment_name, order_number, transactionid, invoice_number)
                VALUES (?, ?, ?, ?, ?)";
        Shopware()->Db()->query($sql, array(
            (int)$myuser["additional"]["payment"]["id"],
            $myuser["additional"]["payment"]["name"],
            $this->ordernumber,
            $invno,
            ''
        ));
    }

    /**
     * Saves multistore data in db
     */
    public function saveKlarnaMultistoreData()
    {
        $piKlarnaConfig = array();
        $piKlarnaConfig = Shopware()->Plugins()->Frontend()->PigmbhKlarnaPayment()->Config();
        $sql = "INSERT INTO Pi_klarna_payment_multistore (order_number, shop_id, secret, liveserver)
                VALUES (?, ?, ?, ?)";
        Shopware()->Db()->query($sql, array(
            $this->ordernumber, 
            $piKlarnaConfig->pi_klarna_Merchant_ID, 
            $piKlarnaConfig->pi_klarna_Secret, 
            $piKlarnaConfig->pi_klarna_liveserver 
        ));
    }

    /**
     * Saves multistore data in db
     */
    public function saveKlarnaHistoryData()
    {
        $sql = "INSERT INTO Pi_klarna_payment_history (ordernumber, event) VALUES (?, '<b class=\"green\">Bestellung reserviert</b>')";
        Shopware()->Db()->query($sql, array($this->ordernumber));
    }

     /**
     * Saves userdata in db
     */
    public function saveKlarnaUserData()
    {
        $myuser = array();
        $myuser = $this->getUser();
        if ($myuser["billingaddress"]["salutation"] == "mr") {
            $mygender = 1;
        } else {
            $mygender = 0;
        }
        $mybirthday = explode("-", $myuser["billingaddress"]["birthday"]);
        $myKlarnaBirthday = $mybirthday[2] . $mybirthday[1] . $mybirthday[0];
        if ($this->getPaymentShortName() == 'KlarnaInvoice'){
            $method = 'KlarnaInvoice';
        } else {
            $method = 'KlarnaPartPayment';
        }
        $sql = "
            INSERT INTO Pi_klarna_payment_user_data
            (
                `user_id`,
                `method`,
                `birthday`,
                `cellphone`,
                `gender`,
                `street`,
                `housenr`,
                `firstname`,
                `lastname`,
                `zip`,
                `city`,
                `mail`,
                `ordernumber`
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        Shopware()->Db()->query($sql, array(
            (int)$myuser["billingaddress"]["userID"],
            $method,
            $myKlarnaBirthday,
            $myuser["billingaddress"]["phone"],
            $mygender,
            $myuser["billingaddress"]["street"],
            $myuser["billingaddress"]["streetnumber"],
            $myuser["billingaddress"]["firstname"],
            $myuser["billingaddress"]["lastname"],
            $myuser["billingaddress"]["zipcode"],
            $myuser["billingaddress"]["city"],
            $myuser["additional"]["user"]["email"],
            $this->ordernumber
        ));
    }

    /**
     * Saves order articles in db
     */
    public function saveKlarnaOrderArticles()
    {
        $piKlarnaBasket = $this->getBasket();
        for ($i = 0; $i < count($piKlarnaBasket["content"]); $i++) {
            $articlePrice = str_replace(",", ".", $piKlarnaBasket["content"][$i]["price"]);
            $piKlarnaCheckVoucher=$this->checkVoucher($piKlarnaBasket["content"][$i]);
            $articleName = "";
            if($piKlarnaCheckVoucher){
                $sql = "SELECT description FROM s_emarketing_vouchers WHERE ordercode = ?";
                $articleName=Shopware()->Db()->fetchOne($sql, array($piKlarnaBasket["content"][$i]["ordernumber"]));
            }
            else{
                $articleName=$piKlarnaBasket["content"][$i]["articlename"];
            }
            $articleName = html_entity_decode($articleName);
            $totalPrice = $articlePrice * $piKlarnaBasket["content"][$i]["quantity"];
            $sql = "
                INSERT INTO `Pi_klarna_payment_order_detail`
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
                    `bezahlstatus`
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, 18)
            ";
            Shopware()->Db()->query($sql,array(
                $this->ordernumber,
                $piKlarnaBasket["content"][$i]["articleID"],
                $piKlarnaBasket["content"][$i]["ordernumber"],
                (int)$piKlarnaBasket["content"][$i]["quantity"],
                $articleName,
                (double)$articlePrice,
                (double)$totalPrice,
                (int)$piKlarnaBasket["content"][$i]["quantity"],
                (int)$piKlarnaBasket["content"][$i]["quantity"],
            ));
        }
    }

    /**
     * Saves shipping costs in db
     */
    public function saveKlarnaOrderShippingCosts()
    {
        $sql = "SELECT invoice_shipping FROM s_order WHERE ordernumber = ?";
        $shippingprice = Shopware()->Db()->fetchOne($sql, array($this->ordernumber));
        $sql = "
            INSERT INTO `Pi_klarna_payment_order_detail`
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
                `bezahlstatus`
            )
            VALUES
            ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        Shopware()->Db()->query($sql, array(
            $this->ordernumber,
            $this->ordernumber."666",
            "versand_" . $this->ordernumber,
            1,
            'Versandkosten',
            (double)$shippingprice,
            (double)$shippingprice,
            1,
            1,
            18
        ));
    }

    /**
     * Saves Pclass data in Database
     *
     * @param Array $pclass 			Array with Pclass
     */
    public function saveKlarnaPclass($pclass){
    	if($this->getPaymentShortName()=='KlarnaPartPayment'){
            $sql = "
                INSERT INTO `Pi_klarna_payment_pclass`
                (
                    `ordernumber`,
                    `pclassid`,
                    `eid`,
                    `description`,
                    `months`,
                    `startfee`,
                    `invoicefee`,
                    `interestrate`,
                    `minamount`,
                    `country`,
                    `type`,
                    `expire`
                )
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            Shopware()->Db()->query($sql, array(
                $this->ordernumber, 
                $pclass->getId(),
                $pclass->getEid(),
                $pclass->getDescription(),
                $pclass->getMonths(),
                $pclass->getStartfee(),
                $pclass->getInvoicefee(),
                $pclass->getInterestrate(),
                $pclass->getMinamount(),
                $pclass->getCountry(),
                $pclass->getType(),
                $pclass->getExpire()
            ));
        } else {
            $sql = "INSERT INTO `Pi_klarna_payment_pclass` (`ordernumber`, `pclassid`) VALUES ( ?, ?)";
            Shopware()->Db()->query($sql, array($this->ordernumber, '-1'));
        }
    }

    /**
     * reserves the order at Klarna
     *
     * @param Object $k             Klarna object
     * @param int    $pclassId      Current Pclass
     *
     * @return Array $result    Response
     */
    public function reserveAmount($k,$pclassId)
    {
        $myuser = $this->getUser();
        $piKlarnaCountryIso = getBillingCountry($myuser) ;
        $piKlarnaConfig = array();
        $myKlarnaPno = "";
        $piKlarnaConfig = Shopware()->Plugins()->Frontend()->PigmbhKlarnaPayment()->Config();
        if ($myuser["billingaddress"]["salutation"] == "mr") {
            $mygender = KlarnaFlags::MALE;
        } else {
            $mygender = KlarnaFlags::FEMALE;
        }
        if($piKlarnaCountryIso=="DE" || $piKlarnaCountryIso=="NL"){
            $mybirthday = explode("-", $myuser["billingaddress"]["birthday"]);
            $myKlarnaPno = $mybirthday[2] . $mybirthday[1] . $mybirthday[0];
        }
        else{
            $myKlarnaPno=$myuser["billingaddress"]["text4"];
        }
        if ($piKlarnaConfig->pi_klarna_Testmode == true) {
            $myTestmode = 2;
        } else {
            $myTestmode = 0;
        }
        $result=$k->reserveAmount($myKlarnaPno, $mygender, -1, $myTestmode, $pclassId);
        return $result;
    }

    /** Customer cancels reservation
     *
     * @param Enlight_Event_EventArgs $piKlarnaArgs
     * @return void
     */
    public function stornoOrderAction(Enlight_Event_EventArgs $piKlarnaArgs)
    {
        $piKlarnaUrl = $_SERVER['PHP_SELF'];
        $substr = substr(
                $piKlarnaUrl,
                strlen('Pi_Klarna,') + strpos($piKlarnaUrl, 'Pi_Klarna,'),
               (strlen($piKlarnaUrl) - strpos($piKlarnaUrl, '15719816655')) * (-1)
        );
        $sql = "SELECT transactionid FROM Pi_klarna_payment_order_data WHERE order_number = ?";
        $piKlarnaTransactionId = Shopware()->Db()->fetchOne($sql, array($substr));
        $k = piKlarnaCreateKlarnaInstance();
        try {
            $k->cancelReservation($piKlarnaTransactionId);
            $piKlarnaCompleteCancelStatusId=piKlarnaGetCompleteCancelStatusId();
            $piKlarnaReserverationCanceledStatusId=piKlarnaGetReserverationCanceledStatusId();
            $sql = "UPDATE Pi_klarna_payment_order_detail SET versandstatus = ? WHERE ordernumber = ?";
            Shopware()->Db()->query($sql, array((int)$piKlarnaCompleteCancelStatusId, $substr));
            $sql = "UPDATE s_order SET cleared = ?, status = ? WHERE ordernumber = ?";
            Shopware()->Db()->query($sql, array(
                (int)$piKlarnaReserverationCanceledStatusId,
                (int)$piKlarnaCompleteCancelStatusId,
                $substr
            ));
        }
        catch (Exception $e) {
            $this->view->sPaymentError = $e->getMessage() . " (#" . $e->getCode() . ")";
        }
        return $this->forward('orders', 'account');
    }

    /**
     * Sets the encoding for the client object.
     *
     * @param  string $encoding
     * @throws Enlight_Exception
     * @return \Shopware_Controllers_Frontend_PiPaymentKlarna
     */
    public function setEncoding($encoding)
    {
        if (!is_string($encoding)) {
            throw new Enlight_Exception('Invalid encoding specified');
        }
        $this->_clientEncoding = $encoding;
        return $this;
    }

    /**
     * Performs pre processing of all argument values.
     *
     * @param string $value
     * @return null|string
     */
    protected function _convertEncoding($value)
    {
        if (!is_string($value) || empty($value)) {
            return;
        } elseif($this->_clientEncoding != $this->_encoding) {
            $value = mb_convert_encoding($value, $this->_clientEncoding, $this->_encoding);
        }
        return $value;
    }
}
