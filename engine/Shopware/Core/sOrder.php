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

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\NumberRangeIncrementerInterface;
use Shopware\Models\Customer\Customer;
use Shopware\Models\Mail\Mail;
use Shopware\Models\Shop\Shop;

/**
 * Deprecated Shopware Class that handles frontend orders
 */
class sOrder implements \Enlight_Hook
{
    /**
     * Array with user data
     *
     * @var array
     */
    public $sUserData;

    /**
     * Array with basket data
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
     * @deprecated in 5.6, will be removed in 5.7 without replacement
     * Payment-mean object
     *
     * @var object
     */
    public $paymentObject;

    /**
     * Total amount net
     *
     * @var float
     */
    public $sAmountNet;

    /**
     * Total amount
     *
     * @var float
     */
    public $sAmount;

    /**
     * Total amount with tax (force)
     *
     * @var float
     */
    public $sAmountWithTax;

    /**
     * Shipping costs
     *
     * @var float
     */
    public $sShippingcosts;

    /**
     * Shipping costs un-formatted
     *
     * @var float
     */
    public $sShippingcostsNumeric;

    /**
     * Shipping costs net un-formatted
     *
     * @var float
     */
    public $sShippingcostsNumericNet;

    /**
     * Pointer to sSystem object
     *
     * @var \sSystem
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
     * ID of chosen dispatch
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
     * Net order true/false
     *
     * @var bool
     */
    public $sNet;    // Completely taxfree

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
     * @var Shopware\Components\Model\ModelManager
     */
    private $modelManager;

    /**
     * Injects all dependencies which are required for this class.
     *
     * @param ContextServiceInterface $contextService
     *
     * @throws Exception
     */
    public function __construct(
        ContextServiceInterface $contextService = null
    ) {
        $container = Shopware()->Container();

        $this->db = Shopware()->Db();
        $this->eventManager = Shopware()->Events();
        $this->config = Shopware()->Config();
        $this->numberRangeIncrementer = $container->get('shopware.number_range_incrementer');

        $this->contextService = $contextService ?: $container->get('shopware_storefront.context_service');
        $this->attributeLoader = $container->get('shopware_attribute.data_loader');
        $this->attributePersister = $container->get('shopware_attribute.data_persister');
        $this->modelManager = $container->get('models');
    }

    /**
     * Get a unique order number
     *
     * @return string The reserved order number
     */
    public function sGetOrderNumber()
    {
        $number = $this->numberRangeIncrementer->increment('invoice');
        $number = $this->eventManager->filter(
            'Shopware_Modules_Order_GetOrdernumber_FilterOrdernumber',
            $number,
            ['subject' => $this]
        );

        return (string) $number;
    }

    /**
     * Check each basket row for instant downloads
     *
     * @param array $basketRow
     * @param int   $orderID
     * @param int   $orderDetailsID
     *
     * @return array
     */
    public function handleESDOrder($basketRow, $orderID, $orderDetailsID)
    {
        $quantity = $basketRow['quantity'];
        $basketRow['assignedSerials'] = [];

        // Check if current order number is an esd variant.
        $esdProduct = $this->getVariantEsd($basketRow['ordernumber']);

        if (!$esdProduct['id']) {
            return $basketRow;
        }

        if (!$esdProduct['serials']) {
            // No serial number is needed
            $this->db->insert('s_order_esd', [
                'serialID' => 0,
                'esdID' => $esdProduct['id'],
                'userID' => $this->sUserData['additional']['user']['id'],
                'orderID' => $orderID,
                'orderdetailsID' => $orderDetailsID,
                'datum' => new Zend_Db_Expr('NOW()'),
            ]);

            return $basketRow;
        }

        $availableSerials = $this->getAvailableSerialsOfEsd($esdProduct['id']);

        if ((count($availableSerials) <= $this->config->get('esdMinSerials')) || count($availableSerials) <= $quantity) {
            // Not enough serial numbers anymore, inform merchant
            $context = [
                'sArticleName' => $basketRow['articlename'],
                'sMail' => $this->sUserData['additional']['user']['email'],
            ];

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

        for ($i = 1; $i <= $quantity; ++$i) {
            // Assign serial number
            $serialId = $availableSerials[$i - 1]['id'];

            // Update basket row
            $basketRow['assignedSerials'][] = $availableSerials[$i - 1]['serialnumber'];

            $this->db->insert('s_order_esd', [
                'serialID' => $serialId,
                'esdID' => $esdProduct['id'],
                'userID' => $this->sUserData['additional']['user']['id'],
                'orderID' => $orderID,
                'orderdetailsID' => $orderDetailsID,
                'datum' => new Zend_Db_Expr('NOW()'),
            ]);
        }

        return $basketRow;
    }

    /**
     * Delete temporary created order
     */
    public function sDeleteTemporaryOrder()
    {
        $sessionId = $this->getSession()->offsetGet('sessionId');

        if (empty($sessionId)) {
            return;
        }

        $deleteWholeOrder = $this->db->fetchAll('
        SELECT * FROM s_order WHERE temporaryID = ? LIMIT 2
        ', [$this->getSession()->offsetGet('sessionId')]);

        foreach ($deleteWholeOrder as $orderDelete) {
            $this->db->executeUpdate('
            DELETE FROM s_order WHERE id = ?
            ', [$orderDelete['id']]);

            $this->db->executeUpdate('
            DELETE FROM s_order_details
            WHERE orderID=?
            ', [$orderDelete['id']]);
        }
    }

    /**
     * Create temporary order (for order cancellation reports)
     *
     * @throws Enlight_Exception
     */
    public function sCreateTemporaryOrder()
    {
        $this->sShippingData['AmountNumeric'] = $this->sShippingData['AmountNumeric'] ? $this->sShippingData['AmountNumeric'] : '0';
        if (!$this->sShippingcostsNumeric) {
            $this->sShippingcostsNumeric = 0.;
        }
        if (!$this->sBasketData['AmountWithTaxNumeric']) {
            $this->sBasketData['AmountWithTaxNumeric'] = $this->sBasketData['AmountNumeric'];
        }

        $net = '0';
        if ($this->isTaxFree(
            $this->sSYSTEM->sUSERGROUPDATA['tax'],
            $this->sSYSTEM->sUSERGROUPDATA['id']
        )) {
            $net = '1';
        }

        $dispatchId = '0';
        $this->sBasketData['AmountNetNumeric'] = round($this->sBasketData['AmountNetNumeric'], 2);
        if ($this->dispatchId) {
            $dispatchId = $this->dispatchId;
        }

        $this->sBasketData['AmountNetNumeric'] = round($this->sBasketData['AmountNetNumeric'], 2);

        if (empty($this->sBasketData['sCurrencyName'])) {
            $this->sBasketData['sCurrencyName'] = 'EUR';
        }
        if (empty($this->sBasketData['sCurrencyFactor'])) {
            $this->sBasketData['sCurrencyFactor'] = '1';
        }

        $shop = Shopware()->Shop();
        $mainShop = $shop->getMain() !== null ? $shop->getMain() : $shop;

        $taxfree = '0';
        if (!empty($this->sNet)) {
            // Complete net delivery
            $net = '1';
            $this->sBasketData['AmountWithTaxNumeric'] = $this->sBasketData['AmountNetNumeric'];
            $this->sShippingcostsNumeric = $this->sShippingcostsNumericNet;
            $taxfree = '1';
        }
        if (empty($this->sBasketData['AmountWithTaxNumeric'])) {
            $this->sBasketData['AmountWithTaxNumeric'] = '0';
        }
        if (empty($this->sBasketData['AmountNetNumeric'])) {
            $this->sBasketData['AmountNetNumeric'] = '0';
        }

        $data = [
            'ordernumber' => '0',
            'userID' => $this->sUserData['additional']['user']['id'],
            'invoice_amount' => $this->sBasketData['AmountWithTaxNumeric'],
            'invoice_amount_net' => $this->sBasketData['AmountNetNumeric'],
            'invoice_shipping' => $this->sShippingcostsNumeric,
            'invoice_shipping_net' => $this->sShippingcostsNumericNet,
            'ordertime' => new Zend_Db_Expr('NOW()'),
            'status' => -1,
            'paymentID' => $this->getPaymentId(),
            'customercomment' => $this->sComment,
            'net' => $net,
            'taxfree' => $taxfree,
            'partnerID' => (string) $this->getSession()->offsetGet('sPartner'),
            'temporaryID' => $this->getSession()->offsetGet('sessionId'),
            'referer' => (string) $this->getSession()->offsetGet('sReferer'),
            'language' => $shop->getId(),
            'dispatchID' => $dispatchId,
            'currency' => $this->sBasketData['sCurrencyName'],
            'currencyFactor' => $this->sBasketData['sCurrencyFactor'],
            'subshopID' => $mainShop->getId(),
            'deviceType' => $this->deviceType,
        ];

        try {
            $affectedRows = $this->db->insert('s_order', $data);
            $orderID = $this->db->lastInsertId();
        } catch (Exception $e) {
            throw new Enlight_Exception(sprintf('##sOrder-sTemporaryOrder-#01:%s', $e->getMessage()), 0, $e);
        }
        if (!$affectedRows || !$orderID) {
            throw new Enlight_Exception('##sOrder-sTemporaryOrder-#01: No rows affected or no order id saved', 0);
        }

        // Create order attributes
        $this->attributePersister->persist($this->orderAttributes, 's_order_attributes', $orderID);

        foreach ($this->sBasketData['content'] as $basketRow) {
            if (!$basketRow['price']) {
                $basketRow['price'] = '0,00';
            }

            $basketRow['articlename'] = html_entity_decode($basketRow['articlename']);
            $basketRow['articlename'] = strip_tags($basketRow['articlename']);

            $basketRow['articlename'] = $this->sSYSTEM->sMODULES['sArticles']->sOptimizeText($basketRow['articlename']);

            if (!$basketRow['esdarticle']) {
                $basketRow['esdarticle'] = '0';
            }
            if (!$basketRow['modus']) {
                $basketRow['modus'] = '0';
            }
            if (!$basketRow['taxID']) {
                $basketRow['taxID'] = '0';
            }
            if (!$basketRow['releasedate']) {
                $basketRow['releasedate'] = '0000-00-00';
            }

            $data = [
                'orderID' => $orderID,
                'ordernumber' => 0,
                'articleID' => $basketRow['articleID'],
                'articleDetailID' => $basketRow['additional_details']['articleDetailsID'],
                'articleordernumber' => $basketRow['ordernumber'],
                'price' => $basketRow['priceNumeric'],
                'quantity' => $basketRow['quantity'],
                'name' => $basketRow['articlename'],
                'status' => 0,
                'releasedate' => $basketRow['releasedate'],
                'modus' => $basketRow['modus'],
                'esdarticle' => $basketRow['esdarticle'],
                'taxID' => $basketRow['taxID'],
                'tax_rate' => $basketRow['tax_rate'],
            ];

            try {
                $this->db->insert('s_order_details', $data);
                $orderDetailId = $this->db->lastInsertId();
            } catch (Exception $e) {
                throw new Enlight_Exception(
                    sprintf('##sOrder-sTemporaryOrder-Position-#02:%s', $e->getMessage()),
                    0,
                    $e
                );
            }

            // Create order detail attributes
            $attributeData = $this->attributeLoader->load('s_order_basket_attributes', $basketRow['id']);
            $this->attributePersister->persist($attributeData, 's_order_details_attributes', $orderDetailId);
        } // For every product in basket
    }

    /**
     * Finally save order and send order confirmation to customer
     */
    public function sSaveOrder()
    {
        $this->sComment = stripslashes($this->sComment);
        $this->sComment = stripcslashes($this->sComment);

        $this->sShippingData['AmountNumeric'] = $this->sShippingData['AmountNumeric'] ?: '0';

        if ($this->isTransactionExist($this->bookingId)) {
            return false;
        }

        // Insert basic-data of the order
        $orderNumber = $this->sGetOrderNumber();
        $this->sOrderNumber = $orderNumber;

        if (!$this->sShippingcostsNumeric) {
            $this->sShippingcostsNumeric = 0.;
        }

        if (!$this->sBasketData['AmountWithTaxNumeric']) {
            $this->sBasketData['AmountWithTaxNumeric'] = $this->sBasketData['AmountNumeric'];
        }

        if ($this->isTaxFree($this->sSYSTEM->sUSERGROUPDATA['tax'], $this->sSYSTEM->sUSERGROUPDATA['id'])) {
            $net = '1';
        } else {
            $net = '0';
        }

        if ($this->dispatchId) {
            $dispatchId = $this->dispatchId;
        } else {
            $dispatchId = '0';
        }

        $this->sBasketData['AmountNetNumeric'] = round($this->sBasketData['AmountNetNumeric'], 2);

        if (empty($this->sBasketData['sCurrencyName'])) {
            $this->sBasketData['sCurrencyName'] = 'EUR';
        }
        if (empty($this->sBasketData['sCurrencyFactor'])) {
            $this->sBasketData['sCurrencyFactor'] = '1';
        }

        $shop = Shopware()->Shop();
        $mainShop = $shop->getMain() !== null ? $shop->getMain() : $shop;

        $taxfree = '0';
        if (!empty($this->sNet)) {
            // Complete net delivery
            $net = '1';
            $this->sBasketData['AmountWithTaxNumeric'] = $this->sBasketData['AmountNetNumeric'];
            $this->sShippingcostsNumeric = $this->sShippingcostsNumericNet;
            $taxfree = '1';
        }

        $partner = $this->getPartnerCode(
            $this->sUserData['additional']['user']['affiliate']
        );

        $ip = Shopware()->Container()->get('shopware.components.privacy.ip_anonymizer')
            ->anonymize(
                (string) Shopware()->Container()->get('request_stack')->getCurrentRequest()->getClientIp()
            );

        $orderParams = [
            'ordernumber' => $orderNumber,
            'userID' => $this->sUserData['additional']['user']['id'],
            'invoice_amount' => $this->sBasketData['AmountWithTaxNumeric'],
            'invoice_amount_net' => $this->sBasketData['AmountNetNumeric'],
            'invoice_shipping' => (float) $this->sShippingcostsNumeric,
            'invoice_shipping_net' => (float) $this->sShippingcostsNumericNet,
            'invoice_shipping_tax_rate' => isset($this->sBasketData['sShippingcostsTaxProportional']) ? 0 : $this->sBasketData['sShippingcostsTax'],
            'ordertime' => new Zend_Db_Expr('NOW()'),
            'changed' => new Zend_Db_Expr('NOW()'),
            'status' => 0,
            'cleared' => 17,
            'paymentID' => $this->getPaymentId(),
            'transactionID' => (string) $this->bookingId,
            'customercomment' => $this->sComment,
            'net' => $net,
            'taxfree' => $taxfree,
            'partnerID' => (string) $partner,
            'temporaryID' => (string) $this->uniqueID,
            'referer' => (string) $this->getSession()->offsetGet('sReferer'),
            'language' => $shop->getId(),
            'dispatchID' => $dispatchId,
            'currency' => $this->sBasketData['sCurrencyName'],
            'currencyFactor' => $this->sBasketData['sCurrencyFactor'],
            'subshopID' => $mainShop->getId(),
            'remote_addr' => $ip,
            'deviceType' => $this->deviceType,
            'is_proportional_calculation' => isset($this->sBasketData['sShippingcostsTaxProportional']) ? 1 : 0,
        ];

        $orderParams = $this->eventManager->filter(
            'Shopware_Modules_Order_SaveOrder_FilterParams',
            $orderParams,
            ['subject' => $this]
        );

        try {
            $this->db->beginTransaction();
            $affectedRows = $this->db->insert('s_order', $orderParams);
            $orderID = $this->db->lastInsertId();
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Enlight_Exception(
                sprintf('Shopware Order Fatal-Error %s :%s', $_SERVER['HTTP_HOST'], $e->getMessage()),
                0,
                $e
            );
        }

        if (!$affectedRows || !$orderID) {
            throw new Enlight_Exception(
                sprintf('Shopware Order Fatal-Error %s : No rows affected or no order id created.', $_SERVER['HTTP_HOST']),
                0
            );
        }

        try {
            $paymentData = Shopware()->Modules()->Admin()
                ->sGetPaymentMeanById($this->getPaymentId(), Shopware()->Modules()->Admin()->sGetUserData());
            $paymentClass = Shopware()->Modules()->Admin()->sInitiatePaymentClass($paymentData);
            if ($paymentClass instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
                $paymentClass->createPaymentInstance(
                    $orderID,
                    $this->sUserData['additional']['user']['id'],
                    $this->getPaymentId()
                );
            }
        } catch (\Exception $e) {
            //Payment method code failure
        }

        $attributeData = $this->eventManager->filter(
            'Shopware_Modules_Order_SaveOrder_FilterAttributes',
            $this->orderAttributes,
            [
                'subject' => $this,
                'orderID' => $orderID,
                'orderParams' => $orderParams,
            ]
        );

        $this->attributePersister->persist($attributeData, 's_order_attributes', $orderID);
        $attributes = $this->attributeLoader->load('s_order_attributes', $orderID);
        unset($attributes['id'], $attributes['orderID']);

        $esdOrder = null;
        foreach ($this->sBasketData['content'] as $key => $basketRow) {
            $basketRow = $this->formatBasketRow($basketRow);

            $preparedQuery = '
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
                pack_unit,
                articleDetailID
                )
                VALUES (%d, %s, %d, %s, %f, %d, %s, %d, %s, %d, %d, %d, %f, %s, %s, %s, %d)
            ';

            $sql = sprintf($preparedQuery,
                $orderID,
                $this->db->quote((string) $orderNumber),
                $basketRow['articleID'],
                $this->db->quote((string) $basketRow['ordernumber']),
                $basketRow['priceNumeric'],
                $basketRow['quantity'],
                $this->db->quote((string) $basketRow['articlename']),
                0,
                $this->db->quote((string) $basketRow['releasedate']),
                $basketRow['modus'],
                $basketRow['esdarticle'],
                $basketRow['taxID'],
                $basketRow['tax_rate'],
                $this->db->quote((string) $basketRow['ean']),
                $this->db->quote((string) $basketRow['itemUnit']),
                $this->db->quote((string) $basketRow['packunit']),
                $basketRow['additional_details']['articleDetailsID']
            );

            $sql = $this->eventManager->filter('Shopware_Modules_Order_SaveOrder_FilterDetailsSQL', $sql, [
                'subject' => $this,
                'row' => $basketRow,
                'user' => $this->sUserData,
                'order' => ['id' => $orderID, 'number' => $orderNumber],
            ]);

            // Check for individual voucher - code
            if ($basketRow['modus'] == 2) {
                //reserve the basket voucher for the current user.
                $this->reserveVoucher(
                    $basketRow['ordernumber'],
                    $this->sUserData['additional']['user']['id'],
                    $basketRow['articleID']
                );
            }

            if ($basketRow['esdarticle']) {
                $esdOrder = true;
            }

            try {
                $this->db->executeUpdate($sql);
                $orderdetailsID = $this->db->lastInsertId();
            } catch (Exception $e) {
                throw new Enlight_Exception(sprintf('Shopware Order Fatal-Error %s :%s', $_SERVER['HTTP_HOST'],
                    $e->getMessage()), 0, $e);
            }

            $this->sBasketData['content'][$key]['orderDetailId'] = $orderdetailsID;

            // Save attributes
            $attributeData = $this->attributeLoader->load('s_order_basket_attributes', $basketRow['id']);

            $attributeData = $this->eventManager->filter(
                'Shopware_Modules_Order_SaveOrder_FilterDetailAttributes',
                $attributeData,
                [
                    'subject' => $this,
                    'basketRow' => $basketRow,
                    'orderdetailsID' => $orderdetailsID,
                ]
            );

            $this->attributePersister->persist($attributeData, 's_order_details_attributes', $orderdetailsID);
            $detailAttributes = $this->attributeLoader->load('s_order_details_attributes', $orderdetailsID);
            unset($detailAttributes['id'], $detailAttributes['detailID']);
            $this->sBasketData['content'][$key]['attributes'] = $detailAttributes;

            // Update sales and stock
            if ($basketRow['priceNumeric'] >= 0) {
                $this->refreshOrderedVariant(
                    $basketRow['ordernumber'],
                    $basketRow['quantity']
                );
            }

            // For esd-products, assign serial number if needed
            // Check if this product is esd-only (check in variants, too -> later)
            if ($basketRow['esdarticle']) {
                $basketRow = $this->handleESDOrder($basketRow, $orderID, $orderdetailsID);

                // Add assignedSerials to basketcontent
                if (!empty($basketRow['assignedSerials'])) {
                    $this->sBasketData['content'][$key]['serials'] = $basketRow['assignedSerials'];
                }
            }
        } // For every product in basket

        $this->eventManager->notify('Shopware_Modules_Order_SaveOrder_ProcessDetails', [
            'subject' => $this,
            'details' => $this->sBasketData['content'],
            'orderId' => $orderID,
        ]);

        // Save Billing and Shipping-Address to retrace in future
        $this->sSaveBillingAddress($this->sUserData['billingaddress'], $orderID);
        $this->sSaveShippingAddress($this->sUserData['shippingaddress'], $orderID);

        $this->sUserData = $this->getUserDataForMail($this->sUserData);

        $details = $this->getOrderDetailsForMail(
            $this->sBasketData['content']
        );

        $variables = [
            'sOrderDetails' => $details,
            'billingaddress' => $this->sUserData['billingaddress'],
            'shippingaddress' => $this->sUserData['shippingaddress'],
            'additional' => $this->sUserData['additional'],
            'sShippingCosts' => $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sShippingcosts) . ' ' . $this->sBasketData['sCurrencyName'],
            'sAmount' => $this->sAmountWithTax ? $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sAmountWithTax) . ' ' . $this->sBasketData['sCurrencyName'] : $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sAmount) . ' ' . $this->sBasketData['sCurrencyName'],
            'sAmountNumeric' => $this->sAmountWithTax ? $this->sAmountWithTax : $this->sAmount,
            'sAmountNet' => $this->sSYSTEM->sMODULES['sArticles']->sFormatPrice($this->sBasketData['AmountNetNumeric']) . ' ' . $this->sBasketData['sCurrencyName'],
            'sAmountNetNumeric' => $this->sBasketData['AmountNetNumeric'],
            'sTaxRates' => $this->sBasketData['sTaxRates'],
            'ordernumber' => $orderNumber,
            'sOrderDay' => date('d.m.Y'),
            'sOrderTime' => date('H:i'),
            'sComment' => $this->sComment,
            'attributes' => $attributes,
            'sEsd' => $esdOrder,
        ];

        if ($dispatchId) {
            $variables['sDispatch'] = $this->sSYSTEM->sMODULES['sAdmin']->sGetPremiumDispatch($dispatchId);
        }
        if ($this->bookingId) {
            $variables['sBookingID'] = $this->bookingId;
        }

        // Completed - Garbage basket / temporary - order
        $this->sDeleteTemporaryOrder();

        $this->db->executeUpdate(
            'DELETE FROM s_order_basket WHERE sessionID=?',
            [$this->getSession()->offsetGet('sessionId')]
        );

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

        $this->eventManager->notify('Shopware_Modules_Order_SaveOrder_OrderCreated', [
            'subject' => $this,
            'details' => $this->sBasketData['content'],
            'orderId' => $orderID,
            'orderNumber' => $orderNumber,
        ]);

        return $orderNumber;
    }

    /**
     * send order confirmation mail
     *
     * @param array $variables
     */
    public function sendMail($variables)
    {
        $variables = $this->eventManager->filter(
            'Shopware_Modules_Order_SendMail_FilterVariables',
            $variables,
            ['subject' => $this]
        );

        $shopContext = $this->contextService->getShopContext();

        $context = [
            'sOrderDetails' => $variables['sOrderDetails'],

            'billingaddress' => $variables['billingaddress'],
            'shippingaddress' => $variables['shippingaddress'],
            'additional' => $variables['additional'],

            'sTaxRates' => $variables['sTaxRates'],
            'sShippingCosts' => $variables['sShippingCosts'],
            'sAmount' => $variables['sAmount'],
            'sAmountNumeric' => $variables['sAmountNumeric'],
            'sAmountNet' => $variables['sAmountNet'],
            'sAmountNetNumeric' => $variables['sAmountNetNumeric'],

            'sOrderNumber' => $variables['ordernumber'],
            'sOrderDay' => $variables['sOrderDay'],
            'sOrderTime' => $variables['sOrderTime'],
            'sComment' => $variables['sComment'],

            'attributes' => $variables['attributes'],
            'sCurrency' => $this->sBasketData['sCurrencyName'],

            'sLanguage' => $shopContext->getShop()->getId(),

            'sSubShop' => $shopContext->getShop()->getId(),

            'sEsd' => $variables['sEsd'],
            'sNet' => $this->sNet,
        ];

        // Support for individual payment means with custom-tables
        if ($variables['additional']['payment']['table']) {
            $paymentTable = $this->db->fetchRow("
                  SELECT * FROM {$variables['additional']['payment']['table']}
                  WHERE userID=?",
                [$variables['additional']['user']['id']]
            );
            $context['sPaymentTable'] = $paymentTable ?: [];
        } else {
            $context['sPaymentTable'] = [];
        }

        if ($variables['sDispatch']) {
            $context['sDispatch'] = $variables['sDispatch'];
        }

        if ($variables['sBookingID']) {
            $context['sBookingID'] = $variables['sBookingID'];
        }

        $context = $this->eventManager->filter(
            'Shopware_Modules_Order_SendMail_FilterContext',
            $context,
            ['subject' => $this]
        );

        $mail = null;
        if ($event = $this->eventManager->notifyUntil(
            'Shopware_Modules_Order_SendMail_Create',
            [
                'subject' => $this,
                'context' => $context,
                'variables' => $variables,
            ]
        )) {
            $mail = $event->getReturn();
        }

        if (!($mail instanceof \Zend_Mail)) {
            $mail = Shopware()->TemplateMail()->createMail('sORDER', $context);
        }

        $mail->addTo($this->sUserData['additional']['user']['email']);

        if (!$this->config->get('sNO_ORDER_MAIL')) {
            $mail->addBcc($this->config->get('sMAIL'));
        }

        $mail = $this->eventManager->filter('Shopware_Modules_Order_SendMail_Filter', $mail, [
            'subject' => $this,
            'context' => $context,
            'variables' => $variables,
        ]);

        if (!($mail instanceof \Zend_Mail)) {
            return;
        }

        $this->eventManager->notify(
            'Shopware_Modules_Order_SendMail_BeforeSend',
            [
                'subject' => $this,
                'mail' => $mail,
                'context' => $context,
                'variables' => $variables,
            ]
        );

        $shouldSendMail = !(bool) $this->eventManager->notifyUntil(
            'Shopware_Modules_Order_SendMail_Send',
            [
                'subject' => $this,
                'mail' => $mail,
                'context' => $context,
                'variables' => $variables,
            ]
        );

        if ($shouldSendMail && $this->config->get('sendOrderMail')) {
            $mail->send();
        }
    }

    /**
     * Save order billing address
     *
     * @param array $address
     * @param int   $id
     *
     * @throws Exception
     *
     * @return int
     */
    public function sSaveBillingAddress($address, $id)
    {
        /** @var Customer $customer */
        $customer = $this->modelManager->find(Customer::class, $address['userID']);

        $sql = '
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
        ';
        $sql = $this->eventManager->filter(
            'Shopware_Modules_Order_SaveBilling_FilterSQL',
            $sql,
            ['subject' => $this, 'address' => $address, 'id' => $id]
        );
        $array = [
            ':userID' => $address['userID'],
            ':orderID' => $id,
            ':customernumber' => $customer->getNumber(),
            ':company' => (string) $address['company'],
            ':department' => (string) $address['department'],
            ':salutation' => (string) $address['salutation'],
            ':firstname' => (string) $address['firstname'],
            ':lastname' => (string) $address['lastname'],
            ':street' => (string) $address['street'],
            ':zipcode' => (string) $address['zipcode'],
            ':city' => (string) $address['city'],
            ':phone' => (string) $address['phone'],
            ':countryID' => $address['countryID'],
            ':stateID' => $address['stateID'],
            ':ustid' => $address['ustid'],
            ':additional_address_line1' => $address['additional_address_line1'],
            ':additional_address_line2' => $address['additional_address_line2'],
            ':title' => $address['title'],
        ];
        $array = $this->eventManager->filter(
            'Shopware_Modules_Order_SaveBilling_FilterArray',
            $array,
            ['subject' => $this, 'address' => $address, 'id' => $id]
        );
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
     * Save order shipping address
     *
     * @param array $address
     * @param int   $id
     *
     * @throws Exception
     *
     * @return int
     */
    public function sSaveShippingAddress($address, $id)
    {
        $sql = '
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
            phone,
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
            :phone,
            :countryID,
            :stateID,
            :additional_address_line1,
            :additional_address_line2,
            :title
            )
        ';
        $sql = $this->eventManager->filter(
            'Shopware_Modules_Order_SaveShipping_FilterSQL',
            $sql,
            ['subject' => $this, 'address' => $address, 'id' => $id]
        );
        $array = [
            ':userID' => $address['userID'],
            ':orderID' => $id,
            ':company' => (string) $address['company'],
            ':department' => (string) $address['department'],
            ':salutation' => (string) $address['salutation'],
            ':firstname' => (string) $address['firstname'],
            ':lastname' => (string) $address['lastname'],
            ':street' => (string) $address['street'],
            ':zipcode' => (string) $address['zipcode'],
            ':city' => (string) $address['city'],
            ':phone' => (string) $address['phone'],
            ':countryID' => $address['countryID'],
            ':stateID' => $address['stateID'],
            ':additional_address_line1' => (string) $address['additional_address_line1'],
            ':additional_address_line2' => (string) $address['additional_address_line2'],
            ':title' => (string) $address['title'],
        ];
        $array = $this->eventManager->filter(
            'Shopware_Modules_Order_SaveShipping_FilterArray',
            $array,
            ['subject' => $this, 'address' => $address, 'id' => $id]
        );
        $result = $this->db->executeUpdate($sql, $array);

        $shippingId = $this->db->lastInsertId();

        $shippingAddressId = null;

        if ($this->session !== null) {
            $shippingAddressId = $this->session->get('checkoutShippingAddressId');
        }

        if ($shippingAddressId === null) {
            /** @var Customer $customer */
            $customer = $this->modelManager->getRepository(\Shopware\Models\Customer\Customer::class)
                ->find($address['userID']);
            $shippingAddressId = $customer->getDefaultShippingAddress()->getId();
        }

        $attributes = $this->attributeLoader->load('s_user_addresses_attributes', $shippingAddressId);

        $this->attributePersister->persist($attributes, 's_order_shippingaddress_attributes', $shippingId);

        return $result;
    }

    /**
     * Check if this order could be referred to a previous recommendation
     */
    public function sTellFriend()
    {
        $checkMail = $this->sUserData['additional']['user']['email'];

        $tmpSQL = '
        SELECT * FROM s_emarketing_tellafriend WHERE confirmed=0 AND recipient=?
        ';
        $checkIfUserFound = $this->db->fetchRow($tmpSQL, [$checkMail]);
        if ($checkIfUserFound) {
            $this->db->executeUpdate('
            UPDATE s_emarketing_tellafriend SET confirmed=1 WHERE recipient=?
            ', [$checkMail]);

            $advertiser = $this->db->fetchRow('
            SELECT email, firstname, lastname FROM s_user
            WHERE s_user.id=?
            ', [$checkIfUserFound['sender']]);

            if (!$advertiser) {
                return;
            }

            $context = [
                'customer' => $advertiser['firstname'] . ' ' . $advertiser['lastname'],
                'user' => $this->sUserData['billingaddress']['firstname'] . ' ' . $this->sUserData['billingaddress']['lastname'],
                'voucherValue' => $this->config->get('sVOUCHERTELLFRIENDVALUE'),
                'voucherCode' => $this->config->get('sVOUCHERTELLFRIENDCODE'),
            ];

            $mail = Shopware()->TemplateMail()->createMail('sVOUCHER', $context);
            $mail->addTo($advertiser['email']);
            $mail->send();
        } // - if user found
    }

    // Tell-a-friend

    /**
     * Send status mail
     *
     * @return Enlight_Components_Mail
     */
    public function sendStatusMail(Enlight_Components_Mail $mail)
    {
        $this->eventManager->notify('Shopware_Controllers_Backend_OrderState_Send_BeforeSend', [
            'subject' => Shopware()->Front(),
            'mail' => $mail,
        ]);

        if (!empty($this->config->OrderStateMailAck)) {
            $mail->addBcc($this->config->OrderStateMailAck);
        }

        /** @var Enlight_Components_Mail $return */
        $return = $mail->send();

        return $return;
    }

    /**
     * Create status mail
     *
     * @param int    $orderId
     * @param int    $statusId
     * @param string $templateName
     *
     * @return Enlight_Components_Mail|void
     */
    public function createStatusMail($orderId, $statusId, $templateName = null)
    {
        $statusId = (int) $statusId;
        $orderId = (int) $orderId;
        $dispatch = null;

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
                SELECT id, name, description FROM s_premium_dispatch
                WHERE id=?
            ', [$order['dispatchID']]);
        }

        $user = $this->getCustomerInformationByOrderId($orderId);

        if (empty($order) || empty($orderDetails) || empty($user)) {
            return;
        }

        $repository = $this->modelManager->getRepository(Shop::class);
        $shopId = is_numeric($order['language']) ? $order['language'] : $order['subshopID'];
        // The (sub-)shop might be inactive by now, so that's why we use `getById` instead of `getActiveById`
        $shop = $repository->getById($shopId);
        Shopware()->Container()->get('shopware.components.shop_registration_service')->registerShop($shop);

        $dispatch = Shopware()->Modules()->Admin()->sGetDispatchTranslation($dispatch);
        $payment = Shopware()->Modules()->Admin()->sGetPaymentTranslation(['id' => $order['paymentID']]);

        $order['status_description'] = Shopware()->Snippets()->getNamespace('backend/static/order_status')->get(
            $order['status_name'],
            $order['status_description']
        );
        $order['cleared_description'] = Shopware()->Snippets()->getNamespace('backend/static/payment_status')->get(
            $order['cleared_name'],
            $order['cleared_description']
        );

        if (!empty($payment['description'])) {
            $order['payment_description'] = $payment['description'];
        }

        /* @var \Shopware\Models\Mail\Mail $mailModel */
        $mailModel = $this->modelManager->getRepository(Mail::class)->findOneBy(
            ['name' => $templateName]
        );

        if (!$mailModel) {
            return;
        }

        $context = [
            'sOrder' => $order,
            'sOrderDetails' => $orderDetails,
            'sUser' => $user,
        ];

        if (!empty($dispatch)) {
            $context['sDispatch'] = $dispatch;
        }

        $result = $this->eventManager->notify('Shopware_Controllers_Backend_OrderState_Notify', [
            'subject' => Shopware()->Front(),
            'id' => $orderId,
            'status' => $statusId,
            'mailname' => $templateName,
        ]);

        if (!empty($result)) {
            $context['EventResult'] = $result->getValues();
        }

        $mail = Shopware()->TemplateMail()->createMail($templateName, $context, $shop);

        $return = [
            'content' => $mail->getPlainBodyText(),
            'subject' => $mail->getPlainSubject(),
            'email' => trim($user['email']),
            'frommail' => $mail->getFrom(),
            'fromname' => $mail->getFromName(),
        ];

        $return = $this->eventManager->filter('Shopware_Controllers_Backend_OrderState_Filter', $return, [
            'subject' => Shopware()->Front(),
            'id' => $orderId,
            'status' => $statusId,
            'mailname' => $templateName,
            'mail' => $mail,
            'engine' => Shopware()->Template(),
        ]);

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
     * @param int         $orderId
     * @param int         $paymentStatusId
     * @param bool        $sendStatusMail
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
            [
                'paymentStatus' => $paymentStatusId,
                'orderId' => $orderId,
            ]
        );

        $sql = '
           INSERT INTO s_order_history (
              orderID, userID, previous_order_status_id, order_status_id,
              previous_payment_status_id, payment_status_id, comment, change_date )
            SELECT id, NULL, status, status, :previousStatus, :currentStatus, :comment, NOW() FROM s_order WHERE id = :orderId
        ';

        $this->db->executeUpdate($sql, [
            ':previousStatus' => $previousStatusId,
            ':currentStatus' => $paymentStatusId,
            ':comment' => $comment,
            ':orderId' => $orderId,
        ]);

        if ($sendStatusMail) {
            $mail = $this->createStatusMail($orderId, $paymentStatusId);
            if ($mail) {
                $this->sendStatusMail($mail);
            }
        }
    }

    /**
     * Set payment status by order id
     *
     * @param int         $orderId
     * @param int         $orderStatusId
     * @param bool        $sendStatusMail
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
            [':status' => $orderStatusId, ':orderId' => $orderId]
        );

        $sql = '
           INSERT INTO s_order_history (
              orderID, userID, previous_order_status_id, order_status_id,
              previous_payment_status_id, payment_status_id, comment, change_date )
            SELECT id, NULL, :previousStatus, :currentStatus, cleared, cleared, :comment, NOW() FROM s_order WHERE id = :orderId
        ';

        $this->db->executeUpdate($sql, [
            ':previousStatus' => $previousStatusId,
            ':currentStatus' => $orderStatusId,
            ':comment' => $comment,
            ':orderId' => $orderId,
        ]);

        if ($sendStatusMail) {
            $mail = $this->createStatusMail($orderId, $orderStatusId);
            if ($mail) {
                $this->sendStatusMail($mail);
            }
        }
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
     *
     * @return array|false
     */
    public function getOrderById($orderId)
    {
        $sql = <<<'EOT'
SELECT
    `o`.`id` AS `orderID`,
    `o`.`ordernumber`,
    `o`.`ordernumber` AS `order_number`,
    `o`.`userID`,
    `o`.`userID` AS `customerID`,
    `o`.`invoice_amount`,
    `o`.`invoice_amount_net`,
    `o`.`invoice_shipping`,
    `o`.`invoice_shipping_net`,
    `o`.`ordertime` AS `ordertime`,
    `o`.`status`,
    `o`.`status` AS `statusID`,
    `o`.`cleared` AS `cleared`,
    `o`.`cleared` AS `clearedID`,
    `o`.`paymentID` AS `paymentID`,
    `o`.`transactionID` AS `transactionID`,
    `o`.`comment`,
    `o`.`customercomment`,
    `o`.`net`,
    `o`.`net` AS `netto`,
    `o`.`partnerID`,
    `o`.`temporaryID`,
    `o`.`referer`,
    o.cleareddate,
    o.cleareddate AS cleared_date,
    o.trackingcode,
    o.language,
    o.currency,
    o.currencyFactor,
    o.subshopID,
    o.dispatchID,
    cu.id AS currencyID,
    `c`.`name` AS `cleared_name`,
    `c`.`description` AS `cleared_description`,
    `s`.`name` AS `status_name`,
    `s`.`description` AS `status_description`,
    `p`.`description` AS `payment_description`,
    `d`.`name` AS `dispatch_description`,
    `cu`.`name` AS `currency_description`
FROM
    `s_order` AS `o`
LEFT JOIN `s_core_states` AS `s`
    ON  (`o`.`status` = `s`.`id`)
LEFT JOIN `s_core_states` AS `c`
    ON  (`o`.`cleared` = `c`.`id`)
LEFT JOIN `s_core_paymentmeans` AS `p`
    ON  (`o`.`paymentID` = `p`.`id`)
LEFT JOIN `s_premium_dispatch` AS `d`
    ON  (`o`.`dispatchID` = `d`.`id`)
LEFT JOIN `s_core_currencies` AS `cu`
    ON  (`o`.`currency` = `cu`.`currency`)
WHERE
    `o`.`id` = :orderId
EOT;

        return $this->db->fetchRow($sql, ['orderId' => $orderId]);
    }

    /**
     * Replacement for: Shopware()->Api()->Export()->sOrderDetails(array('orderID' => $orderId));
     *
     * Returns order details for a given orderId
     *
     * @param int $orderId
     *
     * @return array
     */
    public function getOrderDetailsByOrderId($orderId)
    {
        $sql = <<<'EOT'
SELECT
    `d`.`id` AS `orderdetailsID`,
    `d`.`orderID` AS `orderID`,
    `d`.`ordernumber`,
    `d`.`articleID`,
    `d`.`articleordernumber`,
    `d`.`price` AS `price`,
    `d`.`quantity` AS `quantity`,
    `d`.`price`*`d`.`quantity` AS `invoice`,
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
    `d`.`esdarticle` AS `esd`
FROM
    `s_order_details` AS `d`
LEFT JOIN
    `s_core_tax` AS `t`
ON
    `t`.`id` = `d`.`taxID`
WHERE
    `d`.`orderID` = :orderId
ORDER BY
    `orderdetailsID` ASC
EOT;

        return $this->db->fetchAll($sql, ['orderId' => $orderId]);
    }

    /**
     * Replacement for: Shopware()->Api()->Export()->sOrderCustomers(array('orderID' => $orderId));
     *
     * @param int $orderId
     *
     * @return array|false
     */
    public function getCustomerInformationByOrderId($orderId)
    {
        $sql = <<<'EOT'
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
    `b`.`orderID` AS `orderID`,
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
    `s_order_billingaddress` AS `b`
LEFT JOIN `s_order_shippingaddress` AS `s`
    ON `s`.`orderID` = `b`.`orderID`
LEFT JOIN `s_user` AS `u`
    ON `b`.`userID` = `u`.`id`
LEFT JOIN `s_user_addresses` AS `ub`
    ON `u`.`default_billing_address_id`=`ub`.`id`
    AND `u`.`id`=`ub`.`user_id`
LEFT JOIN `s_core_countries` AS `bc`
    ON `bc`.`id` = `b`.`countryID`
LEFT JOIN `s_core_countries` AS `sc`
    ON `sc`.`id` = `s`.`countryID`
LEFT JOIN `s_core_customergroups` AS `g`
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

        return $this->db->fetchRow($sql, ['orderId' => $orderId]);
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
     * @return int
     */
    private function getPaymentId()
    {
        if (!empty($this->sUserData['additional']['payment']['id'])) {
            return $this->sUserData['additional']['payment']['id'];
        }

        return $this->sUserData['additional']['user']['paymentID'];
    }

    /**
     * Helper function which returns the esd definition of the passed variant
     * order number.
     * Used for the sManageEsd function to check if the current order product variant
     * is an esd variant.
     *
     * @param string $orderNumber
     *
     * @return array|false
     */
    private function getVariantEsd($orderNumber)
    {
        return $this->db->fetchRow(
            'SELECT s_articles_esd.id AS id, serials
            FROM  s_articles_esd, s_articles_details
            WHERE s_articles_esd.articleID = s_articles_details.articleID
            AND   articledetailsID = s_articles_details.id
            AND   s_articles_details.ordernumber= :orderNumber',
            [':orderNumber' => $orderNumber]
        );
    }

    /**
     * Helper function which returns all available esd serials for the passed esd id.
     *
     * @param int $esdId
     *
     * @return array
     */
    private function getAvailableSerialsOfEsd($esdId)
    {
        return $this->db->fetchAll(
            'SELECT s_articles_esd_serials.id AS id, s_articles_esd_serials.serialnumber AS serialnumber
            FROM s_articles_esd_serials
            LEFT JOIN s_order_esd
              ON (s_articles_esd_serials.id = s_order_esd.serialID)
            WHERE s_order_esd.serialID IS NULL
            AND s_articles_esd_serials.esdID= :esdId',
            ['esdId' => $esdId]
        );
    }

    /**
     * Checks if the passed transaction id is already set as transaction id of an
     * existing order.
     *
     * @param string $transactionId
     *
     * @return bool
     */
    private function isTransactionExist($transactionId)
    {
        if (strlen($transactionId) <= 3) {
            return false;
        }

        $insertOrder = $this->db->fetchRow(
            'SELECT id FROM s_order WHERE transactionID = ? AND status != -1',
            [$transactionId]
        );

        return !empty($insertOrder['id']);
    }

    /**
     * Checks if the current customer should see net prices.
     *
     * @param int $taxId
     * @param int $customerGroupId
     *
     * @return bool
     */
    private function isTaxFree($taxId, $customerGroupId)
    {
        return ($this->config->get('sARTICLESOUTPUTNETTO') && !$taxId)
            || (!$taxId && $customerGroupId);
    }

    /**
     * Checks if the current order was send from a partner and returns
     * the partner code.
     *
     * @param int $userAffiliate Affiliate flag of the user data
     *
     * @return string|null
     */
    private function getPartnerCode($userAffiliate)
    {
        $isPartner = $this->getSession()->offsetGet('sPartner');
        if (!empty($isPartner)) {
            return $this->getSession()->offsetGet('sPartner');
        }

        if (empty($userAffiliate)) {
            return null;
        }

        // Get Partner code
        return $this->db->fetchOne(
            'SELECT idcode FROM s_emarketing_partner WHERE id = ?',
            [$userAffiliate]
        );
    }

    /**
     * Helper function which reserves individual voucher codes for the
     * passed user.
     *
     * @param string $orderCode
     * @param int    $customerId
     * @param int    $voucherCodeId
     */
    private function reserveVoucher($orderCode, $customerId, $voucherCodeId)
    {
        $getVoucher = $this->db->fetchRow(
            'SELECT modus,id FROM s_emarketing_vouchers WHERE ordercode = ?',
            [$orderCode]
        );

        if ($getVoucher['modus'] == 1) {
            $this->db->executeUpdate(
                'UPDATE s_emarketing_voucher_codes SET cashed = 1, userID= ? WHERE id = ?',
                [$customerId, $voucherCodeId]
            );
        }
    }

    /**
     * This function updates the data for an ordered variant.
     * The variant sales value will be increased by the passed quantity
     * and the variant stock value decreased by the passed quantity.
     *
     * @param string $orderNumber
     * @param int    $quantity
     */
    private function refreshOrderedVariant($orderNumber, $quantity)
    {
        $this->db->executeUpdate('
            UPDATE s_articles_details
            SET sales = sales + :quantity,
                instock = instock - :quantity
            WHERE ordernumber = :number',
            [':quantity' => $quantity, ':number' => $orderNumber]
        );

        $this->eventManager->notify(
            'product_stock_was_changed',
            ['number' => $orderNumber, 'quantity' => $quantity]
        );
    }

    /**
     * Small helper function which iterates all basket rows
     * and formats the product name and order number.
     * This function is used for the order status mail.
     *
     * @return array
     */
    private function getOrderDetailsForMail(array $basketRows)
    {
        $details = [];
        foreach ($basketRows as $content) {
            $content['articlename'] = trim(html_entity_decode($content['articlename']));
            $content['articlename'] = str_replace(['<br />', '<br>'], "\n", $content['articlename']);
            $content['articlename'] = str_replace('&euro;', '€', $content['articlename']);
            $content['articlename'] = trim($content['articlename']);

            while (strpos($content['articlename'], "\n\n") !== false) {
                $content['articlename'] = str_replace("\n\n", "\n", $content['articlename']);
            }

            $content['ordernumber'] = trim(html_entity_decode($content['ordernumber']));

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
     * @param int $orderId
     *
     * @return array
     */
    private function getOrderDetailsForStatusMail($orderId)
    {
        $orderDetails = $this->getOrderDetailsByOrderId($orderId);

        // add attributes to orderDetails
        foreach ($orderDetails as &$orderDetail) {
            $attributes = $this->attributeLoader->load('s_order_details_attributes', $orderDetail['orderdetailsID']);
            unset($attributes['id'], $attributes['detailID']);

            $orderDetail['attributes'] = $attributes;
        }

        return $orderDetails;
    }

    /**
     * Helper function which gets the formatted order data for the passed order id.
     * This function is used if the order status changed and the status mail will be
     * send.
     *
     * @param int $orderId
     */
    private function getOrderForStatusMail($orderId)
    {
        $order = $this->getOrderById($orderId);
        $attributes = $this->attributeLoader->load('s_order_attributes', $orderId);
        unset($attributes['id'], $attributes['orderID']);

        $order['attributes'] = $attributes;

        return $order;
    }

    /**
     * Helper function which converts all HTML entities, in the passed user data array,
     * to their applicable characters.
     *
     * @return array
     */
    private function getUserDataForMail(array $userData)
    {
        $userData['billingaddress'] = $this->htmlEntityDecodeRecursive($userData['billingaddress']);
        $userData['shippingaddress'] = $this->htmlEntityDecodeRecursive($userData['shippingaddress']);
        $userData['country'] = $this->htmlEntityDecodeRecursive($userData['country']);

        $userData['additional']['payment']['description'] = html_entity_decode(
            $userData['additional']['payment']['description']
        );

        return $userData;
    }

    /**
     * Helper function to recursively apply html_entity_decode() to the given data.
     *
     * @param array|string $data
     *
     * @return array|string
     */
    private function htmlEntityDecodeRecursive($data)
    {
        $func = function ($item) use (&$func) {
            return is_array($item) ? array_map($func, $item) : call_user_func('html_entity_decode', $item);
        };

        return array_map($func, $data);
    }

    /**
     * Helper function for the sSaveOrder which formats a single
     * basket row.
     * This function sets the default for different properties, which
     * might not be set or invalid.
     *
     * @param array $basketRow
     *
     * @return array
     */
    private function formatBasketRow($basketRow)
    {
        $basketRow['articlename'] = str_replace('<br />', "\n", $basketRow['articlename']);
        $basketRow['articlename'] = html_entity_decode($basketRow['articlename']);
        $basketRow['articlename'] = strip_tags($basketRow['articlename']);
        $basketRow['articlename'] = Shopware()->Modules()->Articles()->sOptimizeText(
            $basketRow['articlename']
        );

        if (!$basketRow['price']) {
            $basketRow['price'] = '0,00';
        }
        if (!$basketRow['esdarticle']) {
            $basketRow['esdarticle'] = '0';
        }
        if (!$basketRow['modus']) {
            $basketRow['modus'] = '0';
        }
        if (!$basketRow['taxID']) {
            $basketRow['taxID'] = '0';
        }
        if ($this->sNet == true) {
            $basketRow['taxID'] = '0';
        }
        if (!$basketRow['ean']) {
            $basketRow['ean'] = '';
        }
        if (!$basketRow['releasedate']) {
            $basketRow['releasedate'] = '0000-00-00';
        }

        return $basketRow;
    }

    /**
     * Helper function which returns the current payment status
     * of the passed order.
     *
     * @param int $orderId
     *
     * @return string
     */
    private function getOrderPaymentStatus($orderId)
    {
        return $this->db->fetchOne(
            'SELECT `cleared` FROM `s_order` WHERE `id`=:orderId;',
            [':orderId' => $orderId]
        );
    }

    /**
     * Helper function which returns the current order status of the passed order
     * id.
     *
     * @param int $orderId
     *
     * @return string
     */
    private function getOrderStatus($orderId)
    {
        return $this->db->fetchOne(
            'SELECT status FROM s_order WHERE id= :orderId;',
            [':orderId' => $orderId]
        );
    }

    /**
     * @param string $orderNumber
     * @param string $email
     */
    private function logOrderMailException(\Exception $e, $orderNumber, $email)
    {
        $message = sprintf(
            'Could not send order mail for ordernumber %s to address %s',
            $orderNumber,
            $email
        );

        $context = ['exception' => $e];
        Shopware()->Container()->get('corelogger')->error($message, $context);
    }
}
