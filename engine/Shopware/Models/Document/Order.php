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

use Shopware\Bundle\CartBundle\CartKey;
use Shopware\Bundle\CartBundle\CartPositionsMode;
use Shopware\Bundle\CartBundle\CheckoutKey;
use Shopware\Bundle\StoreFrontBundle\Gateway\CountryGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Country;
use Shopware\Bundle\StoreFrontBundle\Struct\Country\State;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Cart\Struct\Price;
use Shopware\Components\Cart\TaxAggregatorInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Shop\Locale as ShopLocale;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Tax\Repository as TaxRepository;
use Shopware\Models\Tax\Tax;

/**
 * Order model for document generation
 */
class Shopware_Models_Document_Order extends Enlight_Class implements Enlight_Hook
{
    /**
     * ID of the order (s_order.id)
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
     * ID of the customer (s_user.id)
     *
     * @var int
     */
    protected $_userID;

    /**
     * Metadata of the customer (email,customergroup etc. s_user.*)
     *
     * @var ArrayObject
     */
    protected $_user;

    /**
     * Billing data for this order / customer (s_order_billingaddress)
     *
     * @var ArrayObject|null
     */
    protected $_billing;

    /**
     * Shipping data for this order / customer (s_order_shippingaddress)
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
     * @var TaxRepository|null
     */
    protected $_taxRepository;

    private ShopContextInterface $context;

    private CountryGatewayInterface $countryGateway;

    private LegacyStructConverter $structConverter;

    /**
     * @param int                  $id
     * @param array<string, mixed> $config
     */
    public function __construct($id, $config = [])
    {
        // Test-data for preview mode
        if ((bool) $config['_preview'] === true && (bool) $config['_previewSample'] === true) {
            $array = $this->getDemoData();

            $array['_order']->language = 1;

            foreach ($array as $key => $element) {
                $this->$key = $element;
            }

            return;
        }

        $this->_id = $id;
        $this->_config = $config;
        $this->_summaryNet = (bool) ($config['summaryNet'] ?? false);
        $this->_shippingCostsAsPosition = (bool) $config['shippingCostsAsPosition'];

        $this->getOrder();

        $this->initializeShopContext((int) $this->_order['language']);
        $this->countryGateway = Shopware()->Container()->get(CountryGatewayInterface::class);
        $this->structConverter = Shopware()->Container()->get(LegacyStructConverter::class);

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
     * @return array<string, mixed>
     */
    public function __toArray()
    {
        $array = get_object_vars($this);

        $array['_order'] = $array['_order']->getArrayCopy();
        $array['_positions'] = $array['_positions']->getArrayCopy();

        if (!empty($array['_user'])) {
            $array['_user'] = $array['_user']->getArrayCopy();
        }

        $array['_billing'] = $array['_billing'] !== null ? $array['_billing']->getArrayCopy() : null;
        $array['_shipping'] = $array['_shipping'] !== null ? $array['_shipping']->getArrayCopy() : null;
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
     *
     * @return array<string, mixed>|ArrayObject<string, mixed>|bool|float|int|null
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
     *
     * @return void
     */
    public function getOrder()
    {
        $order = Shopware()->Db()->fetchRow(
            'SELECT s_order.*,s_core_currencies.factor,s_core_currencies.id AS currencyID,s_core_currencies.templatechar,s_core_currencies.name AS currencyName
             FROM s_order
             LEFT JOIN s_core_currencies ON s_core_currencies.currency = s_order.currency
             WHERE s_order.id = ?',
            [$this->_id]);

        if (!\is_array($order)) {
            throw new Enlight_Exception(sprintf('Order with id %d not found!', $this->_id));
        }

        $this->_order = new ArrayObject($order, ArrayObject::ARRAY_AS_PROPS);

        // Load order attributes
        $attributes = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_order_attributes WHERE orderID = ?',
            [$this->_order['id']]
        );
        $this->_order['attributes'] = \is_array($attributes) ? $attributes : [];

        $this->_userID = (int) $this->_order['userID'];
        if (!empty($this->_order['net'])) {
            $this->_net = true;
        } else {
            $this->_net = false;
        }
        $this->_currency = new ArrayObject([
            'currency' => $this->_order['currency'],
            'name' => $this->_order['currencyName'],
            'factor' => $this->_order['factor'],
            'char' => $this->_order['templatechar'],
        ], ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Get all information from a certain order (model)
     *
     * @return void
     */
    public function processOrder()
    {
        $snippetManager = Shopware()->Snippets();
        $modelManager = Shopware()->Models();
        $orderLocale = null;

        if ($modelManager !== null) {
            $orderLocale = $modelManager->find(
                ShopLocale::class,
                $this->_order['language']
            );
        }

        if ($orderLocale !== null) {
            $snippetManager->setLocale($orderLocale);
        }

        $shippingName = $snippetManager->getNamespace('documents/index')->get('ShippingCosts', 'Shipping costs', true);

        if ($this->_order['invoice_shipping_tax_rate'] === null && $this->_shipping !== null) {
            $approximateTaxRate = 0.0;
            if ((float) $this->_order['invoice_shipping_net'] !== 0.0) {
                // p.e. = 24.99 / 20.83 * 100 - 100 = 19.971195391 (approx. 20% VAT)
                $approximateTaxRate = $this->_order['invoice_shipping'] / $this->_order['invoice_shipping_net'] * 100 - 100;
            }

            $taxShipping = $this->getTaxRateByApproximateTaxRate(
                $approximateTaxRate,
                (int) $this->_shipping['country']['areaID'],
                (int) $this->_shipping['countryID'],
                (int) $this->_shipping['stateID'],
                (int) $this->_user['customergroupID']
            );
        } else {
            $taxShipping = 0.0;
            if ((float) $this->_order['invoice_shipping_net'] !== 0.0) {
                $taxShipping = (float) $this->_order['invoice_shipping_tax_rate'];
            }
        }

        $this->_shippingCosts = $this->_order['invoice_shipping'];

        if ($this->_shippingCostsAsPosition === true && !empty($this->_shippingCosts)) {
            $taxes = [];

            if ($this->_order['taxfree']) {
                $this->_amountNetto += $this->_order['invoice_shipping'];
            } else {
                if ($this->_order['is_proportional_calculation']) {
                    $taxes = Shopware()->Container()->get('shopware.cart.proportional_tax_calculator')->calculate($this->_order['invoice_shipping'], $this->getPricePositions(), false);
                    $taxNet = 0;

                    foreach ($taxes as $tax) {
                        $taxNet += $tax->getNetPrice();
                        $this->_tax[number_format($tax->getTaxRate(), 2)] += $tax->getTax();
                    }

                    $this->_amountNetto += $taxNet;
                } else {
                    $this->_amountNetto += ($this->_order['invoice_shipping'] / (100 + $taxShipping) * 100);

                    if (!empty($taxShipping) && !empty($this->_order['invoice_shipping'])) {
                        $shippingTax = Shopware()->Container()->get(TaxAggregatorInterface::class)->shippingCostsTaxSum([
                            CheckoutKey::SHIPPING_COSTS_TAX => (float) $this->_order['invoice_shipping_tax_rate'],
                            CheckoutKey::SHIPPING_COSTS_NET => (float) $this->_order['invoice_shipping_net'],
                            CheckoutKey::SHIPPING_COSTS_WITH_TAX => (float) $this->_order['invoice_shipping'],
                        ]);

                        if ($shippingTax !== null) {
                            foreach ($shippingTax as $key => $val) {
                                if (isset($this->_tax[$key])) {
                                    $this->_tax[$key] = $this->_tax[$key] + $val;
                                } else {
                                    $this->_tax[$key] = $val;
                                }
                            }
                        }
                    }
                }
            }

            $this->_amount += $this->_order['invoice_shipping'];

            if ($this->_order['is_proportional_calculation']) {
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
                    $shipping['name'] = $shippingName . ' ' . (\count($taxes) > 1 ? '(' . $tax->getTaxRate() . '%)' : '');

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
     *
     * @return void
     */
    public function getPositions()
    {
        $translator = Shopware()->Container()->get('translation');
        $orderLocale = $this->_order['language'];

        $positions = Shopware()->Db()->fetchAll(
            'SELECT
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
             ORDER BY od.id ASC',
            [$this->_id]
        );
        if (!\is_array($positions)) {
            $positions = [];
        }
        $this->_positions = new ArrayObject($positions, ArrayObject::ARRAY_AS_PROPS);

        foreach ($this->_positions as $key => $unusedPosition) {
            $position = $this->_positions->offsetGet($key);

            $attributes = Shopware()->Db()->fetchRow(
                'SELECT * FROM s_order_details_attributes WHERE detailID = ?',
                [$position['id']]
            );
            $position['attributes'] = \is_array($attributes) ? $attributes : [];

            if (\in_array((int) $position['modus'], [CartPositionsMode::PRODUCT, CartPositionsMode::PREMIUM_PRODUCT], true)) {
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
            if ((int) $position['modus'] === CartPositionsMode::PRODUCT) {
                $getTax = $position['tax_rate'];
                if (empty($getTax) && $this->_shipping !== null) {
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
     *
     * @return void
     */
    public function processPositions()
    {
        foreach ($this->_positions as $key => $dummy) {
            $position = $this->_positions->offsetGet($key);

            $position['name'] = str_replace(['€'], ['&euro;'], $position['name']);
            if (empty($position['quantity'])) {
                continue;
            }

            $allowedPositionModes = [
                CartPositionsMode::PRODUCT,
                CartPositionsMode::CUSTOMER_GROUP_DISCOUNT,
                CartPositionsMode::PAYMENT_SURCHARGE_OR_DISCOUNT,
                CartPositionsMode::SWAG_BUNDLE_DISCOUNT,
                CartPositionsMode::TRUSTED_SHOPS_PRODUCT,
            ];
            $positionMode = (int) $position['modus'];
            if (\in_array($positionMode, $allowedPositionModes, true)) {
                /*
                Read tax for each order position
                */
                if ($positionMode === CartPositionsMode::CUSTOMER_GROUP_DISCOUNT
                    || $positionMode === CartPositionsMode::PAYMENT_SURCHARGE_OR_DISCOUNT
                ) {
                    if (empty($position['tax_rate'])) {
                        // Discounts get tax from configuration
                        if (!empty(Shopware()->Config()->get('sTAXAUTOMODE'))) {
                            $tax = $this->getMaxTaxRate();
                        } else {
                            $tax = Shopware()->Config()->get('sDISCOUNTTAX');
                        }
                        $position['tax'] = $tax;
                    } else {
                        $position['tax'] = $position['tax_rate'];
                    }
                } elseif (empty($position['taxID'])) {
                    // Products get tax per item configuration
                    if (empty($position['tax_rate']) && $this->_shipping !== null) {
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
                    $position['tax'] = $position['tax_rate'];
                    // Bundles tax
                    if (empty($position['tax_rate']) && $this->_shipping !== null) {
                        $position['tax'] = $this->getTaxRepository()->getTaxRateByConditions(
                            $position['taxID'],
                            $this->_shipping['country']['areaID'],
                            $this->_shipping['countryID'],
                            $this->_shipping['stateID'],
                            $this->_user['customergroupID']
                        );
                    }
                }

                if ($this->_net === true) {
                    $position['netto'] = round($position['price'], 2);
                    $position['price'] = Shopware()->Container()->get('shopware.cart.net_rounding')->round($position['price'], $position['tax'], 1);
                } else {
                    $position['netto'] = $position['price'] / (100 + $position['tax']) * 100;
                }
            } elseif ($positionMode === CartPositionsMode::VOUCHER) {
                $ticketResult = Shopware()->Db()->fetchRow(
                    'SELECT * FROM s_emarketing_vouchers WHERE ordercode=?',
                    [$position['articleordernumber']]
                );
                if (!\is_array($ticketResult)) {
                    $ticketResult = [];
                }

                if (empty($position['tax_rate'])) {
                    if ($ticketResult['taxconfig'] === 'default' || empty($ticketResult['taxconfig'])) {
                        $position['tax'] = Shopware()->Config()->get('sVOUCHERTAX');
                    // Pre 3.5.4 behaviour
                    } elseif ($ticketResult['taxconfig'] === 'auto') {
                        // Check max. used tax-rate from basket
                        $position['tax'] = $this->getMaxTaxRate();
                    } elseif ((int) $ticketResult['taxconfig']) {
                        // Fix defined tax
                        $temporaryTax = (int) $ticketResult['taxconfig'];
                        $position['tax'] = (float) Shopware()->Db()->fetchOne(
                            'SELECT tax FROM s_core_tax WHERE id = ?',
                            [$temporaryTax]
                        );
                    } else {
                        $position['tax'] = 0.0;
                    }
                } else {
                    $position['tax'] = $position['tax_rate'];
                }
                if ($this->_net === true) {
                    $position['netto'] = $position['price'];
                    $position['price'] *= (1 + $position['tax'] / 100);
                } else {
                    $position['netto'] = $position['price'] / (100 + $position['tax']) * 100;
                }
            } elseif ($positionMode === CartPositionsMode::PREMIUM_PRODUCT) {
                $position['tax'] = 0;
                $position['netto'] = 0;
            }

            $position['amount_netto'] = round($position['netto'] * $position['quantity'], 2);
            $position['amount'] = round($position['price'] * $position['quantity'], 2);

            $this->_amountNetto += $position['amount_netto'];
            $this->_amount += $position['amount'];

            if ($position['amount'] <= 0) {
                $this->_discount += $position['amount'];
            }

            $this->_positions->offsetSet($key, $position);
        }

        $cartPositions = array_map(static function (array $position): array {
            $position['tax'] = $position['amount'] - $position['amount_netto'];

            return $position;
        }, $this->_positions->getArrayCopy());

        $positionsTaxSum = Shopware()->Container()->get(TaxAggregatorInterface::class)->positionsTaxSum(
            [
                CartKey::POSITIONS => $cartPositions,
            ],
            (float) $this->getMaxTaxRate()
        );

        $this->_tax = $positionsTaxSum ?? [];

        $parameters = Shopware()->Container()->get('events')->filter(
            'Shopware_Models_Order_Document_Filter_Parameters',
            $this->getParameters(),
            ['subject' => $this]
        );

        $this->setParameters($parameters);
    }

    /**
     * Get customer details
     *
     * @return void
     */
    public function getUser()
    {
        $customer = Shopware()->Db()->fetchRow(
            'SELECT u.*, g.id as customergroupID
             FROM s_user u
             LEFT JOIN s_core_customergroups g ON u.customergroup = g.groupkey
             WHERE u.id = ?',
            [$this->_userID]
        );

        $this->_user = new ArrayObject(
            \is_array($customer) ? $customer : [],
            ArrayObject::ARRAY_AS_PROPS
        );
    }

    /**
     * Get customer billing address
     *
     * @return void
     */
    public function getBilling()
    {
        $billingData = Shopware()->Db()->fetchRow(
            "SELECT sob.*,IF(sob.ustid IS NULL OR sob.ustid = '', sub.ustid, sob.ustid) as ustid,u.customernumber
             FROM s_order_billingaddress AS sob
             LEFT JOIN s_user_addresses AS sub ON sub.id = ?
             LEFT JOIN s_user u ON u.id = sub.user_id
             WHERE sob.userID = ? AND sob.orderID = ?",
            [
                $this->_user['default_billing_address_id'],
                $this->_userID,
                $this->_id,
            ]
        );
        if (!\is_array($billingData)) {
            $this->_billing = null;

            return;
        }

        $this->_billing = new ArrayObject($billingData, ArrayObject::ARRAY_AS_PROPS);

        $country = $this->getCountry((int) $this->_billing['countryID']);
        $this->_billing['country'] = new ArrayObject($country, ArrayObject::ARRAY_AS_PROPS);

        $countryState = [];
        if (!empty($this->_billing['stateID'])) {
            $countryState = $this->getCountryState((int) $this->_billing['stateID']);
        }
        $this->_billing['state'] = new ArrayObject($countryState, ArrayObject::ARRAY_AS_PROPS);

        $attributes = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_order_billingaddress_attributes WHERE billingID = ?',
            [$this->_billing['id']]
        );
        $this->_billing['attributes'] = \is_array($attributes) ? $attributes : [];
    }

    /**
     * Get customer shipping address
     *
     * @return void
     */
    public function getShipping()
    {
        if (!$this->_userID && $this->_billing) {
            $this->_shipping = clone $this->_billing;

            return;
        }

        if (!$this->_userID) {
            return;
        }

        $shipping = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_order_shippingaddress WHERE userID = ? AND orderID = ?',
            [$this->_userID, $this->_id]
        );

        if (\is_array($shipping)) {
            $this->_shipping = new ArrayObject($shipping, ArrayObject::ARRAY_AS_PROPS);
        }

        if ($this->_shipping === null && $this->_billing !== null) {
            $this->_shipping = clone $this->_billing;
        } else {
            if (empty($this->_shipping['countryID']) && $this->_billing !== null) {
                $this->_shipping['countryID'] = $this->_billing['countryID'];
            }
            if (!isset($this->_shipping['countryID'])) {
                throw new RuntimeException('Country ID not set in shipping address');
            }

            $country = $this->getCountry((int) $this->_shipping['countryID']);
            $this->_shipping['country'] = new ArrayObject($country, ArrayObject::ARRAY_AS_PROPS);

            $countryState = [];
            if (!empty($this->_shipping['stateID'])) {
                $countryState = $this->getCountryState((int) $this->_shipping['stateID']);
            }
            $this->_shipping['state'] = new ArrayObject($countryState, ArrayObject::ARRAY_AS_PROPS);
        }

        if ($this->_shipping !== null && !empty($this->_shipping['id'])) {
            $attributes = Shopware()->Db()->fetchRow(
                'SELECT * FROM s_order_shippingaddress_attributes WHERE shippingID = ?',
                [$this->_shipping['id']]
            );
            $this->_shipping['attributes'] = \is_array($attributes) ? $attributes : [];
        }
    }

    /**
     * Get dispatch information
     *
     * @return void
     */
    public function getDispatch()
    {
        $dispatch = Shopware()->Db()->fetchRow(
            'SELECT name, description
             FROM s_premium_dispatch
             WHERE id = ?',
            [$this->_order['dispatchID']]
        );

        $this->_dispatch = new ArrayObject(
            \is_array($dispatch) ? $dispatch : [],
            ArrayObject::ARRAY_AS_PROPS
        );
    }

    /**
     * Get payment information
     *
     * @return void
     */
    public function getPayment()
    {
        $paymentData = Shopware()->Db()->fetchRow(
            'SELECT * FROM s_core_paymentmeans WHERE id = ?',
            [$this->_order['paymentID']]
        );

        $this->_payment = new ArrayObject(
            \is_array($paymentData) ? $paymentData : [],
            ArrayObject::ARRAY_AS_PROPS
        );
        if (!empty($this->_payment['table'])) {
            $specificPaymentData = Shopware()->Db()->fetchRow(
                'SELECT * FROM ' . $this->_payment['table'] . ' WHERE userID=?',
                $this->_userID
            );

            $this->_payment['data'] = new ArrayObject(
                \is_array($specificPaymentData) ? $specificPaymentData : [],
                ArrayObject::ARRAY_AS_PROPS
            );
        }
    }

    /**
     * Get payment instances information
     *
     * @return void
     */
    public function getPaymentInstances()
    {
        $paymentInstances = Shopware()->Db()->fetchAll(
            'SELECT * FROM s_core_payment_instance
             WHERE order_id=?',
            [$this->_id]
        );
        $this->_paymentInstances = new ArrayObject($paymentInstances, ArrayObject::ARRAY_AS_PROPS);
    }

    private function initializeShopContext(int $shopId): void
    {
        $shop = Shopware()->Container()->get('models')->getRepository(Shop::class)->find($shopId);
        if (!$shop instanceof Shop) {
            throw new ModelNotFoundException(Shop::class, $shopId);
        }
        Shopware()->Container()->get(ShopRegistrationServiceInterface::class)->registerShop($shop);
        $this->context = Shopware()->Container()->get(ContextServiceInterface::class)->getShopContext();
    }

    /**
     * @return array<string, mixed>
     */
    private function getParameters(): array
    {
        return [
            'positions' => $this->_positions,
            'amountNetto' => $this->_amountNetto,
            'amount' => $this->_amount,
            'discount' => $this->_discount,
            'tax' => $this->_tax,
            'net' => $this->_net,
            'billing' => $this->_billing,
            'shipping' => $this->_shipping,
            'user' => $this->_user,
        ];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function setParameters(array $parameters): void
    {
        $this->_positions = $parameters['positions'];
        $this->_amountNetto = (float) $parameters['amountNetto'];
        $this->_amount = (float) $parameters['amount'];
        $this->_discount = (float) $parameters['discount'];
        $this->_tax = $parameters['tax'];
        $this->_net = (bool) $parameters['net'];
        $this->_billing = $parameters['billing'];
        $this->_shipping = $parameters['shipping'];
        $this->_user = $parameters['user'];
    }

    private function getTaxRepository(): TaxRepository
    {
        if ($this->_taxRepository === null) {
            $this->_taxRepository = Shopware()->Models()->getRepository(Tax::class);
        }

        return $this->_taxRepository;
    }

    /**
     * Helper function to Return the nearest tax rate of an approximate tax rate (used in processOrder())
     * Set $maxDiff to change how big the maximum difference between the approximate and defined tax rates can be
     */
    private function getTaxRateByApproximateTaxRate(
        float $approximateTaxRate,
        int $areaId,
        int $countryId,
        int $stateId,
        int $customerGroupId,
        float $maxDiff = 0.1
    ): float {
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

        if ($taxRate === false) {
            $taxRate = round($approximateTaxRate);
        }

        return (float) $taxRate;
    }

    /**
     * @return array<string, mixed>
     */
    private function getDemoData(): array
    {
        return include __DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'OrderData.php';
    }

    /**
     * Returns prices from invoice positions
     *
     * @return Price[]
     */
    private function getPricePositions(): array
    {
        $prices = [];

        foreach ($this->_positions as $position) {
            if ((int) $position['modus'] === CartPositionsMode::PRODUCT) {
                $prices[] = new Price($position['amount'], $position['amount_netto'], (float) $position['tax_rate'], null);
            }
        }

        return $prices;
    }

    /**
     * This method overwrites all attribute values with the translated value
     * in case there is one.
     *
     * @param array<string, string> $position
     * @param array<string, string> $translation
     *
     * @return array<string, string>
     */
    private function assignAttributeTranslation(array $position, array $translation): array
    {
        $prefix = '__attribute_';

        foreach ($translation as $key => $value) {
            // Abort if the current value is empty or not a translated attribute
            if ($value === '' || strpos($key, $prefix) !== 0) {
                continue;
            }

            $attributeKey = substr($key, \strlen($prefix));
            $position[$attributeKey] = $value;
        }

        return $position;
    }

    /**
     * @return array<string, mixed>
     */
    private function getCountry(int $countryId): array
    {
        $country = $this->countryGateway->getCountry($countryId, $this->context);
        if (!$country instanceof Country) {
            throw new RuntimeException(sprintf('Country with ID "%s" not found', $countryId));
        }

        return $this->structConverter->convertCountryStruct($country);
    }

    /**
     * @return array<string, mixed>
     */
    private function getCountryState(int $countryStateId): array
    {
        $countryState = $this->countryGateway->getState($countryStateId, $this->context);
        if (!$countryState instanceof State) {
            return [];
        }

        return $this->structConverter->convertStateStruct($countryState);
    }
}
