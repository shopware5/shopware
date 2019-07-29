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

use Shopware\Components\Cart\Struct\Price;

/**
 * Order model for document generation
 */
class Shopware_Models_Document_Order extends Enlight_Class implements Enlight_Hook
{
    /**
     * Id of the order (s_order.id)
     *
     * @var int
     */
    protected $_id;

    /**
     * Metadata of the order
     *
     * @var ArrayObject
     */
    protected $_order;

    /**
     * Metadata of the order positions
     *
     * @var ArrayObject
     */
    protected $_positions;

    /**
     * Id of the user (s_user.id)
     *
     * @var int
     */
    protected $_userID;

    /**
     * Metadata of the user (email,customergroup etc. s_user.*)
     *
     * @var ArrayObject
     */
    protected $_user;

    /**
     * Billingdata for this order / user (s_order_billingaddress)
     *
     * @var ArrayObject|null
     */
    protected $_billing;

    /**
     * Shippingdata for this order / user (s_order_shippingaddress)
     *
     * @var ArrayObject|null
     */
    protected $_shipping;

    /**
     * Payment information for this order (s_core_paymentmeans)
     *
     * @var ArrayObject
     */
    protected $_payment;

    /**
     * Payment instances information for this order (s_core_payment_instance)
     *
     * @var ArrayObject
     */
    protected $_paymentInstances;

    /**
     * Information about the dispatch for this order
     *
     * @var ArrayObject
     */
    protected $_dispatch;

    /**
     * Calculate complete without tax
     *
     * @var bool
     */
    protected $_net;

    /**
     * Hide Gross amount
     *
     * @var bool
     */
    protected $_summaryNet;

    /**
     * Complete net amount
     *
     * @var float
     */
    protected $_amountNetto;

    /**
     * Complete gross amount
     *
     * @var float
     */
    protected $_amount;

    /**
     * Array with tax rates
     *
     * @var array
     */
    protected $_tax;

    /**
     * Currency information (s_core_currencies)
     *
     * @var ArrayObject
     */
    protected $_currency;

    /**
     * Shipping costs
     *
     * @var float
     */
    protected $_shippingCosts;

    /**
     * Add shipping costs as order position
     *
     * @var bool
     */
    protected $_shippingCostsAsPosition;

    /**
     * @var float
     */
    protected $_discount;

    /**
     * Document configuration
     *
     * @var array
     */
    protected $_config;

    /**
     * @var \Shopware\Models\Tax\Repository
     */
    protected $_taxRepository;

    /**
     * @param int   $id
     * @param array $config
     */
    public function __construct($id, $config = [])
    {
        // Test-data for preview mode
        if ($config['_preview'] == true && $config['_previewSample'] == true) {
            $array = $this->getDemoData();

            $array['_order']->language = 1;

            foreach ($array as $key => $element) {
                $this->$key = $element;
            }

            return;
        }

        $this->_id = $id;

        $this->_config = $config;

        $this->_summaryNet = isset($config['summaryNet']) ? $config['summaryNet'] : false;

        $this->_shippingCostsAsPosition = isset($config['shippingCostsAsPosition']) ? $config['shippingCostsAsPosition'] : false;

        $this->getOrder();
        $this->getPositions();

        $this->getUser();
        $this->getBilling();
        $this->getShipping();
        $this->getDispatch();
        $this->getPayment();
        $this->getPaymentInstances();

        $this->processPositions();
        $this->processOrder();
    }

    /**
     * Convert this object into an array
     *
     * @return array
     */
    public function __toArray()
    {
        $array = get_object_vars($this);

        $array['_order'] = $array['_order']->getArrayCopy();
        $array['_positions'] = $array['_positions']->getArrayCopy();

        if (!empty($array['_user'])) {
            $array['_user'] = $array['_user']->getArrayCopy();
        }

        $array['_billing'] = $array['_billing']->getArrayCopy();
        $array['_shipping'] = $array['_shipping']->getArrayCopy();
        $array['_payment'] = $array['_payment']->getArrayCopy();
        $array['_paymentInstances'] = $array['_paymentInstances']->getArrayCopy();
        $array['_dispatch'] = $array['_dispatch']->getArrayCopy();
        $array['_currency'] = $array['_currency']->getArrayCopy();

        return $array;
    }

    /**
     * Magic getter
     *
     * @param string $var_name
     *
     * @throws Enlight_Exception
     */
    public function __get($var_name)
    {
        $var_name = '_' . $var_name;
        if (property_exists($this, $var_name)) {
            return $this->$var_name;
        }
        throw new Enlight_Exception(sprintf('Property %s does not exist', $var_name));
    }

    /**
     * Get order database entries
     *
     * @throws Enlight_Exception
     */
    public function getOrder()
    {
        $this->_order = new ArrayObject(Shopware()->Db()->fetchRow('
            SELECT s_order.*,s_core_currencies.factor,s_core_currencies.id AS currencyID,s_core_currencies.templatechar,s_core_currencies.name AS currencyName
            FROM s_order
             LEFT JOIN s_core_currencies ON s_core_currencies.currency = s_order.currency
             WHERE s_order.id = ?
            ', [$this->_id]), ArrayObject::ARRAY_AS_PROPS);

        if (empty($this->_order['id'])) {
            throw new Enlight_Exception(sprintf('Order with id %d not found!', $this->_id));
        }

        // Load order attributes
        $this->_order['attributes'] = Shopware()->Db()->fetchRow('
        SELECT * FROM s_order_attributes WHERE orderID = ?
        ', [$this->_order['id']]);

        $this->_userID = $this->_order['userID'];
        if (!empty($this->_order['net'])) {
            $this->_net = true;
        } else {
            $this->_net = false;
        }
        $this->_currency = new ArrayObject(['currency' => $this->_order['currency'], 'name' => $this->_order['currencyName'], 'factor' => $this->_order['factor'], 'char' => $this->_order['templatechar']], ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Get all information from a certain order (model)
     */
    public function processOrder()
    {
        $snippetManager = Shopware()->Snippets();
        $modelManager = Shopware()->Models();
        $orderLocale = null;

        if ($modelManager !== null) {
            /** @var \Shopware\Models\Shop\Locale $orderLocale */
            $orderLocale = $modelManager->find(
                \Shopware\Models\Shop\Locale::class,
                $this->_order['language']
            );
        }

        if ($orderLocale !== null) {
            $snippetManager->setLocale($orderLocale);
        }

        $namespace = $snippetManager->getNamespace('documents/index');
        $shippingName = $namespace->get('ShippingCosts', 'Shipping costs', true);

        if ($this->_order['invoice_shipping_tax_rate'] === null) {
            if ($this->_order['invoice_shipping_net'] != 0) {
                // p.e. = 24.99 / 20.83 * 100 - 100 = 19.971195391 (approx. 20% VAT)
                $approximateTaxRate = $this->_order['invoice_shipping'] / $this->_order['invoice_shipping_net'] * 100 - 100;
            } else {
                $approximateTaxRate = 0;
            }

            $taxShipping = $this->getTaxRateByApproximateTaxRate(
                $approximateTaxRate,
                $this->_shipping['country']['areaID'],
                $this->_shipping['countryID'],
                $this->_shipping['stateID'],
                $this->_user['customergroupID']
            );

            $taxShipping = (float) $taxShipping;
        } else {
            if ($this->_order['invoice_shipping_net'] != 0) {
                $taxShipping = $this->_order['invoice_shipping_tax_rate'];
            } else {
                $taxShipping = 0;
            }
        }

        $this->_shippingCosts = $this->_order['invoice_shipping'];

        if ($this->_shippingCostsAsPosition == true && !empty($this->_shippingCosts)) {
            $taxes = [];

            if ($this->_order['taxfree']) {
                $this->_amountNetto = $this->_amountNetto + $this->_order['invoice_shipping'];
            } else {
                if ($this->_order['is_proportional_calculation']) {
                    $taxes = Shopware()->Container()->get('shopware.cart.proportional_tax_calculator')->calculate($this->_order['invoice_shipping'], $this->getPricePositions(), false);

                    $taxNet = 0;

                    /** @var Price $tax */
                    foreach ($taxes as $tax) {
                        $taxNet += $tax->getNetPrice();
                        $this->_tax[number_format($tax->getTaxRate(), 2)] += $tax->getTax();
                    }

                    $this->_amountNetto += $taxNet;
                } else {
                    $this->_amountNetto += ($this->_order['invoice_shipping'] / (100 + $taxShipping) * 100);
                    if (!empty($taxShipping) && !empty($this->_order['invoice_shipping'])) {
                        $this->_tax[number_format($taxShipping, 2)] += ($this->_order['invoice_shipping'] / (100 + $taxShipping)) * $taxShipping;
                    }
                }
            }

            $this->_amount = $this->_amount + $this->_order['invoice_shipping'];

            if ($this->_order['is_proportional_calculation']) {
                /** @var Price $tax */
                foreach ($taxes as $tax) {
                    $shipping = [];
                    $shipping['quantity'] = 1;
                    $shipping['netto'] = $tax->getNetPrice();
                    $shipping['tax'] = $tax->getTaxRate();
                    $shipping['price'] = $tax->getPrice();
                    $shipping['amount'] = $tax->getPrice();
                    $shipping['modus'] = 1;
                    $shipping['amount_netto'] = $tax->getNetPrice();
                    $shipping['articleordernumber'] = '';
                    $shipping['name'] = $shippingName . ' ' . (count($taxes) > 1 ? '(' . $tax->getTaxRate() . '%)' : '');

                    $this->_positions[] = $shipping;
                }

                return;
            }

            $shipping = [];
            $shipping['quantity'] = 1;

            if ($this->_order['taxfree']) {
                $shipping['netto'] = $this->_shippingCosts;
                $shipping['tax'] = 0;
            } else {
                $shipping['netto'] = $this->_shippingCosts / (100 + $taxShipping) * 100;
                $shipping['tax'] = $taxShipping;
            }
            $shipping['price'] = $this->_shippingCosts;
            $shipping['amount'] = $shipping['price'];
            $shipping['modus'] = 1;
            $shipping['amount_netto'] = $shipping['netto'];
            $shipping['articleordernumber'] = '';
            $shipping['name'] = $shippingName;

            $this->_positions[] = $shipping;
        }
    }

    /**
     * Get all order positions
     */
    public function getPositions()
    {
        $translator = Shopware()->Container()->get('translation');
        $orderLocale = $this->_order['language'];

        $this->_positions = new ArrayObject(Shopware()->Db()->fetchAll('
        SELECT
            od.*, a.taxID as articleTaxID, d.kind,
            at.attr1, at.attr2, at.attr3, at.attr4, at.attr5, at.attr6, at.attr7, at.attr8, at.attr9, at.attr10,
            at.attr11, at.attr12, at.attr13, at.attr14, at.attr15, at.attr16, at.attr17, at.attr18, at.attr19, at.attr20
        FROM  s_order_details od

        LEFT JOIN s_articles_details d
        ON  d.ordernumber=od.articleordernumber
        AND d.articleID=od.articleID
        AND od.modus=0

        LEFT JOIN s_articles_attributes at
        ON at.articledetailsID=d.id

        LEFT JOIN s_articles a
        ON d.articleID = a.id

        WHERE od.orderID=?
        ORDER BY od.id ASC
        ', [$this->_id]), ArrayObject::ARRAY_AS_PROPS);

        foreach ($this->_positions as $key => $dummy) {
            $position = $this->_positions->offsetGet($key);

            $position['attributes'] = Shopware()->Db()->fetchRow('
            SELECT * FROM s_order_details_attributes WHERE detailID = ?
            ', [$position['id']]);

            if (in_array((int) $position['modus'], [0, 1], true)) {
                $kind = (int) $position['kind'];
                $translation = $translator->read(
                    $orderLocale,
                    $kind === 1 ? 'article' : 'variant',
                    $position[$kind === 1 ? 'articleID' : 'articleDetailID']
                );

                $position = $this->assignAttributeTranslation($position, $translation);
            }

            $this->_positions->offsetSet($key, $position);
        }
    }

    /**
     * Get maximum used tax-rate in this order
     *
     * @return int|string
     */
    public function getMaxTaxRate()
    {
        $maxTax = 0;
        foreach ($this->_positions as $position) {
            if ($position['mode'] == 0) {
                $getTax = $position['tax_rate'];
                if (empty($getTax)) {
                    $position['tax'] = $this->getTaxRepository()->getTaxRateByConditions(
                        $position['taxID'],
                        $this->_shipping['country']['areaID'],
                        $this->_shipping['countryID'],
                        $this->_shipping['stateID'],
                        $this->_user['customergroupID']
                    );
                }
                if ($getTax > $maxTax) {
                    $maxTax = $getTax;
                }
            }
        }

        return $maxTax;
    }

    /**
     * Process basket positions and add tax-information
     */
    public function processPositions()
    {
        foreach ($this->_positions as $key => $dummy) {
            $position = $this->_positions->offsetGet($key);

            $position['name'] = str_replace(['€'], ['&euro;'], $position['name']);
            if (empty($position['quantity'])) {
                continue;
            }

            /*
            modus 0 = default article
            modus 1 = premium articles
            modus 2 = voucher
            modus 3 = customergroup discount
            modus 4 = payment surcharge / discount
            modus 10 = bundle discount
            modus 12 = trusted shops article
            */
            if ($position['modus'] == 0 || $position['modus'] == 4 || $position['modus'] == 3 || $position['modus'] == 10 || $position['modus'] == 12) {
                /*
                Read tax for each order position
                */
                if ($position['modus'] == 4 || $position['modus'] == 3) {
                    if (empty($position['tax_rate'])) {
                        // Discounts get tax from configuration
                        if (!empty(Shopware()->Config()->sTAXAUTOMODE)) {
                            $tax = $this->getMaxTaxRate();
                        } else {
                            $tax = Shopware()->Config()->sDISCOUNTTAX;
                        }
                        $position['tax'] = $tax;
                    } else {
                        $position['tax'] = $position['tax_rate'];
                    }
                } elseif (empty($position['taxID'])) {
                    // Articles get tax per item configuration
                    if (empty($position['tax_rate'])) {
                        $position['tax'] = $this->getTaxRepository()->getTaxRateByConditions(
                            $position['articleTaxID'],
                            $this->_shipping['country']['areaID'],
                            $this->_shipping['countryID'],
                            $this->_shipping['stateID'],
                            $this->_user['customergroupID']
                        );
                    } else {
                        $position['tax'] = $position['tax_rate'];
                    }
                } else {
                    // Bundles tax
                    if (empty($position['tax_rate'])) {
                        $position['tax'] = $this->getTaxRepository()->getTaxRateByConditions(
                            $position['taxID'],
                            $this->_shipping['country']['areaID'],
                            $this->_shipping['countryID'],
                            $this->_shipping['stateID'],
                            $this->_user['customergroupID']
                        );
                    } else {
                        $position['tax'] = $position['tax_rate'];
                    }
                }

                if ($this->_net == true) {
                    $position['netto'] = round($position['price'], 2);
                    $position['price'] = Shopware()->Container()->get('shopware.cart.net_rounding')->round($position['price'], $position['tax']);
                } else {
                    $position['netto'] = $position['price'] / (100 + $position['tax']) * 100;
                }
            } elseif ($position['modus'] == 2) {
                $ticketResult = Shopware()->Db()->fetchRow('
                SELECT * FROM s_emarketing_vouchers WHERE ordercode=?
                ', [$position['articleordernumber']]);

                if (empty($position['tax_rate'])) {
                    if ($ticketResult['taxconfig'] === 'default' || empty($ticketResult['taxconfig'])) {
                        $position['tax'] = Shopware()->Config()->sVOUCHERTAX;
                    // Pre 3.5.4 behaviour
                    } elseif ($ticketResult['taxconfig'] === 'auto') {
                        // Check max. used tax-rate from basket
                        $position['tax'] = $this->getMaxTaxRate();
                    } elseif ((int) $ticketResult['taxconfig']) {
                        // Fix defined tax
                        $temporaryTax = (int) $ticketResult['taxconfig'];
                        $getTaxRate = Shopware()->Db()->fetchOne("
                        SELECT tax FROM s_core_tax WHERE id = $temporaryTax
                        ");
                        $position['tax'] = $getTaxRate;
                    } else {
                        $position['tax'] = 0;
                    }
                } else {
                    $position['tax'] = $position['tax_rate'];
                }
                if ($this->_net == true) {
                    $position['netto'] = $position['price'];
                    $position['price'] = $position['price'] * (1 + $position['tax'] / 100);
                } else {
                    $position['netto'] = $position['price'] / (100 + $position['tax']) * 100;
                }
            } elseif ($position['modus'] == 1) {
                $position['tax'] = 0;
                $position['netto'] = 0;
            }

            $position['amount_netto'] = round($position['netto'] * $position['quantity'], 2);

            $position['amount'] = round($position['price'] * $position['quantity'], 2);

            $this->_amountNetto += $position['amount_netto'];
            $this->_amount += $position['amount'];

            if (!empty($position['tax'])) {
                $this->_tax[number_format((float) $position['tax'], 2)] += round($position['amount'] - $position['amount_netto'], 2);
            }
            if ($position['amount'] <= 0) {
                $this->_discount += $position['amount'];
            }

            $this->_positions->offsetSet($key, $position);
        }

        $parameters = Shopware()->Container()->get('events')->filter(
            'Shopware_Models_Order_Document_Filter_Parameters',
            $this->getParameters(),
            ['subject' => $this]
        );

        $this->setParameters($parameters);
    }

    /**
     * Get user details
     */
    public function getUser()
    {
        $sql = '
        SELECT u.*, g.id as customergroupID
        FROM s_user u
        LEFT JOIN s_core_customergroups g ON u.customergroup = g.groupkey
        WHERE u.id = ?';

        $user = Shopware()->Db()->fetchRow($sql, [$this->_userID]);

        if ($user) {
            $this->_user = new ArrayObject($user, ArrayObject::ARRAY_AS_PROPS);
        }
    }

    /**
     * Get user's billing address
     */
    public function getBilling()
    {
        $this->_billing = new ArrayObject(Shopware()->Db()->fetchRow('
        SELECT sob.*,IF(sob.ustid IS NULL OR sob.ustid = "", sub.ustid, sob.ustid) as ustid,u.customernumber FROM s_order_billingaddress AS sob
        LEFT JOIN s_user_addresses AS sub ON sub.id = ?
        LEFT JOIN s_user u ON u.id = sub.user_id
        WHERE sob.userID = ? AND
        sob.orderID = ?
        ', [$this->_user['default_billing_address_id'], $this->_userID, $this->_id]), ArrayObject::ARRAY_AS_PROPS);

        $this->_billing['country'] = new ArrayObject(Shopware()->Db()->fetchRow('
        SELECT * FROM s_core_countries
        WHERE id=?
        ', [$this->_billing['countryID']]), ArrayObject::ARRAY_AS_PROPS);

        if (!empty($this->_billing['stateID'])) {
            $this->_billing['state'] = new ArrayObject(Shopware()->Db()->fetchRow('
            SELECT * FROM s_core_countries_states
            WHERE id=?
            ', [$this->_billing['stateID']]), ArrayObject::ARRAY_AS_PROPS);
        } else {
            $this->_billing['state'] = [];
        }

        $this->_billing['attributes'] = Shopware()->Db()->fetchRow('
        SELECT * FROM s_order_billingaddress_attributes WHERE billingID = ?
        ', [$this->_billing['id']]);
    }

    /**
     * Get user's shipping address
     */
    public function getShipping()
    {
        if (!$this->_userID && $this->_billing) {
            $this->_shipping = clone $this->_billing;

            return;
        } elseif (!$this->_userID) {
            return;
        }

        $shipping = Shopware()->Db()->fetchRow('
            SELECT * FROM s_order_shippingaddress WHERE userID = ? AND
            orderID = ?
        ', [$this->_userID, $this->_id]);

        if ($shipping) {
            $this->_shipping = new ArrayObject($shipping, ArrayObject::ARRAY_AS_PROPS);
        }

        $this->_shipping['attributes'] = Shopware()->Db()->fetchRow('
        SELECT * FROM s_order_shippingaddress_attributes WHERE shippingID = ?
        ', [$this->_shipping['id']]);

        if (!$this->_shipping) {
            $this->_shipping = clone $this->_billing;
        } else {
            if (empty($this->_shipping['countryID'])) {
                $this->_shipping['countryID'] = $this->_billing['countryID'];
            }
            $this->_shipping['country'] = new ArrayObject(Shopware()->Db()->fetchRow('
            SELECT * FROM s_core_countries
            WHERE id=?
            ', [$this->_shipping['countryID']]), ArrayObject::ARRAY_AS_PROPS);

            if (!empty($this->_shipping['stateID'])) {
                $this->_shipping['state'] = new ArrayObject(Shopware()->Db()->fetchRow('
                SELECT * FROM s_core_countries_states
                WHERE id=?
                ', [$this->_shipping['stateID']]), ArrayObject::ARRAY_AS_PROPS);
            } else {
                $this->_shipping['state'] = [];
            }
        }
    }

    /**
     * Get dispatch information
     */
    public function getDispatch()
    {
        $dispatch_table = 's_premium_dispatch';

        $this->_dispatch = Shopware()->Db()->fetchRow("
            SELECT name, description FROM $dispatch_table
            WHERE id = ?
        ", [$this->_order['dispatchID']]);

        if (empty($this->_dispatch)) {
            $this->_dispatch = new ArrayObject();
        }
        $this->_dispatch = new ArrayObject($this->_dispatch, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Get payment information
     */
    public function getPayment()
    {
        $paymentData = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_core_paymentmeans WHERE id = ?',
            [$this->_order['paymentID']]
        );

        $this->_payment = new ArrayObject(
            $paymentData ?: [],
            ArrayObject::ARRAY_AS_PROPS
        );
        if (!empty($this->_payment['table'])) {
            $specificPaymentData = Shopware()->Db()->fetchRow(
                'SELECT * FROM ' . $this->_payment['table'] . ' WHERE userID=?',
                $this->_userID
            );

            $this->_payment['data'] = new ArrayObject(
                $specificPaymentData,
                ArrayObject::ARRAY_AS_PROPS
            );
        }
    }

    /**
     * Get payment instances information
     */
    public function getPaymentInstances()
    {
        $this->_paymentInstances = new ArrayObject(
            Shopware()->Db()->fetchAll('
                SELECT * FROM s_core_payment_instance
                WHERE order_id=?',
                [$this->_id]
            ),
            ArrayObject::ARRAY_AS_PROPS
        );
    }

    /**
     * @return array
     */
    private function getParameters()
    {
        return [
            'positions' => $this->_positions,
            'amountNetto' => $this->_amountNetto,
            'amount' => $this->_amount,
            'discount' => $this->_discount,
            'tax' => $this->_tax,
            'net' => $this->_net,
            'shipping' => $this->_shipping,
            'user' => $this->_user,
        ];
    }

    private function setParameters(array $parameters)
    {
        $this->_positions = $parameters['positions'];
        $this->_amountNetto = $parameters['amountNetto'];
        $this->_amount = $parameters['amount'];
        $this->_discount = $parameters['discount'];
        $this->_tax = $parameters['tax'];
        $this->_net = $parameters['net'];
        $this->_shipping = $parameters['shipping'];
        $this->_user = $parameters['user'];
    }

    /**
     * Helper function to get access to the tax repository.
     *
     * @return \Shopware\Models\Tax\Repository
     */
    private function getTaxRepository()
    {
        if ($this->_taxRepository === null) {
            $this->_taxRepository = Shopware()->Models()->getRepository('Shopware\Models\Tax\Tax');
        }

        return $this->_taxRepository;
    }

    /**
     * Helper function to Return the nearest tax rate of an approximate tax rate (used in processOrder())
     * Set $maxDiff to change how big the maximum difference between the approximate and defined tax rates can be
     *
     * @param int|float $approximateTaxRate
     * @param int       $areaId
     * @param int       $countryId
     * @param int       $stateId
     * @param int       $customerGroupId
     * @param int|float $maxDiff
     *
     * @return string
     */
    private function getTaxRateByApproximateTaxRate($approximateTaxRate, $areaId, $countryId, $stateId, $customerGroupId, $maxDiff = 0.1)
    {
        $sql = 'SELECT tax, ABS(tax - ?) as difference
                FROM `s_core_tax`
                WHERE ABS(tax - ?) <= ?
            UNION
                SELECT tax, ABS(tax - ?) as difference
                FROM `s_core_tax_rules`
                WHERE active = 1 AND ABS(tax - ?) <= ?
                AND
                    (areaID = ? OR areaID IS NULL)
                AND
                    (countryID = ? OR countryID IS NULL)
                AND
                    (stateID = ? OR stateID IS NULL)
                AND
                    (customer_groupID = ? OR customer_groupID = 0 OR customer_groupID IS NULL)
                ORDER BY difference
                LIMIT 1
                ';

        $taxRate = Shopware()->Db()->fetchOne($sql, [
                $approximateTaxRate, // p.e. 19.971195391 (approx. 20% VAT)
                $approximateTaxRate,
                $maxDiff, //default: 0.1
                $approximateTaxRate,
                $approximateTaxRate,
                $maxDiff,
                $areaId, //p.e. 3 (Europe)
                $countryId, // p.e. 23 (AT)
                $stateId, //p.e. 0
                $customerGroupId, //p.e. 1 (EK)
            ]);

        if (!$taxRate) {
            $taxRate = round($approximateTaxRate);
        }

        return $taxRate;
    }

    private function getDemoData()
    {
        return include __DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'OrderData.php';
    }

    /**
     * Returns prices from invoice positions
     *
     * @return Price[]
     */
    private function getPricePositions()
    {
        $prices = [];

        foreach ($this->_positions as $position) {
            if ($position['modus'] === '0') {
                $prices[] = new Price($position['amount'], $position['amount_netto'], (float) $position['tax_rate'], null);
            }
        }

        return $prices;
    }

    /**
     * This method overwrites all attribute values with the translated value
     * in case there is one.
     */
    private function assignAttributeTranslation(array $position, array $translation): array
    {
        $prefix = '__attribute_';

        foreach ($translation as $key => $value) {
            // Abort if the current value is empty or not a translated attribute
            if ($value === '' || strpos($key, $prefix) !== 0) {
                continue;
            }

            $attributeKey = substr($key, strlen($prefix));
            $position[$attributeKey] = $value;
        }

        return $position;
    }
}
