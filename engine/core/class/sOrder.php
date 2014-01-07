<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 * Deprecated Shopware Class that handle frontend orders
 */
class sOrder
{	/**
     * Array with userdata
     *
     * @var array
     */
    public $sUserData;
    /**
     * Array with basketdata
     *
     * @var array
     */
    public $sBasketData;
    /**
     * Array with shipping / dispatch data
     *
     * @var array
     */
    public $sShippingData;
    /**
     * User comment to save within this order
     *
     * @var string
     */
    public $sComment;
    /**
     * Payment-mean object
     *
     * @var object
     */
    public $paymentObject;
    /**
     * Total amount net
     *
     * @var double
     */
    public $sAmountNet;
    /**
     * Total Amount
     *
     * @var double
     */
    public $sAmount;
    /**
     * Total Amount with tax (force)
     *
     * @var double
     */
    public $sAmountWithTax;
    /**
     * Shipppingcosts
     *
     * @var double
     */
    public $sShippingcosts;
    /**
     * Shippingcosts unformated
     *
     * @var double
     */
    public $sShippingcostsNumeric;
    /**
     * Shippingcosts net unformated
     *
     * @var double
     */
    public $sShippingcostsNumericNet;
    /**
     * Pointer to sSystem object
     *
     * @var object
     */
    public $sSYSTEM;
    /**
     * TransactionID (epayment)
     *
     * @var string
     */
    public $bookingId;
    /**
     * Ordernumber
     *
     * @var string
     */
    public $sOrderNumber;
    /**
     * ID of choosen dispatch
     *
     * @var int
     */
    public $dispatchId;
    /**
     * Random id to identify the order
     *
     * @var string
     */
    public $uniqueID;
    /**
     * Net order true /false
     *
     * @var bool
     */
    public $sNet; 	// Complete taxfree

    /**
     * Custom attributes
     *
     * @var string
     */
    public $o_attr_1, $o_attr_2,$o_attr_3,$o_attr_4,$o_attr_5,$o_attr_6;

    /**
     * Get a unique ordernumber
     * @access public
     * @return string ordernumber
     */
    public function sGetOrderNumber()
    {

        $sql = "/*NO LIMIT*/ SELECT number FROM s_order_number WHERE name='invoice' FOR UPDATE";
        $ordernumber = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql);
        $sql = "UPDATE s_order_number SET number=number+1 WHERE name='invoice'";
        $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
        $ordernumber += 1;

        $ordernumber = Enlight()->Events()->filter('Shopware_Modules_Order_GetOrdernumber_FilterOrdernumber', $ordernumber, array('subject'=>$this));
        return $ordernumber;
    }

    /**
     * Check each basketrow for instant downloads
     * @access public
     */
    public function sManageEsdOrder(&$basketRow, $orderID, $orderdetailsID)
    {
        $quantity = $basketRow["quantity"];
        $basketRow['assignedSerials'] = array();

        $sqlGetEsd = "
        SELECT s_articles_esd.id AS id, serials
        FROM s_articles_esd, s_articles_details
        WHERE s_articles_esd.articleID={$basketRow["articleID"]}
        AND articledetailsID=s_articles_details.id
        AND s_articles_details.ordernumber='{$basketRow["ordernumber"]}'
        ";

        $esdArticle = $this->sSYSTEM->sDB_CONNECTION->CacheGetRow($this->sSYSTEM->sCONFIG['sCACHEARTICLE'], $sqlGetEsd);
        if (!$esdArticle["id"]) {
            // ESD not found
            return;
        }

        if (!$esdArticle["serials"]) {
            // No serialnumber is needed
            $updateSerial = $this->sSYSTEM->sDB_CONNECTION->Execute("
                INSERT INTO s_order_esd
                (serialID, esdID, userID, orderID, orderdetailsID, datum)
                VALUES (0,{$esdArticle["id"]},".$this->sUserData["additional"]["user"]["id"].",$orderID,$orderdetailsID,now())");

            return;
        }

        $sqlCheckSerials = "
        SELECT s_articles_esd_serials.id AS id, s_articles_esd_serials.serialnumber as serialnumber
        FROM s_articles_esd_serials
        LEFT JOIN s_order_esd
        ON (s_articles_esd_serials.id = s_order_esd.serialID)
        WHERE
        s_order_esd.serialID IS NULL
        AND s_articles_esd_serials.esdID={$esdArticle["id"]}
        ";

        $availableSerials = $this->sSYSTEM->sDB_CONNECTION->GetAll($sqlCheckSerials);

        if ((count($availableSerials) <= $this->sSYSTEM->sCONFIG['esdMinSerials']) || count($availableSerials) <= $quantity) {
            // No serialnumber anymore, inform merchant
            $context = array(
                'sArticleName' => $basketRow["articlename"],
                'sMail'        => $this->sUserData["additional"]["user"]["email"],
            );

            $mail = Shopware()->TemplateMail()->createMail('sNOSERIALS', $context);

            if ($this->sSYSTEM->sCONFIG['sESDMAIL']) {
                $mail->addTo($this->sSYSTEM->sCONFIG['sESDMAIL']);
            } else {
                $mail->addTo($this->sSYSTEM->sCONFIG['sMAIL']);
            }

            $mail->send();
        }

        // Check if enough serials are available, if not, an email has been sent
        if (count($availableSerials) >= $quantity) {
            for ($i = 1; $i <= $quantity; $i++) {
                // Assign serialnumber
                $serialId = $availableSerials[$i-1]["id"];

                // Update basketrow
                $basketRow['assignedSerials'][] = $availableSerials[$i-1]["serialnumber"];

                $sql = "
                    INSERT INTO s_order_esd
                    (serialID, esdID, userID, orderID, orderdetailsID, datum)
                    VALUES ($serialId,{$esdArticle["id"]},".$this->sUserData["additional"]["user"]["id"].",$orderID,$orderdetailsID,now())
                    ";

                $updateSerial = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
            }
        }
    }

    /**
     * Delete temporary created order
     * @access public
     */
    public function sDeleteTemporaryOrder()
    {
        if (empty($this->sSYSTEM->sSESSION_ID)) return;
        $deleteWholeOrder = $this->sSYSTEM->sDB_CONNECTION->GetAll("
        SELECT * FROM s_order WHERE temporaryID = ? LIMIT 2
        ",array($this->sSYSTEM->sSESSION_ID));

        foreach ($deleteWholeOrder as $orderDelete) {
            $deleteOrder =  $this->sSYSTEM->sDB_CONNECTION->Execute("
            DELETE FROM s_order WHERE id = ?
            ",array($orderDelete["id"]));

            $deleteSubOrder = $this->sSYSTEM->sDB_CONNECTION->Execute("
            DELETE FROM s_order_details
            WHERE orderID=?
            ",array($orderDelete["id"]));
        }
    }

    /**
     * Create temporary order (for order cancelation reports)
     * @access public
     */
    public function sCreateTemporaryOrder()
    {
        $this->sShippingData["AmountNumeric"] = $this->sShippingData["AmountNumeric"] ? $this->sShippingData["AmountNumeric"] : "0";
        if (!$this->sShippingcostsNumeric) $this->sShippingcostsNumeric = "0";
        if (!$this->sBasketData["AmountWithTaxNumeric"]) $this->sBasketData["AmountWithTaxNumeric"] = $this->sBasketData["AmountNumeric"];

        // Check if tax-free
        if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
            $net = "1";
        } else {
            $net = "0";
        }

        $this->sBasketData["AmountNetNumeric"] = round($this->sBasketData["AmountNetNumeric"],2);
        if ($this->dispatchId) {
            $dispatchId = $this->dispatchId;
        } else {
            $dispatchId = "0";
        }

        $this->sBasketData["AmountNetNumeric"] = round($this->sBasketData["AmountNetNumeric"],2);

        if (empty($this->sSYSTEM->sCurrency["currency"])) $this->sSYSTEM->sCurrency["currency"] = "EUR";
        if (empty($this->sSYSTEM->sCurrency["factor"])) $this->sSYSTEM->sCurrency["factor"] = "1";

        $shop = Shopware()->Shop();
        $mainShop = $shop->getMain() !== null ? $shop->getMain() : $shop;

        $taxfree = "0";
        if (!empty($this->sNet)) {
            // Complete net delivery
            $net = "1";
            $this->sBasketData["AmountWithTaxNumeric"] = $this->sBasketData["AmountNetNumeric"];
            $this->sShippingcostsNumeric = $this->sShippingcostsNumericNet;
            $taxfree = "1";
        }
        if (empty($this->sBasketData["AmountWithTaxNumeric"])) $this->sBasketData["AmountWithTaxNumeric"] = '0';
        if (empty($this->sBasketData["AmountNetNumeric"])) $this->sBasketData["AmountNetNumeric"] = '0';


        $sql = "
            INSERT INTO s_order (
              ordernumber, userID, invoice_amount,invoice_amount_net, invoice_shipping,
              invoice_shipping_net, ordertime, status, paymentID, customercomment,
              net, taxfree, partnerID,temporaryID,referer,language,
              dispatchID,currency,currencyFactor,subshopID
            ) VALUES ('0',
                ?,
                ?,
                ?,
                ?,
                ?,
                now(),
                -1,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ";
        $insertOrder = $this->sSYSTEM->sDB_CONNECTION->Execute($sql, array(
            $this->sUserData["additional"]["user"]["id"],
            $this->sBasketData["AmountWithTaxNumeric"],
            $this->sBasketData["AmountNetNumeric"],
            $this->sShippingcostsNumeric,
            $this->sShippingcostsNumericNet,
            $this->sUserData["additional"]["user"]["paymentID"],
            $this->sComment,
            $net,
            $taxfree,
            (string) $this->sSYSTEM->_SESSION["sPartner"],
            $this->sSYSTEM->sSESSION_ID,
            (string) $this->sSYSTEM->_SESSION['sReferer'],
            $shop->getId(),
            $dispatchId,
            $this->sSYSTEM->sCurrency["currency"],
            $this->sSYSTEM->sCurrency["factor"],
            $mainShop->getId()
        ));

        $orderID = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();

        if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg() || (!$orderID || !$insertOrder)) {
            $this->sSYSTEM->E_CORE_ERROR("##sOrder-sTemporaryOrder-#01",$this->sSYSTEM->sDB_CONNECTION->ErrorMsg().$sql);
            die ("Could not create temporary order");
        }

        $position = 0;
        foreach ($this->sBasketData["content"] as $basketRow) {
            $position++;

            $amountRow = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($basketRow["priceNumeric"] * $basketRow["quantity"]);

            if (!$basketRow["price"]) $basketRow["price"] = "0,00";
            if (!$amountRow) $amountRow = "0,00";


            $basketRow["articlename"] = html_entity_decode($basketRow["articlename"]);
            $basketRow["articlename"] = strip_tags($basketRow["articlename"]);

            if (!$basketRow["itemInfo"]) {
                $priceRow = $basketRow["price"];
            } else {
                $priceRow = $basketRow["itemInfo"];
            }

            $basketRow["articlename"] = $this->sSYSTEM->sMODULES['sArticles']->sOptimizeText($basketRow["articlename"]);

            if (!$basketRow["esdarticle"]) $basketRow["esdarticle"] = "0";
            if (!$basketRow["modus"]) $basketRow["modus"] = "0";
            if (!$basketRow["taxID"]) $basketRow["taxID"] = "0";

            $sql = "
                INSERT INTO s_order_details (
                    orderID,
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
                    taxID,
                    tax_rate
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?
                );
            ";
            $this->sSYSTEM->sDB_CONNECTION->Execute($sql,array(
                $orderID,
                0,
                $basketRow["articleID"],
                $basketRow["ordernumber"],
                $basketRow["priceNumeric"],
                $basketRow["quantity"],
                $basketRow["articlename"],
                0,
                '0000-00-00',
                $basketRow["modus"],
                $basketRow["esdarticle"],
                $basketRow["taxID"],
                $basketRow["tax_rate"]
            ));
            $orderdetailsID = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();
            if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg() || !$orderID) {
                $this->sSYSTEM->E_CORE_ERROR("##sOrder-sTemporaryOrder-Position-#02",$this->sSYSTEM->sDB_CONNECTION->ErrorMsg());
                die ("Could not create temporary order - row");
            }
        } // For every artice in basket
        return;
    }

    /**
     * Finaly save order and send order confirmation to customer
     * @access public
     */
    public function sSaveOrder()
    {
        $this->sComment = stripslashes($this->sComment);
        $this->sComment = stripcslashes($this->sComment);

        $this->sShippingData["AmountNumeric"] = $this->sShippingData["AmountNumeric"] ? $this->sShippingData["AmountNumeric"] : "0";

        if (strlen($this->bookingId)>3) {
            $insertOrder = $this->sSYSTEM->sDB_CONNECTION->GetRow("
            SELECT id FROM s_order WHERE transactionID=? AND status != -1
            ",array($this->bookingId));
            if ($insertOrder["id"]) {
                return false;
            }
        }
        // Insert basic-data of the order
        $orderNumber = $this->sGetOrderNumber();
        $this->sOrderNumber = $orderNumber;

        if (!$this->sShippingcostsNumeric) $this->sShippingcostsNumeric = "0";

        if (!$this->sBasketData["AmountWithTaxNumeric"]) $this->sBasketData["AmountWithTaxNumeric"] = $this->sBasketData["AmountNumeric"];

        // Check if tax-free
        if (($this->sSYSTEM->sCONFIG['sARTICLESOUTPUTNETTO'] && !$this->sSYSTEM->sUSERGROUPDATA["tax"]) || (!$this->sSYSTEM->sUSERGROUPDATA["tax"] && $this->sSYSTEM->sUSERGROUPDATA["id"])) {
            $net = "1";
        } else {
            $net = "0";
        }

        if ($this->dispatchId) {
            $dispatchId = $this->dispatchId;
        } else {
            $dispatchId = "0";
        }

        $this->sBasketData["AmountNetNumeric"] = round($this->sBasketData["AmountNetNumeric"],2);

        if (empty($this->sSYSTEM->sCurrency["currency"])) $this->sSYSTEM->sCurrency["currency"] = "EUR";
        if (empty($this->sSYSTEM->sCurrency["factor"])) $this->sSYSTEM->sCurrency["factor"] = "1";

        $shop = Shopware()->Shop();
        $mainShop = $shop->getMain() !== null ? $shop->getMain() : $shop;

        $taxfree = "0";
        if (!empty($this->sNet)) {
            // Complete net delivery
            $net = "1";
            $this->sBasketData["AmountWithTaxNumeric"] = $this->sBasketData["AmountNetNumeric"];
            $this->sShippingcostsNumeric = $this->sShippingcostsNumericNet;
            $taxfree = "1";
        }

        //unset($this->sSYSTEM->_SESSION["sPartner"]);
        if (empty($this->sSYSTEM->_SESSION["sPartner"])) {
            //"additional"]["user"]
            $pid = $this->sUserData["additional"]["user"]["affiliate"];

            if (!empty($pid) && $pid != "0") {
                // Get Partner code
                $partner = $this->sSYSTEM->sDB_CONNECTION->GetOne("
                SELECT idcode FROM s_emarketing_partner WHERE id = ?
                ",array($pid));
            }
        } else {
            $partner = $this->sSYSTEM->_SESSION["sPartner"];
        }

        $sql = "
        INSERT INTO s_order (
            ordernumber, userID, invoice_amount,invoice_amount_net,
            invoice_shipping,invoice_shipping_net, ordertime, status,
            cleared, paymentID, transactionID, customercomment,
            net,taxfree, partnerID,temporaryID,referer,language,dispatchID,
            currency,currencyFactor,subshopID,remote_addr
        ) VALUES ('".$orderNumber."',
            ".$this->sUserData["additional"]["user"]["id"].",
            ".$this->sBasketData["AmountWithTaxNumeric"].",
            ".$this->sBasketData["AmountNetNumeric"].",
            ".floatval($this->sShippingcostsNumeric).",
            ".floatval($this->sShippingcostsNumericNet).",
            now(),
            0,
            17,
            ".$this->sUserData["additional"]["user"]["paymentID"].",
            '".$this->bookingId."',
            ".$this->sSYSTEM->sDB_CONNECTION->qstr($this->sComment).",
            $net,
            $taxfree,
            ".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $partner).",
            ".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->uniqueID).",
            ".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->sSYSTEM->_SESSION['sReferer']).",
            '".$shop->getId()."',
            '$dispatchId',
            '".$this->sSYSTEM->sCurrency["currency"]."',
            '".$this->sSYSTEM->sCurrency["factor"]."',
            '".$mainShop->getId()."',
            ".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $_SERVER['REMOTE_ADDR'])."
        )
        ";

        $sql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveOrder_FilterSQL', $sql, array('subject'=>$this));

        $insertOrder = $this->sSYSTEM->sDB_CONNECTION->Execute($sql);

        $orderID = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();

        if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg() || (!$orderID || !$insertOrder)) {
            mail($this->sSYSTEM->sCONFIG['sMAIL'],"Shopware Order Fatal-Error {$_SERVER["HTTP_HOST"]}",$this->sSYSTEM->sDB_CONNECTION->ErrorMsg().$sql);
            die("Fatal order failure, please try again later, order was not processed");
        }

        try {
            $paymentData = Shopware()->Modules()->Admin()->sGetPaymentMeanById($this->sUserData["additional"]["user"]["paymentID"], Shopware()->Modules()->Admin()->sGetUserData());
            $paymentClass = Shopware()->Modules()->Admin()->sInitiatePaymentClass($paymentData);
            if ($paymentClass) {
                $paymentClass->createPaymentInstance($orderID, $this->sUserData["additional"]["user"]["id"], $this->sUserData["additional"]["user"]["paymentID"]);
            }
        } catch (\Exception $e) {
            //Payment method code failure
        }

        //new attribute table with shopware 4
        $attributeSql = "INSERT INTO s_order_attributes (orderID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
                VALUES (
                    " . $orderID  .",
                    ".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_1).",
                    ".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_2).",
                    ".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_3).",
                    ".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_4).",
                    ".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_5).",
                    ".$this->sSYSTEM->sDB_CONNECTION->qstr((string) $this->o_attr_6)."
                )";
        $attributeSql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveOrderAttributes_FilterSQL', $attributeSql, array('subject'=>$this));
        $this->sSYSTEM->sDB_CONNECTION->Execute($attributeSql);

        // add attributes to order
        $sql = 'SELECT * FROM s_order_attributes WHERE orderID = :orderId;';
        $attributes = Shopware()->Db()->fetchRow($sql, array('orderId' => $orderID));
        unset($attributes['id']);
        unset($attributes['orderID']);
        $orderAttributes = $attributes;

        $orderDay = date("d.m.Y");
        $orderTime = date("H:i");

        $position = 0;
        foreach ($this->sBasketData["content"] as $key => $basketRow) {
            $position++;

            $amountRow = $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($basketRow["priceNumeric"] * $basketRow["quantity"]);

            if (!$basketRow["price"]) $basketRow["price"] = "0,00";
            if (!$amountRow) $amountRow = "0,00";

            $basketRow["articlename"] = str_replace("<br />","\n",$basketRow["articlename"]);
            $basketRow["articlename"] = html_entity_decode($basketRow["articlename"]);
            $basketRow["articlename"] = strip_tags($basketRow["articlename"]);

            if (!$basketRow["itemInfo"]) {
                $priceRow = $basketRow["price"];
            } else {
                $priceRow = $basketRow["itemInfo"];
            }

            $basketRow["articlename"] = $this->sSYSTEM->sMODULES['sArticles']->sOptimizeText($basketRow["articlename"]);

            if (!$basketRow["esdarticle"]) $basketRow["esdarticle"] = "0";
            if (!$basketRow["modus"]) $basketRow["modus"] = "0";
            if (!$basketRow["taxID"]) $basketRow["taxID"] = "0";
            if ($this->sNet == true) {
                $basketRow["taxID"] = "0";
            }

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
                taxID,
                tax_rate
                )
                VALUES (
                $orderID,
                '$orderNumber',
                {$basketRow["articleID"]},
                '{$basketRow["ordernumber"]}',
                {$basketRow["priceNumeric"]},
                {$basketRow["quantity"]},
                '".addslashes($basketRow["articlename"])."',
                0,
                '0000-00-00',
                {$basketRow["modus"]},
                {$basketRow["esdarticle"]},
                {$basketRow["taxID"]},
                {$basketRow["tax_rate"]}
            )";
            $sql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveOrder_FilterDetailsSQL', $sql, array('subject'=>$this,'row'=>$basketRow,'user'=>$this->sUserData,'order'=>array("id"=>$orderID,"number"=>$orderNumber)));

            // Check for individual voucher - code
            if ($basketRow["modus"]==2) {

                $getVoucher = $this->sSYSTEM->sDB_CONNECTION->GetRow("
                SELECT modus,id FROM s_emarketing_vouchers
                WHERE ordercode=?
                ",array($basketRow["ordernumber"]));

                if ($getVoucher["modus"]==1) {
                    // Update Voucher - Code
                    $updateVoucher = $this->sSYSTEM->sDB_CONNECTION->Execute("
                    UPDATE s_emarketing_voucher_codes
                    SET cashed = 1, userID= ?
                    WHERE id = ?
                    ",array($this->sUserData["additional"]["user"]["id"],$basketRow["articleID"]));

                }
            }

            if ($basketRow["esdarticle"]) $esdOrder = true;

            $this->sSYSTEM->sDB_CONNECTION->Execute($sql);
            $orderdetailsID = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();

            $this->sBasketData['content'][$key]['orderDetailId'] = $orderdetailsID;

            if ($this->sSYSTEM->sDB_CONNECTION->ErrorMsg() || !$orderdetailsID) {
                mail($this->sSYSTEM->sCONFIG['sMAIL'],"Shopware Order Fatal-Error {$_SERVER["HTTP_HOST"]}",$this->sSYSTEM->sDB_CONNECTION->ErrorMsg().$sql);
                die("Fatal order failure, please try again later, order was not processed");
            }

            //new attribute tables
            $attributeSql = "INSERT INTO s_order_details_attributes (detailID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
                             VALUES ("
                             .$orderdetailsID. "," .
                             $this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr1"]).",".
                             $this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr2"]).",".
                             $this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr3"]).",".
                             $this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr4"]).",".
                             $this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr5"]).",".
                             $this->sSYSTEM->sDB_CONNECTION->qstr((string) $basketRow["ob_attr6"]).
            ")";
            $attributeSql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveOrderAttributes_FilterDetailsSQL', $attributeSql, array('subject'=>$this,'row'=>$basketRow,'user'=>$this->sUserData,'order'=>array("id"=>$orderID,"number"=>$orderNumber)));
            $this->sSYSTEM->sDB_CONNECTION->Execute($attributeSql);

            // add attributes
            $sql = 'SELECT * FROM s_order_details_attributes WHERE detailID = :detailID;';
            $attributes = Shopware()->Db()->fetchRow($sql, array('detailID' => $orderdetailsID));
            unset($attributes['id']);
            unset($attributes['detailID']);
            $orderDetail['attributes'] = $attributes;
            $this->sBasketData['content'][$key]['attributes'] = $attributes;

            // Update sales and stock
            if ($basketRow["priceNumeric"] >= 0) {
                $this->sSYSTEM->sDB_CONNECTION->Execute("
                UPDATE s_articles_details SET sales=sales+{$basketRow["quantity"]},instock=instock-{$basketRow["quantity"]}  WHERE ordernumber='{$basketRow["ordernumber"]}'
                ");
            }

            if (!empty($basketRow["laststock"])&&!empty($this->sSYSTEM->sCONFIG['sDEACTIVATENOINSTOCK']) && !empty($basketRow['articleID'])) {
                $sql = 'SELECT MAX(instock) as max_instock FROM s_articles_details WHERE articleID=?';
                $max_instock = $this->sSYSTEM->sDB_CONNECTION->GetOne($sql,array($basketRow['articleID']));
                $max_instock = (int) $max_instock;
                if ($max_instock<=0) {
                    $sql = 'UPDATE s_articles SET active=0 WHERE id=?';
                    $this->sSYSTEM->sDB_CONNECTION->Execute($sql,array($basketRow['articleID']));
                    // Ticket #5517
                    $this->sSYSTEM->sDB_CONNECTION->Execute("
                    UPDATE s_articles_details SET active = 0 WHERE ordernumber = ?
                    ",array($basketRow['ordernumber']));
                }
            }

            // For esd-articles, assign serialnumber if needed
            // Check if this article is esd-only (check in variants, too -> later)
            if ($basketRow["esdarticle"]) {
                $this->sManageEsdOrder($basketRow, $orderID, $orderdetailsID);

                // Add assignedSerials to basketcontent
                if (!empty($basketRow['assignedSerials'])) {
                    $this->sBasketData["content"][$key]['serials'] = $basketRow['assignedSerials'];
                }
            }

        } // For every artice in basket

        Enlight()->Events()->notify('Shopware_Modules_Order_SaveOrder_ProcessDetails', array(
            'subject' => $this,
            'details' => $this->sBasketData['content'],
        ));

        // Assign variables
        foreach ($this->sUserData["billingaddress"] as $key => $value) {
            $this->sUserData["billingaddress"][$key] = html_entity_decode($value);
        }
        foreach ($this->sUserData["shippingaddress"] as $key => $value) {
            $this->sUserData["shippingaddress"][$key] = html_entity_decode($value);
        }
        foreach ($this->sUserData["additional"]["country"] as $key => $value) {
            $this->sUserData["additional"]["country"][$key] = html_entity_decode($value);
        }

        $this->sUserData["additional"]["payment"]["description"] = html_entity_decode($this->sUserData["additional"]["payment"]["description"]);




        $sOrderDetails = array();
        foreach ($this->sBasketData["content"] as $content) {
            $content["articlename"] = trim(html_entity_decode($content["articlename"]));
            $content["articlename"] = str_replace(array("<br />","<br>"),"\n",$content["articlename"]);
            $content["articlename"] = str_replace("&euro;","€",$content["articlename"]);
            $content["articlename"] = trim($content["articlename"]);

            while (strpos($content["articlename"],"\n\n")!==false) {
                $content["articlename"] = str_replace("\n\n","\n",$content["articlename"]);
            }

            $content["ordernumber"] = trim(html_entity_decode($content["ordernumber"]));

            $sOrderDetails[] = $content;
        }

        $variables = array(
            "sOrderDetails"=>$sOrderDetails,
            "billingaddress"=>$this->sUserData["billingaddress"],
            "shippingaddress"=>$this->sUserData["shippingaddress"],
            "additional"=>$this->sUserData["additional"],
            "sShippingCosts"=>$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sShippingcosts)." ".$this->sSYSTEM->sCurrency["currency"],
            "sAmount"=>$this->sAmountWithTax ? $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sAmountWithTax)." ".$this->sSYSTEM->sCurrency["currency"] : $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sAmount)." ".$this->sSYSTEM->sCurrency["currency"],
            "sAmountNet"=>$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sBasketData["AmountNetNumeric"])." ".$this->sSYSTEM->sCurrency["currency"],
            "ordernumber"=>$orderNumber,
            "sOrderDay"=>$orderDay,
            "sOrderTime"=>$orderTime,
            "sComment"=>$this->sComment,
            'attributes' => $orderAttributes,
            "sEsd"=>$esdOrder
        );

        if ($dispatchId) {
            $variables["sDispatch"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetDispatch($dispatchId);
        }
        if ($this->bookingId) {
            $variables['sBookingID'] = $this->bookingId;
        }

        // Save Billing and Shipping-Address to retrace in future
        $this->sSaveBillingAddress($this->sUserData["billingaddress"],$orderID);
        $this->sSaveShippingAddress($this->sUserData["shippingaddress"],$orderID);


        // Completed - Garbage basket / temporary - order
        $this->sDeleteTemporaryOrder();

        $deleteSession =$this->sSYSTEM->sDB_CONNECTION->Execute("
        DELETE FROM s_order_basket WHERE sessionID=?
        ",array($this->sSYSTEM->sSESSION_ID));

        $this->sendMail($variables);

        // Check if voucher is affected
        $this->sTellFriend();

        if (isset(Shopware()->Session()->sOrderVariables)) {
            $variables = Shopware()->Session()->sOrderVariables;
            $variables['sOrderNumber'] = $orderNumber;
            Shopware()->Session()->sOrderVariables = $variables;
        }

        return $orderNumber;
    } // End public function Order


    /**
     * send order confirmation mail
     * @access public
     */
    public function sendMail($variables)
    {
        $variables = Enlight()->Events()->filter('Shopware_Modules_Order_SendMail_FilterVariables', $variables, array('subject' => $this));

        $context = array(
            'sOrderDetails' => $variables["sOrderDetails"],

            'billingaddress'  => $variables["billingaddress"],
            'shippingaddress' => $variables["shippingaddress"],
            'additional'      => $variables["additional"],

            'sShippingCosts' => $variables["sShippingCosts"],
            'sAmount'        => $variables["sAmount"],
            'sAmountNet'     => $variables["sAmountNet"],

            'sOrderNumber' => $variables["ordernumber"],
            'sOrderDay'    => $variables["sOrderDay"],
            'sOrderTime'   => $variables["sOrderTime"],
            'sComment'     => $variables["sComment"],

            'attributes'     => $variables["attributes"],
            'sCurrency'    => $this->sSYSTEM->sCurrency["currency"],

            'sLanguage'    => $this->sSYSTEM->sLanguageData[$this->sSYSTEM->sLanguage]["isocode"],

            'sSubShop'     => $this->sSYSTEM->sSubShop["id"],

            'sEsd'    => $variables["sEsd"],
            'sNet'    => $this->sNet,

        );

        // Support for individual paymentmeans with custom-tables
        if ($variables["additional"]["payment"]["table"]) {
            $paymentTable = $this->sSYSTEM->sDB_CONNECTION->GetRow("
            SELECT * FROM {$variables["additional"]["payment"]["table"]}
            WHERE userID=?",array($variables["additional"]["user"]["id"]));
            $context["sPaymentTable"] = $paymentTable;
        } else {
            $context["sPaymentTable"] = array();
        }

        if ($variables["sDispatch"]) {
            $context['sDispatch'] = $variables["sDispatch"];
        }

        if ($variables['sBookingID']) {
            $context['sBookingID'] = $variables["sBookingID"];
        }

        $mail = null;
        if ($event = Enlight_Application::Instance()->Events()->notifyUntil(
            'Shopware_Modules_Order_SendMail_Create',
            array(
                'subject'   => $this,
                'context'   => $context,
                'variables' => $variables,
            )
        )) {
            $mail = $event->getReturn();
        }

        if (!($mail instanceof \Zend_Mail)) {
            $mail = Shopware()->TemplateMail()->createMail('sORDER', $context);
        }

        $mail->addTo($this->sUserData["additional"]["user"]["email"]);

        if (!$this->sSYSTEM->sCONFIG["sNO_ORDER_MAIL"]) {
            $mail->addBcc($this->sSYSTEM->sCONFIG['sMAIL']);
        }

        $mail = Enlight()->Events()->filter('Shopware_Modules_Order_SendMail_Filter', $mail, array(
            'subject'   => $this,
            'context'   => $context,
            'variables' => $variables,
        ));

        if (!($mail instanceof \Zend_Mail)) {
            return;
        }

        Enlight()->Events()->notify(
            'Shopware_Modules_Order_SendMail_BeforeSend',
            array(
                'subject'   => $this,
                'mail'      => $mail,
                'context'   => $context,
                'variables' => $variables,
            )
        );

        $shouldSendMail = !(bool) Enlight_Application::Instance()->Events()->notifyUntil(
            'Shopware_Modules_Order_SendMail_Send',
            array(
                'subject' => $this,
                'mail' => $mail,
                'context' => $context,
                'variables' => $variables,
            )
        );

        if ($shouldSendMail && Shopware()->Config()->get('sendOrderMail')) {
            $mail->send();
        }
    }

    /**
     * Save order billing address
     * @access public
     */
    public function sSaveBillingAddress($address,$id)
    {
        $sql = "
        INSERT INTO s_order_billingaddress
        (
            userID,
            orderID,
            customernumber,
            company,
            department,
            salutation,
            firstname,
            lastname,
            street,
            streetnumber,
            zipcode,
            city,
            phone,
            fax,
            countryID,
            stateID,
            ustid
        )
        VALUES (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
            )
        ";
        $sql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveBilling_FilterSQL', $sql, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $array = array(
            $address["userID"],
            $id,
            $address["customernumber"],
            $address["company"],
            $address["department"],
            $address["salutation"],
            $address["firstname"],
            $address["lastname"],
            $address["street"],
            $address["streetnumber"],
            $address["zipcode"],
            $address["city"],
            $address["phone"],
            $address["fax"],
            $address["countryID"],
            $address["stateID"],
            $address["ustid"]
        );
        $array = Enlight()->Events()->filter('Shopware_Modules_Order_SaveBilling_FilterArray', $array, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $result =$this->sSYSTEM->sDB_CONNECTION->Execute($sql,$array);


        //new attribute tables
        $billingID = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();
        $sql = "INSERT INTO s_order_billingaddress_attributes (billingID, text1, text2, text3, text4, text5, text6) VALUES (?,?,?,?,?,?,?)";
        $sql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveBillingAttributes_FilterSQL', $sql, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $array = array(
            $billingID,
            $address["text1"],
            $address["text2"],
            $address["text3"],
            $address["text4"],
            $address["text5"],
            $address["text6"]
        );
        $array = Enlight()->Events()->filter('Shopware_Modules_Order_SaveBillingAttributes_FilterArray', $array, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $this->sSYSTEM->sDB_CONNECTION->Execute($sql,$array);

        return $result;
    }

    /**
     * save order shipping address
     * @access public
     */
    public function sSaveShippingAddress($address,$id)
    {
        $sql = "
        INSERT INTO s_order_shippingaddress
        (
            userID,
            orderID,
            company,
            department,
            salutation,
            firstname,
            lastname,
            street,
            streetnumber,
            zipcode,
            city,
            countryID,
            stateID
        )
        VALUES (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
            )
        ";
        $sql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveShipping_FilterSQL', $sql, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $array = array(
            $address["userID"],
            $id,
            $address["company"],
            $address["department"],
            $address["salutation"],
            $address["firstname"],
            $address["lastname"],
            $address["street"],
            $address["streetnumber"],
            $address["zipcode"],
            $address["city"],
            $address["countryID"],
            $address["stateID"]
        );
        $array = Enlight()->Events()->filter('Shopware_Modules_Order_SaveShipping_FilterArray', $array, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $result = $this->sSYSTEM->sDB_CONNECTION->Execute($sql,$array);

        //new attribute table
        $shippingId = $this->sSYSTEM->sDB_CONNECTION->Insert_ID();
        $sql = "INSERT INTO s_order_shippingaddress_attributes (shippingID, text1, text2, text3, text4, text5, text6) VALUES (?,?,?,?,?,?,?)";
        $sql = Enlight()->Events()->filter('Shopware_Modules_Order_SaveShippingAttributes_FilterSQL', $sql, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $array = array(
            $shippingId,
            $address["text1"],
            $address["text2"],
            $address["text3"],
            $address["text4"],
            $address["text5"],
            $address["text6"]
        );
        $array = Enlight()->Events()->filter('Shopware_Modules_Order_SaveShippingAttributes_FilterArray', $array, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $this->sSYSTEM->sDB_CONNECTION->Execute($sql,$array);

        return $result;
    }

    /**
     * smarty modifier fill
     */
    public function smarty_modifier_fill($str, $width=10, $break="...", $fill=" ")
    {
        if(!is_scalar($break))
        $break = "...";
        if(empty($fill)||!is_scalar($fill))
        $fill = " ";
        if(empty($width)||!is_numeric($width))
        $width = 10;
        else
        $width = (int) $width;
        if(!is_scalar($str))
        return str_repeat($fill,$width);
        if(strlen($str)>$width)
        $str = substr($str,0,$width-strlen($break)).$break;
        if($width>strlen($str))
        return $str.str_repeat($fill,$width-strlen($str));
        else
        return $str;
    }

    /**
     * smarty modifier padding
     */
    public function smarty_modifier_padding($str, $width=10, $break="...", $fill=" ")
    {
        if(!is_scalar($break))
        $break = "...";
        if(empty($fill)||!is_scalar($fill))
        $fill = " ";
        if(empty($width)||!is_numeric($width))
        $width = 10;
        else
        $width = (int) $width;
        if(!is_scalar($str))
        return str_repeat($fill,$width);
        if(strlen($str)>$width)
        $str = substr($str,0,$width-strlen($break)).$break;
        if($width>strlen($str))
        return str_repeat($fill,$width-strlen($str)).$str;
        else
        return $str;
    }

    /**
     * Check if this order could be refered to a previous recommendation
     * @access public
     */
    public function sTellFriend()
    {
        $checkMail = $this->sUserData["additional"]["user"]["email"];

        $tmpSQL = "
        SELECT * FROM s_emarketing_tellafriend WHERE confirmed=0 AND recipient=?
        ";
        $checkIfUserFound = $this->sSYSTEM->sDB_CONNECTION->GetRow($tmpSQL, array($checkMail));
        if (count($checkIfUserFound)) {
            // User-Datensatz aktualisieren
            $updateUserFound = $this->sSYSTEM->sDB_CONNECTION->Execute("
            UPDATE s_emarketing_tellafriend SET confirmed=1 WHERE recipient=?
            ",array($checkMail));
            // --

            // Daten �ber Werber fetchen
            $getWerberInfo = $this->sSYSTEM->sDB_CONNECTION->GetRow("
            SELECT email, firstname, lastname FROM s_user, s_user_billingaddress
            WHERE s_user_billingaddress.userID = s_user.id AND s_user.id=?
            ",array($checkIfUserFound["sender"]));

            if (empty($getWerberInfo)) {
                // Benutzer nicht mehr vorhanden
                return;
            }

            $context = array(
                'customer'     => $getWerberInfo["firstname"] . " " . $getWerberInfo["lastname"],
                'user'         => $this->sUserData["billingaddress"]["firstname"] . " " . $this->sUserData["billingaddress"]["lastname"],
                'voucherValue' => $this->sSYSTEM->sCONFIG['sVOUCHERTELLFRIENDVALUE'],
                'voucherCode'  => $this->sSYSTEM->sCONFIG['sVOUCHERTELLFRIENDCODE']
            );

            $mail = Shopware()->TemplateMail()->createMail('sVOUCHER', $context);
            $mail->addTo($getWerberInfo["email"]);
            $mail->send();
        } // - if user found
    } // Tell-a-friend

    /**
     * Send status mail
     *
     * @param Enlight_Components_Mail $mail
     * @return Enlight_Components_Mail
     */
    public function sendStatusMail(Enlight_Components_Mail $mail)
    {

        Enlight()->Events()->notify('Shopware_Controllers_Backend_OrderState_Send_BeforeSend', array(
            'subject' => Shopware()->Front(), 'mail' => $mail,
        ));


        if (!empty(Shopware()->Config()->OrderStateMailAck)) {
            $mail->addBcc(Shopware()->Config()->OrderStateMailAck);
        }

        return $mail->send();
    }

    /**
     * Create status mail
     *
     * @param int $orderId
     * @param int $statusId
     * @param string $templateName
     * @return Enlight_Components_Mail
     */
    public function createStatusMail($orderId, $statusId, $templateName = null)
    {
        $statusId = (int) $statusId;
        $orderId  = (int) $orderId;

        if (empty($templateName)) {
            $templateName = 'sORDERSTATEMAIL' . $statusId;
        }

        if (empty($orderId) || !is_numeric($statusId)) {
            return;
        }

        //todo@all replace this with the new sw api
        $order = Shopware()->Api()->Export()->sGetOrders(array('orderID' => $orderId));
        $order = current($order);

        // add attributes to order
        $sql = 'SELECT * FROM s_order_attributes WHERE orderID = :orderId;';
        $attributes = Shopware()->Db()->fetchRow($sql, array('orderId' => $orderId));
        unset($attributes['id']);
        unset($attributes['orderID']);
        $order['attributes'] = $attributes;

        if (!empty($order['dispatchID'])) {
            $dispatch = Shopware()->Db()->fetchRow('
                SELECT name, description FROM s_premium_dispatch
                WHERE id=?
            ', array($order['dispatchID']));
        }


        $orderDetails = Shopware()->Api()->Export()->sOrderDetails(array('orderID' => $orderId));
        $orderDetails = array_values($orderDetails);

        // add attributes to orderDetails
        foreach ($orderDetails as &$orderDetail) {
            $sql = 'SELECT * FROM s_order_details_attributes WHERE detailID = :detailID;';
            $attributes = Shopware()->Db()->fetchRow($sql, array('detailID' => $orderDetail['orderdetailsID']));
            unset($attributes['id']);
            unset($attributes['detailID']);
            $orderDetail['attributes'] = $attributes;
        }

        $user = Shopware()->Api()->Export()->sOrderCustomers(array('orderID' => $orderId));
        $user = current($user);

        if (empty($order) || empty($orderDetails) || empty($user)) {
            return;
        }

        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shopId = is_numeric($order['language']) ? $order['language'] : $order['subshopID'];
        $shop = $repository->getActiveById($shopId);
        $shop->registerResources(Shopware()->Bootstrap());

        /* @var $mailModel \Shopware\Models\Mail\Mail */
        $mailModel = Shopware()->Models()->getRepository('Shopware\Models\Mail\Mail')->findOneBy(array('name' => $templateName));
        if (!$mailModel) {
            return;
        }

        $context = array(
            'sOrder'        => $order,
            'sOrderDetails' => $orderDetails,
            'sUser'         => $user,
        );

        if (!empty($dispatch)) {
            $context['sDispatch'] = $dispatch;
        }

        $result = Enlight()->Events()->notify('Shopware_Controllers_Backend_OrderState_Notify', array(
            'subject'  => Shopware()->Front(),
            'id'       => $orderId,
            'status'   => $statusId,
            'mailname' => $templateName,
        ));

        if (!empty($result)) {
            $context['EventResult'] = $result->getValues();
        }

        $mail = Shopware()->TemplateMail()->createMail($templateName, $context, $shop);

        $return = array(
            'content'  => $mail->getPlainBodyText(),
            'subject'  => $mail->getPlainSubject(),
            'email'    => trim($user['email']),
            'frommail' => $mail->getFrom(),
            'fromname' => $mail->getFromName()
        );

        $return = Enlight()->Events()->filter('Shopware_Controllers_Backend_OrderState_Filter', $return, array(
            'subject'  => Shopware()->Front(),
            'id'       => $orderId,
            'status'   => $statusId,
            'mailname' => $templateName,
            'mail'     => $mail,
            'engine'   => Shopware()->Template()
        ));

        $mail->clearSubject();
        $mail->setSubject($return['subject']);

        $mail->setBodyText($return['content']);

        $mail->clearFrom();
        $mail->setFrom($return['frommail'], $return['fromname']);

        $mail->addTo($return['email']);

        return $mail;
    }

    /**
     * Set payment status by order id
     *
     * @param int $orderId
     * @param int $paymentStatusId
     * @param bool $sendStatusMail
     * @param string|null $comment
     */
    public function setPaymentStatus($orderId, $paymentStatusId, $sendStatusMail = false, $comment = null)
    {
        $sql = 'SELECT `cleared` FROM `s_order` WHERE `id`=?;';
        $previousStatusId = Shopware()->Db()->fetchOne($sql, array($orderId));
        if ($paymentStatusId != $previousStatusId) {
            $sql = 'UPDATE `s_order` SET `cleared`=? WHERE `id`=?;';
            Shopware()->Db()->query($sql, array($paymentStatusId, $orderId));
            $sql = '
               INSERT INTO s_order_history (
                  orderID, userID, previous_order_status_id, order_status_id,
                  previous_payment_status_id, payment_status_id, comment, change_date )
                SELECT id, NULL, status, status, ?, ?, ?, NOW() FROM s_order WHERE id=?
            ';
            Shopware()->Db()->query($sql, array($previousStatusId, $paymentStatusId, $comment, $orderId));
            if ($sendStatusMail) {
                $mail = $this->createStatusMail($paymentStatusId, $comment, $orderId);
                if ($mail) {
                    $this->sendStatusMail($mail);
                }
            }
        }
    }

    /**
     * Set payment status by order id
     *
     * @param int $orderId
     * @param int $orderStatusId
     * @param bool $sendStatusMail
     * @param string|null $comment
     */
    public function setOrderStatus($orderId, $orderStatusId, $sendStatusMail = false, $comment = null)
    {
        $sql = 'SELECT `status` FROM `s_order` WHERE `id`=?;';
        $previousStatusId = Shopware()->Db()->fetchOne($sql, array($orderId));
        if ($orderStatusId != $previousStatusId) {
            $sql = 'UPDATE `s_order` SET `status`=? WHERE `id`=?;';
            Shopware()->Db()->query($sql, array($orderStatusId, $orderId));
            $sql = '
               INSERT INTO s_order_history (
                  orderID, userID, previous_order_status_id, order_status_id,
                  previous_payment_status_id, payment_status_id, comment, change_date )
                SELECT id, NULL, ?, ?, cleared, cleared, ?, NOW() FROM s_order WHERE id=?
            ';
            Shopware()->Db()->query($sql, array($previousStatusId, $orderStatusId, $comment, $orderId));
            if ($sendStatusMail) {
                $mail = $this->createStatusMail($orderStatusId, $comment, $orderId);
                if ($mail) {
                    $this->sendStatusMail($mail);
                }
            }
        }
    }
}
