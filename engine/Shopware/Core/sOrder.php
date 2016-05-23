<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

use Shopware\Bundle\StoreFrontBundle;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\NumberRangeIncrementerInterface;
use Shopware\Models\Customer\Customer;

/**
 * Deprecated Shopware Class that handle frontend orders
 */
class sOrder
{
    /**
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
     * Shipping costs
     *
     * @var double
     */
    public $sShippingcosts;
    /**
     * Shipping costs unformated
     *
     * @var double
     */
    public $sShippingcostsNumeric;
    /**
     * Shipping costs net unformated
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
    public $sNet;    // Complete taxfree

    /**
     * Custom attributes
     *
     * @var string
     * @deprecated since 5.2, remove in 5.3. Use orderAttributes instead
     */
    public $o_attr_1, $o_attr_2,$o_attr_3,$o_attr_4,$o_attr_5,$o_attr_6;

    /**
     * Custom attributes
     *
     * @var array
     */
    public $orderAttributes = [];

    /**
     * Device type from which the order was placed
     *
     * @var string
     */
    public $deviceType;

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
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var NumberRangeIncrementerInterface
     */
    private $numberRangeIncrementer;

    /**
     * @var Shopware\Bundle\AttributeBundle\Service\DataLoader
     */
    private $attributeLoader;

    /**
     * @var Shopware\Bundle\AttributeBundle\Service\DataPersister
     */
    private $attributePersister;

    /**
     * Class constructor.
     * Injects all dependencies which are required for this class.
     * @param ContextServiceInterface $contextService
     * @throws Exception
     */
    public function __construct(
        ContextServiceInterface $contextService = null
    ) {
        $this->db = Shopware()->Db();
        $this->eventManager = Shopware()->Events();
        $this->config = Shopware()->Config();
        $this->numberRangeIncrementer = Shopware()->Container()->get('shopware.number_range_incrementer');

        $this->contextService = $contextService ? : Shopware()->Container()->get('shopware_storefront.context_service');
        $this->attributeLoader = Shopware()->Container()->get('shopware_attribute.data_loader');
        $this->attributePersister = Shopware()->Container()->get('shopware_attribute.data_persister');
    }

    /**
     * @return Enlight_Components_Session_Namespace
     */
    private function getSession()
    {
        if ($this->session == null) {
            $this->session = Shopware()->Session();
        }
        return $this->session;
    }

    /**
     * Get a unique order number
     * @access public
     * @return string The reserved order number
     */
    public function sGetOrderNumber()
    {
        $number = $this->numberRangeIncrementer->increment('invoice');
        $number = $this->eventManager->filter(
            'Shopware_Modules_Order_GetOrdernumber_FilterOrdernumber',
            $number,
            array('subject'=>$this)
        );

        return $number;
    }

    /**
     * Check each basket row for instant downloads
     * @param $basketRow
     * @param $orderID
     * @param $orderDetailsID
     * @return array
     */
    public function handleESDOrder($basketRow, $orderID, $orderDetailsID)
    {
        $quantity = $basketRow["quantity"];
        $basketRow['assignedSerials'] = array();

        //check if current order number is an esd variant.
        $esdArticle = $this->getVariantEsd($basketRow["ordernumber"]);

        if (!$esdArticle["id"]) {
            return $basketRow;
        }

        if (!$esdArticle["serials"]) {
            // No serial number is needed
            $this->db->insert('s_order_esd', array(
                'serialID' => 0,
                'esdID' => $esdArticle["id"],
                'userID' => $this->sUserData["additional"]["user"]["id"],
                'orderID' => $orderID,
                'orderdetailsID' => $orderDetailsID,
                'datum' => new Zend_Db_Expr('NOW()'),
            ));
            return $basketRow;
        }

        $availableSerials = $this->getAvailableSerialsOfEsd($esdArticle["id"]);

        if ((count($availableSerials) <= $this->config->get('esdMinSerials')) || count($availableSerials) <= $quantity) {
            // Not enough serial numbers anymore, inform merchant
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

        // Check if enough serials are available, if not, an email has been sent, and we can return
        if (count($availableSerials) < $quantity) {
            return $basketRow;
        }

        for ($i = 1; $i <= $quantity; $i++) {
            // Assign serial number
            $serialId = $availableSerials[$i-1]["id"];

            // Update basket row
            $basketRow['assignedSerials'][] = $availableSerials[$i-1]["serialnumber"];

            $this->db->insert('s_order_esd', array(
                'serialID' => $serialId,
                'esdID' => $esdArticle["id"],
                'userID' => $this->sUserData["additional"]["user"]["id"],
                'orderID' => $orderID,
                'orderdetailsID' => $orderDetailsID,
                'datum' => new Zend_Db_Expr('NOW()'),
            ));
        }

        return $basketRow;
    }

    /**
     * Delete temporary created order
     * @access public
     */
    public function sDeleteTemporaryOrder()
    {
        $sessionId = $this->getSession()->offsetGet('sessionId');

        if (empty($sessionId)) {
            return;
        }

        $deleteWholeOrder = $this->db->fetchAll("
        SELECT * FROM s_order WHERE temporaryID = ? LIMIT 2
        ", array($this->getSession()->offsetGet('sessionId')));

        foreach ($deleteWholeOrder as $orderDelete) {
            $this->db->executeUpdate("
            DELETE FROM s_order WHERE id = ?
            ", array($orderDelete["id"]));

            $this->db->executeUpdate("
            DELETE FROM s_order_details
            WHERE orderID=?
            ", array($orderDelete["id"]));
        }
    }

    /**
     * Create temporary order (for order cancellation reports)
     * @access public
     */
    public function sCreateTemporaryOrder()
    {
        $this->sShippingData["AmountNumeric"] = $this->sShippingData["AmountNumeric"] ? $this->sShippingData["AmountNumeric"] : "0";
        if (!$this->sShippingcostsNumeric) {
            $this->sShippingcostsNumeric = "0";
        }
        if (!$this->sBasketData["AmountWithTaxNumeric"]) {
            $this->sBasketData["AmountWithTaxNumeric"] = $this->sBasketData["AmountNumeric"];
        }

        if ($this->isTaxFree(
            $this->sSYSTEM->sUSERGROUPDATA["tax"],
            $this->sSYSTEM->sUSERGROUPDATA["id"])
        ) {
            $net = "1";
        } else {
            $net = "0";
        }

        $this->sBasketData["AmountNetNumeric"] = round($this->sBasketData["AmountNetNumeric"], 2);
        if ($this->dispatchId) {
            $dispatchId = $this->dispatchId;
        } else {
            $dispatchId = "0";
        }

        $this->sBasketData["AmountNetNumeric"] = round($this->sBasketData["AmountNetNumeric"], 2);

        if (empty($this->sSYSTEM->sCurrency["currency"])) {
            $this->sSYSTEM->sCurrency["currency"] = "EUR";
        }
        if (empty($this->sSYSTEM->sCurrency["factor"])) {
            $this->sSYSTEM->sCurrency["factor"] = "1";
        }

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
        if (empty($this->sBasketData["AmountWithTaxNumeric"])) {
            $this->sBasketData["AmountWithTaxNumeric"] = '0';
        }
        if (empty($this->sBasketData["AmountNetNumeric"])) {
            $this->sBasketData["AmountNetNumeric"] = '0';
        }

        $data = array(
            'ordernumber' => '0',
            'userID' => $this->sUserData["additional"]["user"]["id"],
            'invoice_amount' => $this->sBasketData["AmountWithTaxNumeric"],
            'invoice_amount_net' => $this->sBasketData["AmountNetNumeric"],
            'invoice_shipping' => $this->sShippingcostsNumeric,
            'invoice_shipping_net' => $this->sShippingcostsNumericNet,
            'ordertime' => new Zend_Db_Expr('NOW()'),
            'status' => -1,
            'paymentID' => $this->sUserData["additional"]["user"]["paymentID"],
            'customercomment' => $this->sComment,
            'net' => $net,
            'taxfree' => $taxfree,
            'partnerID' => (string) $this->getSession()->offsetGet("sPartner"),
            'temporaryID' => $this->getSession()->offsetGet('sessionId'),
            'referer' => (string) $this->getSession()->offsetGet('sReferer'),
            'language' => $shop->getId(),
            'dispatchID' => $dispatchId,
            'currency' => $this->sSYSTEM->sCurrency["currency"],
            'currencyFactor' => $this->sSYSTEM->sCurrency["factor"],
            'subshopID' => $mainShop->getId(),
            'deviceType' => $this->deviceType
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

            if (!$basketRow["price"]) {
                $basketRow["price"] = "0,00";
            }

            $basketRow["articlename"] = html_entity_decode($basketRow["articlename"]);
            $basketRow["articlename"] = strip_tags($basketRow["articlename"]);

            $basketRow["articlename"] = $this->sSYSTEM->sMODULES['sArticles']->sOptimizeText($basketRow["articlename"]);

            if (!$basketRow["esdarticle"]) {
                $basketRow["esdarticle"] = "0";
            }
            if (!$basketRow["modus"]) {
                $basketRow["modus"] = "0";
            }
            if (!$basketRow["taxID"]) {
                $basketRow["taxID"] = "0";
            }
            if (!$basketRow["releasedate"]) {
                $basketRow["releasedate"] = '0000-00-00';
            }

            $data = array(
                'orderID' => $orderID,
                'ordernumber' => 0,
                'articleID' => $basketRow["articleID"],
                'articleordernumber' => $basketRow["ordernumber"],
                'price' => $basketRow["priceNumeric"],
                'quantity' => $basketRow["quantity"],
                'name' => $basketRow["articlename"],
                'status' => 0,
                'releasedate' => $basketRow["releasedate"],
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
        } // For every article in basket
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

        if (!$this->sShippingcostsNumeric) {
            $this->sShippingcostsNumeric = "0";
        }

        if (!$this->sBasketData["AmountWithTaxNumeric"]) {
            $this->sBasketData["AmountWithTaxNumeric"] = $this->sBasketData["AmountNumeric"];
        }

        if ($this->isTaxFree(
                $this->sSYSTEM->sUSERGROUPDATA["tax"],
                $this->sSYSTEM->sUSERGROUPDATA["id"])
        ) {
            $net = "1";
        } else {
            $net = "0";
        }

        if ($this->dispatchId) {
            $dispatchId = $this->dispatchId;
        } else {
            $dispatchId = "0";
        }

        $this->sBasketData["AmountNetNumeric"] = round($this->sBasketData["AmountNetNumeric"], 2);

        if (empty($this->sSYSTEM->sCurrency["currency"])) {
            $this->sSYSTEM->sCurrency["currency"] = "EUR";
        }
        if (empty($this->sSYSTEM->sCurrency["factor"])) {
            $this->sSYSTEM->sCurrency["factor"] = "1";
        }

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

        $partner = $this->getPartnerCode(
            $this->sUserData["additional"]["user"]["affiliate"]
        );

        $orderParams = array(
            'ordernumber'          => $orderNumber,
            'userID'               => $this->sUserData["additional"]["user"]["id"],
            'invoice_amount'       => $this->sBasketData["AmountWithTaxNumeric"],
            'invoice_amount_net'   => $this->sBasketData["AmountNetNumeric"],
            'invoice_shipping'     => floatval($this->sShippingcostsNumeric),
            'invoice_shipping_net' => floatval($this->sShippingcostsNumericNet),
            'ordertime'            => new Zend_Db_Expr('NOW()'),
            'status'               => 0,
            'cleared'              => 17,
            'paymentID'            => $this->sUserData["additional"]["user"]["paymentID"],
            'transactionID'        => (string) $this->bookingId,
            'customercomment'      => $this->sComment,
            'net'                  => $net,
            'taxfree'              => $taxfree,
            'partnerID'            => (string) $partner,
            'temporaryID'          => (string) $this->uniqueID,
            'referer'              => (string) $this->getSession()->offsetGet('sReferer'),
            'language'             => $shop->getId(),
            'dispatchID'           => $dispatchId,
            'currency'             => $this->sSYSTEM->sCurrency["currency"],
            'currencyFactor'       => $this->sSYSTEM->sCurrency["factor"],
            'subshopID'            => $mainShop->getId(),
            'remote_addr'          => (string) $_SERVER['REMOTE_ADDR'],
            'deviceType'           => $this->deviceType
        );

        $orderParams = $this->eventManager->filter('Shopware_Modules_Order_SaveOrder_FilterParams', $orderParams, array('subject' => $this));

        try {
            $this->db->beginTransaction();
            $affectedRows = $this->db->insert('s_order', $orderParams);
            $orderID = $this->db->lastInsertId();
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Enlight_Exception("Shopware Order Fatal-Error {$_SERVER["HTTP_HOST"]} :" . $e->getMessage(), 0, $e);
        }

        if (!$affectedRows || !$orderID) {
            throw new Enlight_Exception("Shopware Order Fatal-Error {$_SERVER["HTTP_HOST"]} : No rows affected or no order id created.", 0);
        }

        try {
            $paymentData = Shopware()->Modules()->Admin()->sGetPaymentMeanById($this->sUserData["additional"]["user"]["paymentID"], Shopware()->Modules()->Admin()->sGetUserData());
            $paymentClass = Shopware()->Modules()->Admin()->sInitiatePaymentClass($paymentData);
            if ($paymentClass instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
                $paymentClass->createPaymentInstance($orderID, $this->sUserData["additional"]["user"]["id"], $this->sUserData["additional"]["user"]["paymentID"]);
            }
        } catch (\Exception $e) {
            //Payment method code failure
        }

        $attributeData = [
            'attribute1' => $this->o_attr_1,
            'attribute2' => $this->o_attr_2,
            'attribute3' => $this->o_attr_3,
            'attribute4' => $this->o_attr_4,
            'attribute5' => $this->o_attr_5,
            'attribute6' => $this->o_attr_6,
        ];

        $attributeData = array_merge($attributeData, $this->orderAttributes);

        $this->attributePersister->persist($attributeData, 's_order_attributes', $orderID);
        $attributes = $this->attributeLoader->load('s_order_attributes', $orderID) ?: [];
        unset($attributes['id']);
        unset($attributes['orderID']);

        $position = 0;
        foreach ($this->sBasketData["content"] as $key => $basketRow) {
            $position++;

            $basketRow = $this->formatBasketRow($basketRow);

            $preparedQuery = "
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
                tax_rate,
                ean,
                unit,
                pack_unit
                )
                VALUES (%d, %s, %d, %s, %f, %d, %s, %d, %s, %d, %d, %d, %f, %s, %s, %s)
            ";

            $sql = sprintf($preparedQuery,
                $orderID,
                $this->db->quote((string) $orderNumber),
                $basketRow["articleID"],
                $this->db->quote((string) $basketRow["ordernumber"]),
                $basketRow["priceNumeric"],
                $basketRow["quantity"],
                $this->db->quote((string) $basketRow["articlename"]),
                0,
                $this->db->quote((string) $basketRow["releasedate"]),
                $basketRow["modus"],
                $basketRow["esdarticle"],
                $basketRow["taxID"],
                $basketRow["tax_rate"],
                $this->db->quote((string) $basketRow["ean"]),
                $this->db->quote((string) $basketRow["itemUnit"]),
                $this->db->quote((string) $basketRow["packunit"])
            );


            $sql = $this->eventManager->filter('Shopware_Modules_Order_SaveOrder_FilterDetailsSQL', $sql, array('subject'=>$this, 'row'=>$basketRow, 'user'=>$this->sUserData, 'order'=>array("id"=>$orderID, "number"=>$orderNumber)));

            // Check for individual voucher - code
            if ($basketRow["modus"] == 2) {
                //reserve the basket voucher for the current user.
                $this->reserveVoucher(
                    $basketRow["ordernumber"],
                    $this->sUserData["additional"]["user"]["id"],
                    $basketRow["articleID"]
                );
            }

            if ($basketRow["esdarticle"]) {
                $esdOrder = true;
            }


            try {
                $this->db->executeUpdate($sql);
                $orderdetailsID = $this->db->lastInsertId();
            } catch (Exception $e) {
                throw new Enlight_Exception("Shopware Order Fatal-Error {$_SERVER["HTTP_HOST"]} :" . $e->getMessage(), 0, $e);
            }

            $this->sBasketData['content'][$key]['orderDetailId'] = $orderdetailsID;

            // save attributes
            $attributeData = [
                'attribute1' => $basketRow['ob_attr1'],
                'attribute2' => $basketRow['ob_attr2'],
                'attribute3' => $basketRow['ob_attr3'],
                'attribute4' => $basketRow['ob_attr4'],
                'attribute5' => $basketRow['ob_attr5'],
                'attribute6' => $basketRow['ob_attr6'],
            ];
            $this->attributePersister->persist($attributeData, 's_order_details_attributes', $orderdetailsID);
            $detailAttributes = $this->attributeLoader->load('s_order_details_attributes', $orderdetailsID) ?: [];
            unset($detailAttributes['id']);
            unset($detailAttributes['detailID']);
            $this->sBasketData['content'][$key]['attributes'] = $detailAttributes;

            // Update sales and stock
            if ($basketRow["priceNumeric"] >= 0) {
                $this->refreshOrderedVariant(
                    $basketRow["ordernumber"],
                    $basketRow["quantity"]
                );
            }

            // For esd-articles, assign serial number if needed
            // Check if this article is esd-only (check in variants, too -> later)
            if ($basketRow["esdarticle"]) {
                $basketRow = $this->handleESDOrder($basketRow, $orderID, $orderdetailsID);

                // Add assignedSerials to basketcontent
                if (!empty($basketRow['assignedSerials'])) {
                    $this->sBasketData["content"][$key]['serials'] = $basketRow['assignedSerials'];
                }
            }
        } // For every article in basket

        $this->eventManager->notify('Shopware_Modules_Order_SaveOrder_ProcessDetails', array(
            'subject' => $this,
            'details' => $this->sBasketData['content'],
        ));

        // Save Billing and Shipping-Address to retrace in future
        $this->sSaveBillingAddress($this->sUserData["billingaddress"], $orderID);
        $this->sSaveShippingAddress($this->sUserData["shippingaddress"], $orderID);

        $this->sUserData = $this->getUserDataForMail($this->sUserData);

        $details = $this->getOrderDetailsForMail(
            $this->sBasketData["content"]
        );

        $variables = array(
            "sOrderDetails"=>$details,
            "billingaddress"=>$this->sUserData["billingaddress"],
            "shippingaddress"=>$this->sUserData["shippingaddress"],
            "additional"=>$this->sUserData["additional"],
            "sShippingCosts"=>$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sShippingcosts)." ".$this->sSYSTEM->sCurrency["currency"],
            "sAmount"=>$this->sAmountWithTax ? $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sAmountWithTax)." ".$this->sSYSTEM->sCurrency["currency"] : $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sAmount)." ".$this->sSYSTEM->sCurrency["currency"],
            "sAmountNet"=>$this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sBasketData["AmountNetNumeric"])." ".$this->sSYSTEM->sCurrency["currency"],
            "sTaxRates"   => $this->sBasketData["sTaxRates"],
            "ordernumber"=>$orderNumber,
            "sOrderDay" => date("d.m.Y"),
            "sOrderTime" => date("H:i"),
            "sComment"=>$this->sComment,
            'attributes' => $attributes,
            "sEsd"=>$esdOrder
        );

        if ($dispatchId) {
            $variables["sDispatch"] = $this->sSYSTEM->sMODULES['sAdmin']->sGetPremiumDispatch($dispatchId);
        }
        if ($this->bookingId) {
            $variables['sBookingID'] = $this->bookingId;
        }

        // Completed - Garbage basket / temporary - order
        $this->sDeleteTemporaryOrder();

        $this->db->executeUpdate("DELETE FROM s_order_basket WHERE sessionID=?", array($this->getSession()->offsetGet('sessionId')));

        $confirmMailDeliveryFailed = false;
        try {
            $this->sendMail($variables);
        } catch (\Exception $e) {
            $confirmMailDeliveryFailed = true;
            $email = $this->sUserData['additional']['user']['email'];
            $this->logOrderMailException($e, $orderNumber, $email);
        }

        // Check if voucher is affected
        $this->sTellFriend();

        if ($this->getSession()->offsetExists('sOrderVariables')) {
            $variables = $this->getSession()->offsetGet('sOrderVariables');
            $variables['sOrderNumber'] = $orderNumber;
            $variables['confirmMailDeliveryFailed'] = $confirmMailDeliveryFailed;
            $this->getSession()->offsetSet('sOrderVariables', $variables);
        }

        return $orderNumber;
    }

    /**
     * Helper function which returns the esd definition of the passed variant
     * order number.
     * Used for the sManageEsd function to check if the current order article variant
     * is an esd variant.
     * @param $orderNumber
     * @return array|false
     */
    private function getVariantEsd($orderNumber)
    {
        return $this->db->fetchRow(
            "SELECT s_articles_esd.id AS id, serials
            FROM  s_articles_esd, s_articles_details
            WHERE s_articles_esd.articleID = s_articles_details.articleID
            AND   articledetailsID = s_articles_details.id
            AND   s_articles_details.ordernumber= :orderNumber",
            array(':orderNumber' => $orderNumber)
        );
    }

    /**
     * Helper function which returns all available esd serials for the passed esd id.
     *
     * @param $esdId
     * @return array
     */
    private function getAvailableSerialsOfEsd($esdId)
    {
        return $this->db->fetchAll(
            "SELECT s_articles_esd_serials.id AS id, s_articles_esd_serials.serialnumber as serialnumber
            FROM s_articles_esd_serials
            LEFT JOIN s_order_esd
              ON (s_articles_esd_serials.id = s_order_esd.serialID)
            WHERE s_order_esd.serialID IS NULL
            AND s_articles_esd_serials.esdID= :esdId",
            array('esdId' => $esdId)
        );
    }

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
    private function getPartnerCode($userAffiliate)
    {
        $isPartner = $this->getSession()->offsetGet("sPartner");
        if (!empty($isPartner)) {
            return $this->getSession()->offsetGet("sPartner");
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
     * This function updates the data for an ordered variant.
     * The variant sales value will be increased by the passed quantity
     * and the variant stock value decreased by the passed quantity.
     *
     * @param string $orderNumber
     * @param int $quantity
     */
    private function refreshOrderedVariant($orderNumber, $quantity)
    {
        $this->db->executeUpdate("
            UPDATE s_articles_details
            SET sales = sales + :quantity,
                instock = instock - :quantity
            WHERE ordernumber = :number",
            array(':quantity' => $quantity, ':number' => $orderNumber)
        );

        $this->eventManager->notify(
            'product_stock_was_changed',
            ['number' => $orderNumber, 'quantity' => $quantity]
        );
    }

    /**
     * Small helper function which iterates all basket rows
     * and formats the article name and order number.
     * This function is used for the order status mail.
     *
     * @param $basketRows
     * @return array
     */
    private function getOrderDetailsForMail($basketRows)
    {
        $details = array();
        foreach ($basketRows as $content) {
            $content["articlename"] = trim(html_entity_decode($content["articlename"]));
            $content["articlename"] = str_replace(array("<br />", "<br>"), "\n", $content["articlename"]);
            $content["articlename"] = str_replace("&euro;", "€", $content["articlename"]);
            $content["articlename"] = trim($content["articlename"]);

            while (strpos($content["articlename"], "\n\n")!==false) {
                $content["articlename"] = str_replace("\n\n", "\n", $content["articlename"]);
            }

            $content["ordernumber"] = trim(html_entity_decode($content["ordernumber"]));

            $details[] = $content;
        }
        return $details;
    }

    /**
     * Helper function which returns order details for the
     * order status mail.
     * Additionally to the order details rows, this function returns
     * the order detail attributes for each position.
     *
     * @param $orderId
     * @return array
     */
    private function getOrderDetailsForStatusMail($orderId)
    {
        $orderDetails = $this->getOrderDetailsByOrderId($orderId);

        // add attributes to orderDetails
        foreach ($orderDetails as &$orderDetail) {
            $attributes = $this->attributeLoader->load('s_order_details_attributes', $orderDetail['orderdetailsID']) ?: [];
            unset($attributes['id']);
            unset($attributes['detailID']);
            $orderDetail['attributes'] = $attributes;
        }
        return $orderDetails;
    }

    /**
     * Helper function which get formated order data for the passed order id.
     * This function is used if the order status changed and the status mail will be
     * send.
     *
     * @param $orderId
     * @return mixed
     */
    private function getOrderForStatusMail($orderId)
    {
        $order = $this->getOrderById($orderId);
        $attributes = $this->attributeLoader->load('s_order_attributes', $orderId) ?: [];
        unset($attributes['id']);
        unset($attributes['orderID']);
        $order['attributes'] = $attributes;

        return $order;
    }

    /**
     * Helper function which converts all HTML entities, in the passed user data array,
     * to their applicable characters.
     *
     * @param $userData
     * @return array
     */
    private function getUserDataForMail($userData)
    {
        foreach ($userData["billingaddress"] as $key => $value) {
            $userData["billingaddress"][$key] = html_entity_decode($value);
        }
        foreach ($userData["shippingaddress"] as $key => $value) {
            $userData["shippingaddress"][$key] = html_entity_decode($value);
        }
        foreach ($userData["additional"]["country"] as $key => $value) {
            $userData["additional"]["country"][$key] = html_entity_decode($value);
        }

        $userData["additional"]["payment"]["description"] = html_entity_decode(
            $userData["additional"]["payment"]["description"]
        );
        return $userData;
    }

    /**
     * Helper function for the sSaveOrder which formats a single
     * basket row.
     * This function sets the default for different properties, which
     * might not be set or invalid.
     *
     * @param $basketRow
     * @return mixed
     */
    private function formatBasketRow($basketRow)
    {
        $basketRow["articlename"] = str_replace("<br />", "\n", $basketRow["articlename"]);
        $basketRow["articlename"] = html_entity_decode($basketRow["articlename"]);
        $basketRow["articlename"] = strip_tags($basketRow["articlename"]);
        $basketRow["articlename"] = Shopware()->Modules()->Articles()->sOptimizeText(
            $basketRow["articlename"]
        );

        if (!$basketRow["price"]) {
            $basketRow["price"] = "0,00";
        }
        if (!$basketRow["esdarticle"]) {
            $basketRow["esdarticle"] = "0";
        }
        if (!$basketRow["modus"]) {
            $basketRow["modus"] = "0";
        }
        if (!$basketRow["taxID"]) {
            $basketRow["taxID"] = "0";
        }
        if ($this->sNet == true) {
            $basketRow["taxID"] = "0";
        }
        if (!$basketRow["ean"]) {
            $basketRow["ean"] = '';
        }
        if (!$basketRow["releasedate"]) {
            $basketRow["releasedate"] = '0000-00-00';
        }

        return $basketRow;
    }

    /**
     * send order confirmation mail
     * @access public
     */
    public function sendMail($variables)
    {
        $variables = $this->eventManager->filter(
            'Shopware_Modules_Order_SendMail_FilterVariables',
            $variables,
            array('subject' => $this)
        );

        $shopContext = $this->contextService->getShopContext();

        $context = array(
            'sOrderDetails' => $variables["sOrderDetails"],

            'billingaddress'  => $variables["billingaddress"],
            'shippingaddress' => $variables["shippingaddress"],
            'additional'      => $variables["additional"],

            'sTaxRates'      => $variables["sTaxRates"],
            'sShippingCosts' => $variables["sShippingCosts"],
            'sAmount'        => $variables["sAmount"],
            'sAmountNet'     => $variables["sAmountNet"],

            'sOrderNumber' => $variables["ordernumber"],
            'sOrderDay'    => $variables["sOrderDay"],
            'sOrderTime'   => $variables["sOrderTime"],
            'sComment'     => $variables["sComment"],

            'attributes'     => $variables["attributes"],
            'sCurrency'    => $this->sSYSTEM->sCurrency["currency"],

            'sLanguage'    => $shopContext->getShop()->getId(),

            'sSubShop'     => $shopContext->getShop()->getId(),

            'sEsd'    => $variables["sEsd"],
            'sNet'    => $this->sNet,
        );

        // Support for individual payment means with custom-tables
        if ($variables["additional"]["payment"]["table"]) {
            $paymentTable = $this->db->fetchRow("
                  SELECT * FROM {$variables["additional"]["payment"]["table"]}
                  WHERE userID=?",
                array($variables["additional"]["user"]["id"])
            );
            $context["sPaymentTable"] = $paymentTable ? : array();
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
     * @param array $address
     * @param int $id
     * @return int
     * @throws Exception
     */
    public function sSaveBillingAddress($address, $id)
    {
        /** @var Customer $customer */
        $customer = Shopware()->Container()->get('models')->find(Customer::class, $address['userID']);

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
            zipcode,
            city,
            phone,
            countryID,
            stateID,
            ustid,
            additional_address_line1,
            additional_address_line2,
            title
        )
        VALUES (
            :userID,
            :orderID,
            :customernumber,
            :company,
            :department,
            :salutation,
            :firstname,
            :lastname,
            :street,
            :zipcode,
            :city,
            :phone,
            :countryID,
            :stateID,
            :ustid,
            :additional_address_line1,
            :additional_address_line2,
            :title
            )
        ";
        $sql = $this->eventManager->filter('Shopware_Modules_Order_SaveBilling_FilterSQL', $sql, array('subject'=>$this, 'address'=>$address, 'id'=>$id));
        $array = array(
            ':userID' => $address["userID"],
            ':orderID' => $id,
            ':customernumber' => $customer->getNumber(),
            ':company' => (string) $address["company"],
            ':department' => (string) $address["department"],
            ':salutation' => (string) $address["salutation"],
            ':firstname' => (string) $address["firstname"],
            ':lastname' => (string) $address["lastname"],
            ':street' => (string) $address["street"],
            ':zipcode' => (string) $address["zipcode"],
            ':city' => (string) $address["city"],
            ':phone' => (string) $address["phone"],
            ':countryID' => $address["countryID"],
            ':stateID' => $address["stateID"],
            ':ustid' => $address["ustid"],
            ':additional_address_line1' => $address["additional_address_line1"],
            ':additional_address_line2' => $address["additional_address_line2"],
            ':title' => $address["title"]
        );
        $array = $this->eventManager->filter('Shopware_Modules_Order_SaveBilling_FilterArray', $array, array('subject'=>$this, 'address'=>$address, 'id'=>$id));
        $result = $this->db->executeUpdate($sql, $array);

        $billingID = $this->db->lastInsertId();

        $billingAddressId = null;

        if ($this->session !== null) {
            $billingAddressId = $this->session->get('checkoutBillingAddressId');
        }

        if ($billingAddressId === null) {
            $billingAddressId = $customer->getDefaultBillingAddress()->getId();
        }

        $attributes = $this->attributeLoader->load('s_user_addresses_attributes', $billingAddressId);

        if (!is_array($attributes)) {
            $attributes = [];
        }

        $this->attributePersister->persist($attributes, 's_order_billingaddress_attributes', $billingID);

        return $result;
    }

    /**
     * save order shipping address
     * @access public
     * @param array $address
     * @param int $id
     * @return int
     * @throws Exception
     */
    public function sSaveShippingAddress($address, $id)
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
            zipcode,
            city,
            countryID,
            stateID,
            additional_address_line1,
            additional_address_line2,
            title
        )
        VALUES (
            :userID,
            :orderID,
            :company,
            :department,
            :salutation,
            :firstname,
            :lastname,
            :street,
            :zipcode,
            :city,
            :countryID,
            :stateID,
            :additional_address_line1,
            :additional_address_line2,
            :title
            )
        ";
        $sql = $this->eventManager->filter('Shopware_Modules_Order_SaveShipping_FilterSQL', $sql, array('subject'=>$this, 'address'=>$address, 'id'=>$id));
        $array = array(
            ':userID' => $address["userID"],
            ':orderID' => $id,
            ':company' => (string) $address["company"],
            ':department' => (string) $address["department"],
            ':salutation' => (string) $address["salutation"],
            ':firstname' => (string) $address["firstname"],
            ':lastname' => (string) $address["lastname"],
            ':street' => (string) $address["street"],
            ':zipcode' => (string) $address["zipcode"],
            ':city' => (string) $address["city"],
            ':countryID' => $address["countryID"],
            ':stateID' => $address["stateID"],
            ':additional_address_line1' => (string) $address["additional_address_line1"],
            ':additional_address_line2' => (string) $address["additional_address_line2"],
            ':title' => (string) $address["title"]
        );
        $array = $this->eventManager->filter('Shopware_Modules_Order_SaveShipping_FilterArray', $array, array('subject'=>$this, 'address'=>$address, 'id'=>$id));
        $result = $this->db->executeUpdate($sql, $array);

        $shippingId = $this->db->lastInsertId();

        $shippingAddressId = null;

        if ($this->session !== null) {
            $shippingAddressId = $this->session->get('checkoutShippingAddressId');
        }

        if ($shippingAddressId === null) {
            /** @var Customer $customer */
            $customer = Shopware()->Models()->getRepository('Shopware\Models\Customer\Customer')->find($address['userID']);
            $shippingAddressId = $customer->getDefaultShippingAddress()->getId();
        }

        $attributes = $this->attributeLoader->load('s_user_addresses_attributes', $shippingAddressId) ?: [];

        $this->attributePersister->persist($attributes, 's_order_shippingaddress_attributes', $shippingId);

        return $result;
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
        if ($checkIfUserFound) {
            $this->db->executeUpdate("
            UPDATE s_emarketing_tellafriend SET confirmed=1 WHERE recipient=?
            ", array($checkMail));

            $advertiser = $this->db->fetchRow("
            SELECT email, firstname, lastname FROM s_user
            WHERE s_user.id=?
            ", array($checkIfUserFound["sender"]));

            if (!$advertiser) {
                return;
            }

            $context = array(
                'customer'     => $advertiser["firstname"] . " " . $advertiser["lastname"],
                'user'         => $this->sUserData["billingaddress"]["firstname"] . " " . $this->sUserData["billingaddress"]["lastname"],
                'voucherValue' => $this->config->get('sVOUCHERTELLFRIENDVALUE'),
                'voucherCode'  => $this->config->get('sVOUCHERTELLFRIENDCODE')
            );

            $mail = Shopware()->TemplateMail()->createMail('sVOUCHER', $context);
            $mail->addTo($advertiser["email"]);
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

        $order = $this->getOrderForStatusMail($orderId);
        $orderDetails = $this->getOrderDetailsForStatusMail($orderId);

        if (!empty($order['dispatchID'])) {
            $dispatch = $this->db->fetchRow('
                SELECT name, description FROM s_premium_dispatch
                WHERE id=?
            ', array($order['dispatchID']));
        }

        $user = $this->getCustomerInformationByOrderId($orderId);

        if (empty($order) || empty($orderDetails) || empty($user)) {
            return;
        }

        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shopId = is_numeric($order['language']) ? $order['language'] : $order['subshopID'];
        $shop = $repository->getActiveById($shopId);
        $shop->registerResources();

        $order['status_description'] = Shopware()->Snippets()->getNamespace('backend/static/order_status')->get(
            $order['status_name'],
            $order['status_description']
        );
        $order['cleared_description'] = Shopware()->Snippets()->getNamespace('backend/static/payment_status')->get(
            $order['cleared_name'],
            $order['cleared_description']
        );

        /* @var $mailModel \Shopware\Models\Mail\Mail */
        $mailModel = Shopware()->Models()->getRepository('Shopware\Models\Mail\Mail')->findOneBy(
            array('name' => $templateName)
        );

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
        $previousStatusId = $this->getOrderPaymentStatus($orderId);
        if ($paymentStatusId == $previousStatusId) {
            return;
        }

        $this->db->executeUpdate(
            'UPDATE s_order SET cleared = :paymentStatus WHERE id = :orderId;',
            array(
                'paymentStatus' => $paymentStatusId,
                'orderId' => $orderId
            )
        );

        $sql = '
           INSERT INTO s_order_history (
              orderID, userID, previous_order_status_id, order_status_id,
              previous_payment_status_id, payment_status_id, comment, change_date )
            SELECT id, NULL, status, status, :previousStatus, :currentStatus, :comment, NOW() FROM s_order WHERE id = :orderId
        ';

        $this->db->executeUpdate($sql, array(
            ':previousStatus' => $previousStatusId,
            ':currentStatus' => $paymentStatusId,
            ':comment' => $comment,
            ':orderId' => $orderId
        ));

        if ($sendStatusMail) {
            $mail = $this->createStatusMail($orderId, $paymentStatusId);
            if ($mail) {
                $this->sendStatusMail($mail);
            }
        }
    }

    /**
     * Helper function which returns the current payment status
     * of the passed order.
     * @param $orderId
     * @return string
     */
    private function getOrderPaymentStatus($orderId)
    {
        return $this->db->fetchOne(
            'SELECT cleared FROM s_order WHERE id= :orderId;',
            array(':orderId' => $orderId)
        );
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
        $previousStatusId = $this->getOrderStatus($orderId);

        if ($orderStatusId == $previousStatusId) {
            return;
        }

        $this->db->executeUpdate(
            'UPDATE s_order SET status = :status WHERE id = :orderId;',
            array(':status' => $orderStatusId, ':orderId' => $orderId)
        );

        $sql = '
           INSERT INTO s_order_history (
              orderID, userID, previous_order_status_id, order_status_id,
              previous_payment_status_id, payment_status_id, comment, change_date )
            SELECT id, NULL, :previousStatus, :currentStatus, cleared, cleared, :comment, NOW() FROM s_order WHERE id = :orderId
        ';

        $this->db->executeUpdate($sql, array(
            ':previousStatus' => $previousStatusId,
            ':currentStatus' => $orderStatusId,
            ':comment' => $comment,
            ':orderId' => $orderId
        ));

        if ($sendStatusMail) {
            $mail = $this->createStatusMail($orderId, $orderStatusId);
            if ($mail) {
                $this->sendStatusMail($mail);
            }
        }
    }

    /**
     * Helper function which returns the current order status of the passed order
     * id.
     *
     * @param $orderId
     * @return string
     */
    private function getOrderStatus($orderId)
    {
        return $this->db->fetchOne(
            'SELECT status FROM s_order WHERE id= :orderId;',
            array(':orderId' => $orderId)
        );
    }

    /**
     * Setter for config
     *
     * @param \Shopware_Components_Config $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Getter for config
     *
     * @return \Shopware_Components_Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Replacement for: Shopware()->Api()->Export()->sGetOrders(array('orderID' => $orderId));
     *
     * @param int $orderId
     * @return array|false
     */
    public function getOrderById($orderId)
    {
        $sql = <<<EOT
SELECT
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
    `c`.`name` as `cleared_name`,
    `c`.`description` as `cleared_description`,
    `s`.`name` as `status_name`,
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
LEFT JOIN `s_premium_dispatch` as `d`
    ON	(`o`.`dispatchID` = `d`.`id`)
LEFT JOIN `s_core_currencies` as `cu`
    ON	(`o`.`currency` = `cu`.`currency`)
WHERE
    `o`.`id` = :orderId
EOT;

        $row = $this->db->fetchRow($sql, ['orderId' => $orderId]);

        return $row;
    }

    /**
     * Replacement for: Shopware()->Api()->Export()->sOrderDetails(array('orderID' => $orderId));
     *
     * Returns order details for a given orderId
     *
     * @param int $orderId
     * @return array
     */
    public function getOrderDetailsByOrderId($orderId)
    {
        $sql = <<<EOT
SELECT
    `d`.`id` as `orderdetailsID`,
    `d`.`orderID` as `orderID`,
    `d`.`ordernumber`,
    `d`.`articleID`,
    `d`.`articleordernumber`,
    `d`.`price` as `price`,
    `d`.`quantity` as `quantity`,
    `d`.`price`*`d`.`quantity` as `invoice`,
    `d`.`name`,
    `d`.`status`,
    `d`.`shipped`,
    `d`.`shippedgroup`,
    `d`.`releasedate`,
    `d`.`modus`,
    `d`.`esdarticle`,
    `d`.`taxID`,
    `t`.`tax`,
    `d`.`tax_rate`,
    `d`.`esdarticle` as `esd`
FROM
    `s_order_details` as `d`
LEFT JOIN
    `s_core_tax` as `t`
ON
    `t`.`id` = `d`.`taxID`
WHERE
    `d`.`orderID` = :orderId
ORDER BY
    `orderdetailsID` ASC
EOT;

        $rows = $this->db->fetchAll($sql, ['orderId' => $orderId]);

        return $rows;
    }

    /**
     * Replacement for: Shopware()->Api()->Export()->sOrderCustomers(array('orderID' => $orderId));
     *
     * @param $orderId
     * @return array|false
     */
    public function getCustomerInformationByOrderId($orderId)
    {
        $sql = <<<EOT
SELECT
    `b`.`company` AS `billing_company`,
    `b`.`department` AS `billing_department`,
    `b`.`salutation` AS `billing_salutation`,
    `u`.`customernumber`,
    `b`.`firstname` AS `billing_firstname`,
    `b`.`lastname` AS `billing_lastname`,
    `b`.`street` AS `billing_street`,
    `b`.`additional_address_line1` AS `billing_additional_address_line1`,
    `b`.`additional_address_line2` AS `billing_additional_address_line2`,
    `b`.`zipcode` AS `billing_zipcode`,
    `b`.`city` AS `billing_city`,
    `b`.`phone` AS `phone`,
    `b`.`phone` AS `billing_phone`,
    `b`.`countryID` AS `billing_countryID`,
    `b`.`stateID` AS `billing_stateID`,
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
    `s`.`additional_address_line1` AS `shipping_additional_address_line1`,
    `s`.`additional_address_line2` AS `shipping_additional_address_line2`,
    `s`.`zipcode` AS `shipping_zipcode`,
    `s`.`city` AS `shipping_city`,
    `s`.`stateID` AS `shipping_stateID`,
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
    `b`.`orderID`=:orderId
EOT;

        $row = $this->db->fetchRow($sql, ['orderId' => $orderId]);

        return $row;
    }

    /**
     * @param \Exception $e
     * @param string     $orderNumber
     * @param string     $email
     */
    private function logOrderMailException(\Exception $e, $orderNumber, $email)
    {
        $message = sprintf(
            "Could not send order mail for ordernumber %s to address %s",
            $orderNumber,
            $email
        );

        $context = array('exception' => $e);
        Shopware()->Container()->get('corelogger')->error($message, $context);
    }
}
