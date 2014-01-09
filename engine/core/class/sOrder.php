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
     * @var sSYSTEM
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
     * Database connection which used for each database operation in this class.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    private $db;

    /**
     * Event manager which is used for the event system of shopware.
     * Injected over the class constructor
     *
     * @var Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * Shopware configuration object which used for
     * each config access in this class.
     * Injected over the class constructor
     *
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * Shopware session namespace object which is used
     * for each session access in this class.
     * Injected over the class constructor
     *
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * Class constructor.
     * Injects all dependencies which are required for this class.
     */
    public function __construct()
    {
        $this->db = Shopware()->Db();
        $this->eventManager = Shopware()->Events();
        $this->config = Shopware()->Config();
        $this->session = Shopware()->Session();
    }

    /**
     * Get a unique ordernumber
     * @access public
     * @return string ordernumber
     */
    public function sGetOrderNumber()
    {
        $sql = "/*NO LIMIT*/ SELECT number FROM s_order_number WHERE name='invoice' FOR UPDATE";
        $ordernumber = $this->db->fetchOne($sql);
        $sql = "UPDATE s_order_number SET number=number+1 WHERE name='invoice'";
        $this->db->executeUpdate($sql);
        $ordernumber += 1;

        $ordernumber = $this->eventManager->filter('Shopware_Modules_Order_GetOrdernumber_FilterOrdernumber', $ordernumber, array('subject'=>$this));
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

        $esdArticle = $this->db->fetchRow($this->config->get('sCACHEARTICLE'), $sqlGetEsd);
        if (!$esdArticle["id"]) {
            // ESD not found
            return;
        }

        if (!$esdArticle["serials"]) {
            // No serialnumber is needed
            $this->db->insert('s_order_esd', array(
                'serialID' => 0,
                'esdID' => $esdArticle["id"],
                'userID' => $this->sUserData["additional"]["user"]["id"],
                'orderID' => $orderID,
                'orderdetailsID' => $orderdetailsID,
                'datum' => 'now()'
            ));
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

        $availableSerials = $this->db->fetchAll($sqlCheckSerials);

        if ((count($availableSerials) <= $this->config->get('esdMinSerials')) || count($availableSerials) <= $quantity) {
            // No serialnumber anymore, inform merchant
            $context = array(
                'sArticleName' => $basketRow["articlename"],
                'sMail'        => $this->sUserData["additional"]["user"]["email"],
            );

            $mail = Shopware()->TemplateMail()->createMail('sNOSERIALS', $context);

            if ($this->config->get('sESDMAIL')) {
                $mail->addTo($this->config->get('sESDMAIL'));
            } else {
                $mail->addTo($this->config->get('sMAIL'));
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

                $this->db->insert('s_order_esd', array(
                    'serialID' => $serialId,
                    'esdID' => $esdArticle["id"],
                    'userID' => $this->sUserData["additional"]["user"]["id"],
                    'orderID' => $orderID,
                    'orderdetailsID' => $orderdetailsID,
                    'datum' => 'now()'
                ));
            }
        }
    }

    /**
     * Delete temporary created order
     * @access public
     */
    public function sDeleteTemporaryOrder()
    {
        $sessionId = $this->session->offsetGet('sessionId');

        if (empty($sessionId)) return;

        $deleteWholeOrder = $this->db->fetchAll("
        SELECT * FROM s_order WHERE temporaryID = ? LIMIT 2
        ",array($this->session->offsetGet('sessionId')));

        foreach ($deleteWholeOrder as $orderDelete) {
            $this->db->executeUpdate("
            DELETE FROM s_order WHERE id = ?
            ",array($orderDelete["id"]));

            $this->db->executeUpdate("
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

        $taxId = $this->sSYSTEM->sUSERGROUPDATA["tax"];
        $customerGroupId = $this->sSYSTEM->sUSERGROUPDATA["id"];
        if ($this->isTaxFree($taxId, $customerGroupId)) {
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

        $data = array(
            'ordernumber' => '0',
            'userID' => $this->sUserData["additional"]["user"]["id"],
            'invoice_amount' => $this->sBasketData["AmountWithTaxNumeric"],
            'invoice_amount_net' => $this->sBasketData["AmountNetNumeric"],
            'invoice_shipping' => $this->sShippingcostsNumeric,
            'invoice_shipping_net' => $this->sShippingcostsNumericNet,
            'ordertime' => 'now()',
            'status' => -1,
            'paymentID' => $this->sUserData["additional"]["user"]["paymentID"],
            'customercomment' => $this->sComment,
            'net' => $net,
            'taxfree' => $taxfree,
            'partnerID' => (string) $this->session->offsetGet("sPartner"),
            'temporaryID' => $this->session->offsetGet('sessionId'),
            'referer' => (string) $this->session->offsetGet('sReferer'),
            'language' => $shop->getId(),
            'dispatchID' => $dispatchId,
            'currency' => $this->sSYSTEM->sCurrency["currency"],
            'currencyFactor' => $this->sSYSTEM->sCurrency["factor"],
            'subshopID' => $mainShop->getId()
        );

        try {
            $affectedRows = $this->db->insert('s_order', $data);
            $orderID = $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Enlight_Exception("##sOrder-sTemporaryOrder-#01:" . $e->getMessage(), 0, $e);
        }
        if (!$affectedRows || ! $orderID) {
            throw new Enlight_Exception("##sOrder-sTemporaryOrder-#01: No rows affected or no order id saved", 0);
        }

        $position = 0;
        foreach ($this->sBasketData["content"] as $basketRow) {
            $position++;

            if (!$basketRow["price"]) $basketRow["price"] = "0,00";

            $basketRow["articlename"] = html_entity_decode($basketRow["articlename"]);
            $basketRow["articlename"] = strip_tags($basketRow["articlename"]);

            $basketRow["articlename"] = $this->sSYSTEM->sMODULES['sArticles']->sOptimizeText($basketRow["articlename"]);

            if (!$basketRow["esdarticle"]) $basketRow["esdarticle"] = "0";
            if (!$basketRow["modus"]) $basketRow["modus"] = "0";
            if (!$basketRow["taxID"]) $basketRow["taxID"] = "0";

            $data = array(
                'orderID' => $orderID,
                'ordernumber' => 0,
                'articleID' => $basketRow["articleID"],
                'articleordernumber' => $basketRow["ordernumber"],
                'price' => $basketRow["priceNumeric"],
                'quantity' => $basketRow["quantity"],
                'name' => $basketRow["articlename"],
                'status' => 0,
                'releasedate' => '0000-00-00',
                'modus' => $basketRow["modus"],
                'esdarticle' => $basketRow["esdarticle"],
                'taxID' => $basketRow["taxID"],
                'tax_rate' => $basketRow["tax_rate"]
            );

            try {
                $this->db->insert('s_order_details', $data);
            } catch (Exception $e) {
                throw new Enlight_Exception("##sOrder-sTemporaryOrder-Position-#02:" . $e->getMessage(), 0, $e);
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

        if ($this->isTransactionExist($this->bookingId)) {
            return false;
        }

        // Insert basic-data of the order
        $orderNumber = $this->sGetOrderNumber();
        $this->sOrderNumber = $orderNumber;

        if (!$this->sShippingcostsNumeric) $this->sShippingcostsNumeric = "0";

        if (!$this->sBasketData["AmountWithTaxNumeric"]) $this->sBasketData["AmountWithTaxNumeric"] = $this->sBasketData["AmountNumeric"];

        $taxId = $this->sSYSTEM->sUSERGROUPDATA["tax"];
        $customerGroupId = $this->sSYSTEM->sUSERGROUPDATA["id"];
        if ($this->isTaxFree($taxId, $customerGroupId)) {
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

        $partner = $this->getPartnerId(
            $this->sUserData["additional"]["user"]["affiliate"]
        );

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
            ".$this->db->quote($this->sComment).",
            $net,
            $taxfree,
            " . $this->db->quote((string) $partner) . ",
            ".$this->db->quote((string) $this->uniqueID).",
            ".$this->db->quote((string) $this->session->offsetGet('sReferer')).",
            '".$shop->getId()."',
            '$dispatchId',
            '".$this->sSYSTEM->sCurrency["currency"]."',
            '".$this->sSYSTEM->sCurrency["factor"]."',
            '".$mainShop->getId()."',
            ".$this->db->quote((string) $_SERVER['REMOTE_ADDR'])."
        )
        ";

        $sql = $this->eventManager->filter('Shopware_Modules_Order_SaveOrder_FilterSQL', $sql, array('subject'=>$this));

        try {
            $affectedRows = $this->db->executeUpdate($sql);
            $orderID = $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Enlight_Exception("Shopware Order Fatal-Error {$_SERVER["HTTP_HOST"]} :" . $e->getMessage(), 0, $e);
        }
        if (!$affectedRows || !$orderID) {
            throw new Enlight_Exception("Shopware Order Fatal-Error {$_SERVER["HTTP_HOST"]} : No rows affected or no order id created.", 0);
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
                    ".$this->db->quote((string) $this->o_attr_1).",
                    ".$this->db->quote((string) $this->o_attr_2).",
                    ".$this->db->quote((string) $this->o_attr_3).",
                    ".$this->db->quote((string) $this->o_attr_4).",
                    ".$this->db->quote((string) $this->o_attr_5).",
                    ".$this->db->quote((string) $this->o_attr_6)."
                )";
        $attributeSql = $this->eventManager->filter('Shopware_Modules_Order_SaveOrderAttributes_FilterSQL', $attributeSql, array('subject'=>$this));
        $this->db->executeUpdate($attributeSql);

        // add attributes to order
        $sql = 'SELECT * FROM s_order_attributes WHERE orderID = :orderId;';
        $attributes = $this->db->fetchRow($sql, array('orderId' => $orderID));
        unset($attributes['id']);
        unset($attributes['orderID']);
        $orderAttributes = $attributes;

        $orderDay = date("d.m.Y");
        $orderTime = date("H:i");

        $position = 0;
        foreach ($this->sBasketData["content"] as $key => $basketRow) {
            $position++;

            if (!$basketRow["price"]) $basketRow["price"] = "0,00";

            $basketRow["articlename"] = str_replace("<br />","\n",$basketRow["articlename"]);
            $basketRow["articlename"] = html_entity_decode($basketRow["articlename"]);
            $basketRow["articlename"] = strip_tags($basketRow["articlename"]);

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
            $sql = $this->eventManager->filter('Shopware_Modules_Order_SaveOrder_FilterDetailsSQL', $sql, array('subject'=>$this,'row'=>$basketRow,'user'=>$this->sUserData,'order'=>array("id"=>$orderID,"number"=>$orderNumber)));

            // Check for individual voucher - code
            if ($basketRow["modus"] == 2) {
                //reserve the basket voucher for the current user.
                $this->reserveVoucher(
                    $basketRow["ordernumber"],
                    $this->sUserData["additional"]["user"]["id"],
                    $basketRow["articleID"]
                );
            }

            if ($basketRow["esdarticle"]) $esdOrder = true;


            try {
                $this->db->executeUpdate($sql);
                $orderdetailsID = $this->db->lastInsertId();
            } catch (Exception $e) {
                throw new Enlight_Exception("Shopware Order Fatal-Error {$_SERVER["HTTP_HOST"]} :" . $e->getMessage(), 0, $e);
            }

            $this->sBasketData['content'][$key]['orderDetailId'] = $orderdetailsID;

            //new attribute tables
            $attributeSql = "INSERT INTO s_order_details_attributes (detailID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
                             VALUES ("
                             .$orderdetailsID. "," .
                             $this->db->quote((string) $basketRow["ob_attr1"]).",".
                             $this->db->quote((string) $basketRow["ob_attr2"]).",".
                             $this->db->quote((string) $basketRow["ob_attr3"]).",".
                             $this->db->quote((string) $basketRow["ob_attr4"]).",".
                             $this->db->quote((string) $basketRow["ob_attr5"]).",".
                             $this->db->quote((string) $basketRow["ob_attr6"]).
            ")";
            $attributeSql = $this->eventManager->filter('Shopware_Modules_Order_SaveOrderAttributes_FilterDetailsSQL', $attributeSql, array('subject'=>$this,'row'=>$basketRow,'user'=>$this->sUserData,'order'=>array("id"=>$orderID,"number"=>$orderNumber)));
            $this->db->executeUpdate($attributeSql);

            // add attributes
            $sql = 'SELECT * FROM s_order_details_attributes WHERE detailID = :detailID;';
            $attributes = $this->db->fetchRow($sql, array('detailID' => $orderdetailsID));
            unset($attributes['id']);
            unset($attributes['detailID']);
            $orderDetail['attributes'] = $attributes;
            $this->sBasketData['content'][$key]['attributes'] = $attributes;

            // Update sales and stock
            if ($basketRow["priceNumeric"] >= 0) {
                $this->db->executeUpdate("
                UPDATE s_articles_details SET sales=sales+{$basketRow["quantity"]},instock=instock-{$basketRow["quantity"]}  WHERE ordernumber='{$basketRow["ordernumber"]}'
                ");
            }

            $deactivateNoInStock = $this->config->get('sDEACTIVATENOINSTOCK');
            if (!empty($basketRow["laststock"])&&!empty($deactivateNoInStock) && !empty($basketRow['articleID'])) {
                $sql = 'SELECT MAX(instock) as max_instock FROM s_articles_details WHERE articleID=?';
                $max_instock = $this->db->fetchOne($sql,array($basketRow['articleID']));
                $max_instock = (int) $max_instock;
                if ($max_instock<=0) {
                    $sql = 'UPDATE s_articles SET active=0 WHERE id=?';
                    $this->db->executeUpdate($sql,array($basketRow['articleID']));
                    // Ticket #5517
                    $this->db->executeUpdate("
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

        $this->eventManager->notify('Shopware_Modules_Order_SaveOrder_ProcessDetails', array(
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

        $this->db->executeUpdate("DELETE FROM s_order_basket WHERE sessionID=?",array($this->session->offsetGet('sessionId')));

        $this->sendMail($variables);

        // Check if voucher is affected
        $this->sTellFriend();

        if ($this->session->offsetExists('sOrderVariables')) {
            $variables = $this->session->offsetGet('sOrderVariables');
            $variables['sOrderNumber'] = $orderNumber;
            $this->session->offsetSet('sOrderVariables', $variables);
        }

        return $orderNumber;
    } // End public function Order


    /**
     * Checks if the passed transaction id is already set as transaction id of an
     * existing order.
     * @param $transactionId
     * @return bool
     */
    private function isTransactionExist($transactionId)
    {
        if (strlen($transactionId) <= 3) {
            return false;
        }

        $insertOrder = $this->db->fetchRow(
            "SELECT id FROM s_order WHERE transactionID = ? AND status != -1",
            array($transactionId)
        );

        return !empty($insertOrder["id"]);
    }

    /**
     * Checks if the current customer should see net prices.
     * @param $taxId
     * @param $customerGroupId
     * @return bool
     */
    private function isTaxFree($taxId, $customerGroupId)
    {
        return (($this->config->get('sARTICLESOUTPUTNETTO') && !$taxId)
            || (!$taxId && $customerGroupId));
    }

    /**
     * Checks if the current order was send from a partner and returns
     * the partner code.
     *
     * @param int $userAffiliate affiliate flag of the user data.
     * @return null|string
     */
    private function getPartnerId($userAffiliate)
    {
        $isPartner = $this->session->offsetGet("sPartner");
        if (!empty($isPartner)) {
            return $this->session->offsetGet("sPartner");
        }

        if (empty($userAffiliate)) {
            return null;
        }

        // Get Partner code
        return $this->db->fetchOne(
            "SELECT idcode FROM s_emarketing_partner WHERE id = ?",
            array($userAffiliate)
        );
    }

    /**
     * Helper function which reserves individual voucher codes for the
     * passed user.
     *
     * @param $orderCode
     * @param $customerId
     * @param $voucherCodeId
     */
    private function reserveVoucher($orderCode, $customerId, $voucherCodeId)
    {
        $getVoucher = $this->db->fetchRow(
            "SELECT modus,id FROM s_emarketing_vouchers WHERE ordercode = ?",
            array($orderCode)
        );

        if ($getVoucher["modus"] == 1) {
            $this->db->executeUpdate(
                "UPDATE s_emarketing_voucher_codes SET cashed = 1, userID= ? WHERE id = ?",
                array($customerId, $voucherCodeId)
            );
        }
    }

    /**
     * send order confirmation mail
     * @access public
     */
    public function sendMail($variables)
    {
        $variables = $this->eventManager->filter('Shopware_Modules_Order_SendMail_FilterVariables', $variables, array('subject' => $this));

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
            $paymentTable = $this->db->fetchRow("
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
        if ($event = $this->eventManager->notifyUntil(
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

        if (!$this->config->get("sNO_ORDER_MAIL")) {
            $mail->addBcc($this->config->get('sMAIL'));
        }

        $mail = $this->eventManager->filter('Shopware_Modules_Order_SendMail_Filter', $mail, array(
            'subject'   => $this,
            'context'   => $context,
            'variables' => $variables,
        ));

        if (!($mail instanceof \Zend_Mail)) {
            return;
        }

        $this->eventManager->notify(
            'Shopware_Modules_Order_SendMail_BeforeSend',
            array(
                'subject'   => $this,
                'mail'      => $mail,
                'context'   => $context,
                'variables' => $variables,
            )
        );

        $shouldSendMail = !(bool) $this->eventManager->notifyUntil(
            'Shopware_Modules_Order_SendMail_Send',
            array(
                'subject' => $this,
                'mail' => $mail,
                'context' => $context,
                'variables' => $variables,
            )
        );

        if ($shouldSendMail && $this->config->get('sendOrderMail')) {
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
        $sql = $this->eventManager->filter('Shopware_Modules_Order_SaveBilling_FilterSQL', $sql, array('subject'=>$this,'address'=>$address,'id'=>$id));
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
        $array = $this->eventManager->filter('Shopware_Modules_Order_SaveBilling_FilterArray', $array, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $result = $this->db->executeUpdate($sql,$array);


        //new attribute tables
        $billingID = $this->db->lastInsertId();
        $sql = "INSERT INTO s_order_billingaddress_attributes (billingID, text1, text2, text3, text4, text5, text6) VALUES (?,?,?,?,?,?,?)";
        $sql = $this->eventManager->filter('Shopware_Modules_Order_SaveBillingAttributes_FilterSQL', $sql, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $array = array(
            $billingID,
            $address["text1"],
            $address["text2"],
            $address["text3"],
            $address["text4"],
            $address["text5"],
            $address["text6"]
        );
        $array = $this->eventManager->filter('Shopware_Modules_Order_SaveBillingAttributes_FilterArray', $array, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $this->db->executeUpdate($sql,$array);

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
        $sql = $this->eventManager->filter('Shopware_Modules_Order_SaveShipping_FilterSQL', $sql, array('subject'=>$this,'address'=>$address,'id'=>$id));
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
        $array = $this->eventManager->filter('Shopware_Modules_Order_SaveShipping_FilterArray', $array, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $result = $this->db->executeUpdate($sql,$array);

        //new attribute table
        $shippingId = $this->db->lastInsertId();
        $sql = "INSERT INTO s_order_shippingaddress_attributes (shippingID, text1, text2, text3, text4, text5, text6) VALUES (?,?,?,?,?,?,?)";
        $sql = $this->eventManager->filter('Shopware_Modules_Order_SaveShippingAttributes_FilterSQL', $sql, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $array = array(
            $shippingId,
            $address["text1"],
            $address["text2"],
            $address["text3"],
            $address["text4"],
            $address["text5"],
            $address["text6"]
        );
        $array = $this->eventManager->filter('Shopware_Modules_Order_SaveShippingAttributes_FilterArray', $array, array('subject'=>$this,'address'=>$address,'id'=>$id));
        $this->db->executeUpdate($sql,$array);

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
        $checkIfUserFound = $this->db->fetchRow($tmpSQL, array($checkMail));
        if (count($checkIfUserFound)) {
            // User-Datensatz aktualisieren
            $this->db->executeUpdate("
            UPDATE s_emarketing_tellafriend SET confirmed=1 WHERE recipient=?
            ",array($checkMail));
            // --

            // Daten �ber Werber fetchen
            $getWerberInfo = $this->db->fetchRow("
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
                'voucherValue' => $this->config->get('sVOUCHERTELLFRIENDVALUE'),
                'voucherCode'  => $this->config->get('sVOUCHERTELLFRIENDCODE')
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

        $this->eventManager->notify('Shopware_Controllers_Backend_OrderState_Send_BeforeSend', array(
            'subject' => Shopware()->Front(), 'mail' => $mail,
        ));


        if (!empty($this->config->OrderStateMailAck)) {
            $mail->addBcc($this->config->OrderStateMailAck);
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
        $attributes = $this->db->fetchRow($sql, array('orderId' => $orderId));
        unset($attributes['id']);
        unset($attributes['orderID']);
        $order['attributes'] = $attributes;

        if (!empty($order['dispatchID'])) {
            $dispatch = $this->db->fetchRow('
                SELECT name, description FROM s_premium_dispatch
                WHERE id=?
            ', array($order['dispatchID']));
        }


        $orderDetails = Shopware()->Api()->Export()->sOrderDetails(array('orderID' => $orderId));
        $orderDetails = array_values($orderDetails);

        // add attributes to orderDetails
        foreach ($orderDetails as &$orderDetail) {
            $sql = 'SELECT * FROM s_order_details_attributes WHERE detailID = :detailID;';
            $attributes = $this->db->fetchRow($sql, array('detailID' => $orderDetail['orderdetailsID']));
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

        $result = $this->eventManager->notify('Shopware_Controllers_Backend_OrderState_Notify', array(
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

        $return = $this->eventManager->filter('Shopware_Controllers_Backend_OrderState_Filter', $return, array(
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
        $previousStatusId = $this->db->fetchOne($sql, array($orderId));
        if ($paymentStatusId != $previousStatusId) {
            $sql = 'UPDATE `s_order` SET `cleared`=? WHERE `id`=?;';
            $this->db->executeUpdate($sql, array($paymentStatusId, $orderId));
            $sql = '
               INSERT INTO s_order_history (
                  orderID, userID, previous_order_status_id, order_status_id,
                  previous_payment_status_id, payment_status_id, comment, change_date )
                SELECT id, NULL, status, status, ?, ?, ?, NOW() FROM s_order WHERE id=?
            ';
            $this->db->executeUpdate($sql, array($previousStatusId, $paymentStatusId, $comment, $orderId));
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
        $previousStatusId = $this->db->fetchOne($sql, array($orderId));
        if ($orderStatusId != $previousStatusId) {
            $sql = 'UPDATE `s_order` SET `status`=? WHERE `id`=?;';
            $this->db->executeUpdate($sql, array($orderStatusId, $orderId));
            $sql = '
               INSERT INTO s_order_history (
                  orderID, userID, previous_order_status_id, order_status_id,
                  previous_payment_status_id, payment_status_id, comment, change_date )
                SELECT id, NULL, ?, ?, cleared, cleared, ?, NOW() FROM s_order WHERE id=?
            ';
            $this->db->executeUpdate($sql, array($previousStatusId, $orderStatusId, $comment, $orderId));
            if ($sendStatusMail) {
                $mail = $this->createStatusMail($orderStatusId, $comment, $orderId);
                if ($mail) {
                    $this->sendStatusMail($mail);
                }
            }
        }
    }
}
