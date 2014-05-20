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
 * @category  Shopware
 * @package   Shopware\Controllers\Frontend
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Controllers_Frontend_Checkout extends Enlight_Controller_Action
{
    /**
     * Reference to sAdmin object (core/class/sAdmin.php)
     *
     * @var sAdmin
     */
    protected $admin;

    /**
     * Reference to sBasket object (core/class/sBasket.php)
     *
     * @var sBasket
     */
    protected $basket;

    /**
     * Reference to Shopware session object (Shopware()->Session)
     *
     * @var Zend_Session_Namespace
     */
    protected $session;

    /**
     * Init method that get called automatically
     *
     * Set class properties
     */
    public function init()
    {
        $this->admin = Shopware()->Modules()->Admin();
        $this->basket = Shopware()->Modules()->Basket();
        $this->session = Shopware()->Session();
    }

    /**
     * Pre dispatch method
     */
    public function preDispatch()
    {
        $this->View()->setScope(Enlight_Template_Manager::SCOPE_PARENT);

        $this->View()->sUserLoggedIn = $this->admin->sCheckUser();
        $this->View()->sUserData = $this->getUserData();
    }

    /**
     * Save basket to session
     */
    public function postDispatch()
    {
        $this->session->sBasketCurrency = Shopware()->Shop()->getCurrency()->getId();
        $this->session->sBasketQuantity = $this->basket->sCountBasket();
        $amount = $this->basket->sGetAmount();
        $this->session->sBasketAmount = empty($amount) ? 0 : array_shift($amount);
    }

    /**
     * Forward to cart or confirm action depending on user state
     */
    public function indexAction()
    {
        if ($this->basket->sCountBasket()<1 || empty($this->View()->sUserLoggedIn)) {
            $this->forward('cart');
        } else {
            $this->forward('confirm');
        }
    }

    /**
     * Read all data from objects / models that are required in cart view
     * (User-Data / Payment-Data / Basket-Data etc.)
     */
    public function cartAction()
    {
        $this->View()->sCountry = $this->getSelectedCountry();
        $this->View()->sPayment = $this->getSelectedPayment();
        $this->View()->sDispatch = $this->getSelectedDispatch();
        $this->View()->sCountryList = $this->getCountryList();
        $this->View()->sPayments = $this->getPayments();
        $this->View()->sDispatches = $this->getDispatches();
        $this->View()->sState = $this->getSelectedState();

        $this->View()->sUserData = $this->getUserData();
        $this->View()->sBasket = $this->getBasket();

        $this->View()->sShippingcosts = $this->View()->sBasket['sShippingcosts'];
        $this->View()->sShippingcostsDifference = $this->View()->sBasket['sShippingcostsDifference'];
        $this->View()->sAmount = $this->View()->sBasket['sAmount'];
        $this->View()->sAmountWithTax = $this->View()->sBasket['sAmountWithTax'];
        $this->View()->sAmountTax = $this->View()->sBasket['sAmountTax'];
        $this->View()->sAmountNet = $this->View()->sBasket['AmountNetNumeric'];

        $this->View()->sMinimumSurcharge = $this->getMinimumCharge();
        $this->View()->sPremiums = $this->getPremiums();

        $this->View()->sInquiry = $this->getInquiry();
        $this->View()->sInquiryLink = $this->getInquiryLink();

        $this->View()->sTargetAction = 'cart';
    }

    /**
     * Mostly equivalent to cartAction
     * Get user- basket- and payment-data for view assignment
     * Create temporary entry in s_order table
     * Check some conditions (minimum charge)
     *
     * @return void
     */
    public function confirmAction()
    {
        if (empty($this->View()->sUserLoggedIn)) {
            return $this->forward('login', 'account', null, array('sTarget'=>'checkout'));
        } elseif ($this->basket->sCountBasket() < 1) {
            return $this->forward('cart');
        }

        $this->View()->sCountry = $this->getSelectedCountry();
        $this->View()->sState = $this->getSelectedState();
        $this->View()->sPayment = $this->getSelectedPayment();
        $this->View()->sUserData["payment"] = $this->View()->sPayment;

        $this->View()->sDispatch = $this->getSelectedDispatch();
        $this->View()->sPayments = $this->getPayments();
        $this->View()->sDispatches = $this->getDispatches();

        $this->View()->sBasket = $this->getBasket();

        $this->View()->sLaststock = $this->basket->sCheckBasketQuantities();
        $this->View()->sShippingcosts = $this->View()->sBasket['sShippingcosts'];
        $this->View()->sShippingcostsDifference = $this->View()->sBasket['sShippingcostsDifference'];
        $this->View()->sAmount = $this->View()->sBasket['sAmount'];
        $this->View()->sAmountWithTax = $this->View()->sBasket['sAmountWithTax'];
        $this->View()->sAmountTax = $this->View()->sBasket['sAmountTax'];
        $this->View()->sAmountNet = $this->View()->sBasket['AmountNetNumeric'];

        $this->View()->sPremiums = $this->getPremiums();

        $this->View()->sNewsletter = isset($this->session['sNewsletter']) ? $this->session['sNewsletter'] : null;
        $this->View()->sComment = isset($this->session['sComment']) ? $this->session['sComment'] : null;

        $this->View()->sShowEsdNote = $this->getEsdNote();
        $this->View()->sDispatchNoOrder = $this->getDispatchNoOrder();
        $this->View()->sRegisterFinished = !empty($this->session['sRegisterFinished']);

        $this->saveTemporaryOrder();

        if ($this->getMinimumCharge()) {
            return $this->forward('cart');
        }

        $this->session['sOrderVariables'] = new ArrayObject($this->View()->getAssign(), ArrayObject::ARRAY_AS_PROPS);

        $agbChecked = $this->Request()->getParam('sAGB');
        if (!empty($agbChecked)) {
            $this->View()->assign('sAGBChecked', true);
        }

        $this->View()->sTargetAction = 'confirm';
    }

    /**
     * Called from confirmAction View
     * Customers requests to finish current order
     * Check if all conditions match and save order
     *
     * @return void
     */
    public function finishAction()
    {
        if ($this->Request()->getParam('sUniqueID') && !empty($this->session['sOrderVariables'])) {
            $sql = '
                SELECT transactionID as sTransactionumber, ordernumber as sOrderNumber
                FROM s_order
                WHERE temporaryID=? AND userID=?
            ';

            $order = Shopware()->Db()->fetchRow($sql, array($this->Request()->getParam('sUniqueID'), Shopware()->Session()->sUserId));
            if (!empty($order)) {
                $this->View()->assign($order);
                $this->View()->assign($this->session['sOrderVariables']->getArrayCopy());
                return;
            }
        }

        if (empty($this->session['sOrderVariables'])||$this->getMinimumCharge()||$this->getEsdNote()||$this->getDispatchNoOrder()) {
            return $this->forward('confirm');
        }

        $checkQuantities = $this->basket->sCheckBasketQuantities();
        if (!empty($checkQuantities['hideBasket'])) {
            return $this->forward('confirm');
        }

        $this->View()->assign($this->session['sOrderVariables']->getArrayCopy());

        if ($this->basket->sCountBasket()>0
                && empty($this->View()->sUserData['additional']['payment']['embediframe'])) {
            if ($this->Request()->getParam('sNewsletter')!==null) {
                $this->session['sNewsletter'] = $this->Request()->getParam('sNewsletter') ? true : false;
            }
            if ($this->Request()->getParam('sComment')!==null) {
                $this->session['sComment'] = trim(strip_tags($this->Request()->getParam('sComment')));
            }
            if (!Shopware()->Config()->get('IgnoreAGB') && !$this->Request()->getParam('sAGB')) {
                $this->View()->sAGBError = true;
                return $this->forward('confirm');
            }
            if (!empty($this->session['sNewsletter'])) {
                $this->admin->sUpdateNewsletter(true, $this->admin->sGetUserMailById(), true);
            }
            $this->saveOrder();
        }

        $this->View()->assign($this->session['sOrderVariables']->getArrayCopy());
    }

    /**
     * If any external payment mean chooses by customer
     * Forward to payment page after order submitting
     */
    public function paymentAction()
    {
        if(empty($this->session['sOrderVariables'])
                || $this->getMinimumCharge()
                || $this->getEsdNote()
                || $this->getDispatchNoOrder()) {
            return $this->forward('confirm');
        }

        if ($this->Request()->getParam('sNewsletter')!==null) {
            $this->session['sNewsletter'] = $this->Request()->getParam('sNewsletter') ? true : false;
        }
        if ($this->Request()->getParam('sComment')!==null) {
            $this->session['sComment'] = trim(strip_tags($this->Request()->getParam('sComment')));
        }

        if (!Shopware()->Config()->get('IgnoreAGB') && !$this->Request()->getParam('sAGB')) {
            $this->View()->sAGBError = true;
            return $this->forward('confirm');
        }

        $this->View()->assign($this->session['sOrderVariables']->getArrayCopy());
        $this->View()->sAGBError = false;

        if(empty($this->View()->sPayment['embediframe'])
                && empty($this->View()->sPayment['action'])) {
            return $this->forward('confirm');
        }

        if (!empty($this->session['sNewsletter'])) {
            $this->admin->sUpdateNewsletter(true, $this->admin->sGetUserMailById(), true);
        }

        if (!empty($this->View()->sPayment['embediframe'])) {
            $embedded = $this->View()->sPayment['embediframe'];
            $embedded = preg_replace('#^[./]+#', '', $embedded);
            $embedded .= '?sCoreId='.Shopware()->SessionID();
            $embedded .= '&sAGB=1';

            $this->View()->sEmbedded = $embedded;
        } else {
            $action = explode('/', $this->View()->sPayment['action']);
            $this->redirect(array(
                    'controller' => $action[0],
                    'action' => empty($action[1]) ? 'index' : $action[1],
                    'forceSecure' => true
                ));
        }
    }

    /**
     * Add an article to cart directly from cart / confirm view
     * @param sAdd = ordernumber
     * @param sQuantity = quantity
     */
    public function addArticleAction()
    {
        $ordernumber = $this->Request()->getParam('sAdd');
        $quantity = $this->Request()->getParam('sQuantity');
        $articleID = Shopware()->Modules()->Articles()->sGetArticleIdByOrderNumber($ordernumber);

        $this->View()->sBasketInfo = $this->getInstockInfo($ordernumber, $quantity);

        if (!empty($articleID)) {
            $insertID = $this->basket->sAddArticle($ordernumber, $quantity);
            $this->View()->sArticleName = Shopware()->Modules()->Articles()->sGetArticleNameByOrderNumber($ordernumber);
            if (!empty($insertID)) {
                $basket = $this->getBasket();
                foreach ($basket['content'] as $item) {
                    if ($item['id']==$insertID) {
                        $this->View()->sArticle = $item;
                        break;
                    }
                }
            }

            if (Shopware()->Config()->get('similarViewedShow', true)) {
                $this->View()->sCrossSimilarShown = $this->getSimilarShown($articleID);
            }

            if (Shopware()->Config()->get('alsoBoughtShow', true)) {
                $this->View()->sCrossBoughtToo = $this->getBoughtToo($articleID);
            }
        }

        if ($this->request->isXmlHttpRequest()||!empty($this->Request()->callback)) {
            $this->Request()->setParam('sTargetAction', 'ajax_add_article');
        }


        if ($this->Request()->getParam('sAddAccessories')) {
            $this->forward('addAccessories');
        } else {
            $this->forward($this->Request()->getParam('sTargetAction', 'index'));
        }
    }

    /**
     * Add more then one article directly from cart / confirm view
     * @param sAddAccessories = List of article ordernumbers separated by ;
     * @param sAddAccessoriesQuantity = List of article quantities separated by ;
     */
    public function addAccessoriesAction()
    {
        $accessories = $this->Request()->getParam('sAddAccessories');
        $accessoriesQuantity = $this->Request()->getParam('sAddAccessoriesQuantity');
        if (is_string($accessories)) {
            $accessories = explode(';', $accessories);
        }

        if (!empty($accessories)&&is_array($accessories)) {
            foreach ($accessories as $key => $accessory) {
                try {
                    if (!empty($accessoriesQuantity[$key])) {
                        $quantity = intval($accessoriesQuantity[$key]);
                    } else {
                        $quantity = 1;
                    }
                    $this->basket->sAddArticle($accessory, $quantity);
                } catch (Exception $e) {

                }
            }
        }

        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }

    /**
     * Delete an article from cart -
     * @param sDelete = id from s_basket identifying the product to delete
     * Forward to cart / confirmation page after success
     */
    public function deleteArticleAction()
    {
        if ($this->Request()->getParam('sDelete')) {
            $this->basket->sDeleteArticle($this->Request()->getParam('sDelete'));
        }
        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }

    /**
     * Change quantity of a certain product
     * @param sArticle = The article to update
     * @param sQuantity = new quantity
     * Forward to cart / confirm view after success
     */
    public function changeQuantityAction()
    {
        if ($this->Request()->getParam('sArticle') && $this->Request()->getParam('sQuantity')) {
            $this->View()->sBasketInfo = $this->basket->sUpdateArticle($this->Request()->getParam('sArticle'), $this->Request()->getParam('sQuantity'));
        }
        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }

    /**
     * Add voucher to cart
     *
     * At failure view variable sVoucherError will give further information
     * At success return to cart / confirm view
     */
    public function addVoucherAction()
    {
        if ($this->Request()->isPost()) {
            $voucher = $this->basket->sAddVoucher($this->Request()->getParam('sVoucher'));
            if (!empty($voucher['sErrorMessages'])) {
                $this->View()->sVoucherError = $voucher['sErrorMessages'];
            }
        }
        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }

    /**
     * Add premium / bonus article to cart
     * @param sAddPremium - ordernumber of bonus article (defined in s_articles_premiums)
     * Return to cart / confirm page on success
     */
    public function addPremiumAction()
    {
        if ($this->Request()->isPost()) {
            if (!$this->Request()->getParam('sAddPremium')) {
                $this->View()->sBasketInfo = Shopware()->Snippets()->getSnippet()->get(
                    'CheckoutSelectPremiumVariant',
                    'Please select an option to place the required premium to the cart',
                    true
                );
            } else {
                $this->basket->sSYSTEM->_GET['sAddPremium'] = $this->Request()->getParam('sAddPremium');
                $this->basket->sInsertPremium();
            }
        }
        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }

    /**
     * On any change on country, payment or dispatch recalculate shipping costs
     * and forward to cart / confirm view
     */
    public function calculateShippingCostsAction()
    {
        if ($this->Request()->getPost('sCountry')) {
            $this->session['sCountry'] = (int) $this->Request()->getPost('sCountry');
            $this->session["sState"] = 0;
            $this->session["sArea"] = Shopware()->Db()->fetchOne("
            SELECT areaID FROM s_core_countries WHERE id = ?
            ",array($this->session['sCountry']));
        }

        if ($this->Request()->getPost('sPayment')) {
            $this->session['sPaymentID'] = (int) $this->Request()->getPost('sPayment');
        }

        if ($this->Request()->getPost('sDispatch')) {
            $this->session['sDispatch'] = (int) $this->Request()->getPost('sDispatch');
        }

        if ($this->Request()->getPost('sState')) {
            $this->session['sState'] = (int) $this->Request()->getPost('sState');
        }

        $this->forward($this->Request()->getParam('sTargetAction', 'index'));
    }

    /**
     * Used only for new customers
     * Action to handle selection of default shipping and payment methods
     */
    public function shippingPaymentAction()
    {
        // This action is only available for new customers
        // redirect if we come from an existing account
        if (empty($this->session['sRegisterFinished'])) {
            $this->redirect(array('action' => 'index'));
        }

        $this->View()->sPayment = $this->getSelectedPayment();
        $this->View()->sUserData["payment"] = $this->View()->sPayment;

        $this->View()->sBasket = $this->getBasket();

        $this->View()->sDispatch = $this->getSelectedDispatch();
        $this->View()->sPayments = $this->getPayments();
        $this->View()->sDispatches = $this->getDispatches();

        $this->View()->sLaststock = $this->basket->sCheckBasketQuantities();
        $this->View()->sShippingcosts = $this->View()->sBasket['sShippingcosts'];
        $this->View()->sShippingcostsDifference = $this->View()->sBasket['sShippingcostsDifference'];
        $this->View()->sAmount = $this->View()->sBasket['sAmount'];
        $this->View()->sAmountWithTax = $this->View()->sBasket['sAmountWithTax'];
        $this->View()->sAmountTax = $this->View()->sBasket['sAmountTax'];
        $this->View()->sAmountNet = $this->View()->sBasket['AmountNetNumeric'];
        $this->View()->sRegisterFinished = !empty($this->session['sRegisterFinished']);

        $this->View()->sTargetAction = 'shippingPayment';
    }

    /**
     * Get complete user-data as an array to use in view
     *
     * @return array
     */
    public function getUserData()
    {
        $system = Shopware()->System();
        $userData = $this->admin->sGetUserData();
        if (!empty($userData['additional']['countryShipping'])) {
            $sTaxFree = false;
            if (!empty($userData['additional']['countryShipping']['taxfree'])) {
                $sTaxFree = true;
            } elseif (
                !empty($userData['additional']['countryShipping']['taxfree_ustid'])
                && !empty($userData['billingaddress']['ustid'])
                && $userData['additional']['country']['id'] == $userData['additional']['countryShipping']['id']
            ) {
                $sTaxFree = true;
            }

            $system->sUSERGROUPDATA = Shopware()->Db()->fetchRow("
                SELECT * FROM s_core_customergroups
                WHERE groupkey = ?
            ", array($system->sUSERGROUP));

            if (!empty($sTaxFree)) {
                $system->sUSERGROUPDATA['tax'] = 0;
                $system->sCONFIG['sARTICLESOUTPUTNETTO'] = 1; //Old template
                Shopware()->Session()->sUserGroupData = $system->sUSERGROUPDATA;
                $userData['additional']['charge_vat'] = false;
                $userData['additional']['show_net'] = false;
                Shopware()->Session()->sOutputNet = true;
            } else {
                $userData['additional']['charge_vat'] = true;
                $userData['additional']['show_net'] = !empty($system->sUSERGROUPDATA['tax']);
                Shopware()->Session()->sOutputNet = empty($system->sUSERGROUPDATA['tax']);
            }
        }

        return $userData;
    }

    /**
     * Create temporary order in s_order_basket on confirm page
     * Used to track failed / aborted orders
     */
    public function saveTemporaryOrder()
    {
        $order = Shopware()->Modules()->Order();

        $order->sUserData = $this->View()->sUserData;
        $order->sComment = isset($this->session['sComment']) ? $this->session['sComment'] : '';
        $order->sBasketData = $this->View()->sBasket;
        $order->sAmount = $this->View()->sBasket['sAmount'];
        $order->sAmountWithTax = !empty($this->View()->sBasket['AmountWithTaxNumeric']) ? $this->View()->sBasket['AmountWithTaxNumeric'] : $this->View()->sBasket['AmountNumeric'];
        $order->sAmountNet = $this->View()->sBasket['AmountNetNumeric'];
        $order->sShippingcosts = $this->View()->sBasket['sShippingcosts'];
        $order->sShippingcostsNumeric = $this->View()->sBasket['sShippingcostsWithTax'];
        $order->sShippingcostsNumericNet = $this->View()->sBasket['sShippingcostsNet'];
        $order->bookingId = Shopware()->System()->_POST['sBooking'];
        $order->dispatchId = $this->session['sDispatch'];
        $order->sNet = !$this->View()->sUserData['additional']['charge_vat'];

        $order->sDeleteTemporaryOrder();	// Delete previous temporary orders
        $order->sCreateTemporaryOrder();	// Create new temporary order
    }

    /**
     * Finish order - set some object properties to do this
     */
    public function saveOrder()
    {
        $order = Shopware()->Modules()->Order();

        $order->sUserData = $this->View()->sUserData;
        $order->sComment = isset($this->session['sComment']) ? $this->session['sComment'] : '';
        $order->sBasketData = $this->View()->sBasket;
        $order->sAmount = $this->View()->sBasket['sAmount'];
        $order->sAmountWithTax = !empty($this->View()->sBasket['AmountWithTaxNumeric']) ? $this->View()->sBasket['AmountWithTaxNumeric'] : $this->View()->sBasket['AmountNumeric'];
        $order->sAmountNet = $this->View()->sBasket['AmountNetNumeric'];
        $order->sShippingcosts = $this->View()->sBasket['sShippingcosts'];
        $order->sShippingcostsNumeric = $this->View()->sBasket['sShippingcostsWithTax'];
        $order->sShippingcostsNumericNet = $this->View()->sBasket['sShippingcostsNet'];
        $order->bookingId = Shopware()->System()->_POST['sBooking'];
        $order->dispatchId = $this->session['sDispatch'];
        $order->sNet = !$this->View()->sUserData['additional']['charge_vat'];

        return $order->sSaveOrder();
    }

    /**
     * Used in ajax add cart action
     * Check availability of product and return info / error - messages
     *
     * @param unknown_type $ordernumber article order number
     * @param unknown_type $quantity quantity
     * @return unknown
     */
    public function getInstockInfo($ordernumber, $quantity)
    {
        if (empty($ordernumber)) {
            return Shopware()->Snippets()->getNamespace("frontend")->get('CheckoutSelectVariant', 'Please select an option to place the required product in the cart', true);
        }

        $quantity = max(1, (int) $quantity);
        $instock = $this->getAvailableStock($ordernumber);
        $instock['quantity'] += $quantity;

        if (empty($instock['articleID'])) {
            return  Shopware()->Snippets()->getNamespace("frontend")->get('CheckoutArticleNotFound', 'Product could not be found.', true);
        }
        if (!empty($instock['laststock'])||!empty(Shopware()->Config()->InstockInfo)) {
            if ($instock['instock']<=0&&!empty($instock['laststock'])) {
                return Shopware()->Snippets()->getNamespace("frontend")->get('CheckoutArticleNoStock', 'Unfortunately we can not deliver the desired product in sufficient quantity', true);
            } elseif ($instock['instock']<$instock['quantity']) {
                $result = 'Unfortunately we can not deliver the desired product in sufficient quantity. (#0 von #1 in stock).';
                $result = Shopware()->Snippets()->getNamespace("frontend")->get('CheckoutArticleLessStock', $result, true);
                return str_replace(array('#0', '#1'), array($instock['instock'], $instock['quantity']), $result);
            }
        }
        return null;
    }

    /**
     * Get current stock from a certain product defined by $ordernumber
     * Support for multidimensional variants
     *
     * @param unknown_type $ordernumber
     * @return array with article id / current basket quantity / instock / laststock
     */
    public function getAvailableStock($ordernumber)
    {
        $sql = '
            SELECT
                a.id as articleID,
                ob.quantity,
                IF(ad.instock < 0, 0, ad.instock) as instock,
                a.laststock,
                ad.ordernumber as ordernumber
            FROM s_articles a
            LEFT JOIN s_articles_details ad
            ON ad.ordernumber=?
            LEFT JOIN s_order_basket ob
            ON ob.sessionID=?
            AND ob.ordernumber=ad.ordernumber
            AND ob.modus=0
            WHERE a.id=ad.articleID
        ';
        $row = Shopware()->Db()->fetchRow($sql, array(
                $ordernumber,
                Shopware()->SessionID(),
            ));
        return $row;
    }

    /**
     * Get shipping costs as an array (brutto / netto) depending on selected country / payment
     *
     * @return array
     */
    public function getShippingCosts()
    {
        $country = $this->getSelectedCountry();
        $payment = $this->getSelectedPayment();
        if (empty($country) || empty($payment)) {
            return array('brutto'=>0, 'netto'=>0);
        }
        $shippingcosts = $this->admin->sGetPremiumShippingcosts($country);
        return empty($shippingcosts) ? array('brutto'=>0, 'netto'=>0) : $shippingcosts;
    }

    /**
     * Return complete basket data to view
     * Basket items / Shippingcosts / Amounts / Tax-Rates
     *
     * @return array
     */
    public function getBasket()
    {
        $basket = $this->basket->sGetBasket();

        $shippingcosts = $this->getShippingCosts();

        $basket = $this->basket->sGetBasket();

        $basket['sShippingcostsWithTax'] = $shippingcosts['brutto'];
        $basket['sShippingcostsNet'] = $shippingcosts['netto'];
        $basket['sShippingcostsTax'] = $shippingcosts['tax'];

        if (!empty($shippingcosts['brutto'])) {
            $basket['AmountNetNumeric'] += $shippingcosts['netto'];
            $basket['AmountNumeric'] += $shippingcosts['brutto'];
            $basket['sShippingcostsDifference'] = $shippingcosts['difference']['float'];
        }
        if (!empty($basket['AmountWithTaxNumeric'])) {
            $basket['AmountWithTaxNumeric'] += $shippingcosts['brutto'];
        }
        if ((!Shopware()->System()->sUSERGROUPDATA['tax'] && Shopware()->System()->sUSERGROUPDATA['id'])) {
            $basket['sTaxRates'] = $this->getTaxRates($basket);

            $basket['sShippingcosts'] = $shippingcosts['netto'];
            $basket['sAmount'] = round($basket['AmountNetNumeric'], 2);
            $basket['sAmountTax'] = round($basket['AmountWithTaxNumeric'] - $basket['AmountNetNumeric'], 2);
            $basket['sAmountWithTax'] = round($basket['AmountWithTaxNumeric'], 2);

        } else {
            $basket['sTaxRates'] = $this->getTaxRates($basket);

            $basket['sShippingcosts'] = $shippingcosts['brutto'];
            $basket['sAmount'] = $basket['AmountNumeric'];

            $basket['sAmountTax'] = round($basket['AmountNumeric'] - $basket['AmountNetNumeric'], 2);
        }
        return $basket;
    }

    /**
     * Returns tax rates for all basket positions
     *
     * @param unknown_type $basket array returned from this->getBasket
     * @return array
     */
    public function getTaxRates($basket)
    {
        $result = array();

        if (!empty($basket['sShippingcostsTax'])) {
            $basket['sShippingcostsTax'] = number_format(floatval($basket['sShippingcostsTax']),2);

            $result[$basket['sShippingcostsTax']] = $basket['sShippingcostsWithTax']-$basket['sShippingcostsNet'];
            if (empty($result[$basket['sShippingcostsTax']])) unset($result[$basket['sShippingcostsTax']]);
        } elseif ($basket['sShippingcostsWithTax']) {
            $result[number_format(floatval(Shopware()->Config()->get('sTAXSHIPPING')),2)] = $basket['sShippingcostsWithTax']-$basket['sShippingcostsNet'];
            if (empty($result[number_format(floatval(Shopware()->Config()->get('sTAXSHIPPING')),2)])) unset($result[number_format(floatval(Shopware()->Config()->get('sTAXSHIPPING')),2)]);
        }


        if (empty($basket['content'])) {
            ksort($result, SORT_NUMERIC);
            return $result;
        }

        foreach ($basket['content'] as $item) {

            if (!empty($item["tax_rate"])) {

            } elseif (!empty($item['taxPercent'])) {
                $item['tax_rate'] = $item["taxPercent"];
            } elseif ($item['modus'] == 2) {
                // Ticket 4842 - dynamic tax-rates
                $resultVoucherTaxMode = Shopware()->Db()->fetchOne(
                    "SELECT taxconfig FROM s_emarketing_vouchers WHERE ordercode=?
                ", array($item["ordernumber"]));
                // Old behaviour
                if (empty($resultVoucherTaxMode) || $resultVoucherTaxMode == "default") {
                    $tax = Shopware()->Config()->get('sVOUCHERTAX');
                } elseif ($resultVoucherTaxMode == "auto") {
                    // Automatically determinate tax
                    $tax = $this->basket->getMaxTax();
                } elseif ($resultVoucherTaxMode == "none") {
                    // No tax
                    $tax = "0";
                } elseif (intval($resultVoucherTaxMode)) {
                    // Fix defined tax
                    $tax = Shopware()->Db()->fetchOne("
                    SELECT tax FROM s_core_tax WHERE id = ?
                    ", array($resultVoucherTaxMode));
                }
                $item['tax_rate'] = $tax;
            } else {
                // Ticket 4842 - dynamic tax-rates
                $taxAutoMode = Shopware()->Config()->get('sTAXAUTOMODE');
                if (!empty($taxAutoMode)) {
                    $tax = $this->basket->getMaxTax();
                } else {
                    $tax = Shopware()->Config()->get('sDISCOUNTTAX');
                }
                $item['tax_rate'] = $tax;
            }

            if (empty($item['tax_rate']) || empty($item["tax"])) continue; // Ignore 0 % tax

            $taxKey = number_format(floatval($item['tax_rate']), 2);

            $result[$taxKey] += str_replace(',', '.', $item['tax']);
        }

        ksort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * Get similar shown products to display in ajax add dialog
     *
     * @param int $articleID
     * @return array
     */
    public function getSimilarShown($articleID)
    {
        Shopware()->Modules()->Marketing()->sBlacklist = $this->basket->sGetBasketIds();

        $similarId = Shopware()->Modules()->Marketing()->sGetSimilaryShownArticles($articleID);

        $similars = array();
        if(!empty($similarId))
            foreach ($similarId as $similarID) {
                $temp = Shopware()->Modules()->Articles()->sGetPromotionById('fix', 0, (int) $similarID['id']);
                if (!empty($temp)) {
                    $similars[] = $temp;
                }
            }
        return $similars;
    }

    /**
     * Get articles that bought in combination with last added product to
     * display on cart page
     *
     * @param int $articleID
     * @return array
     */
    public function getBoughtToo($articleID)
    {
        Shopware()->Modules()->Marketing()->sBlacklist = $this->basket->sGetBasketIds();

        $alsoBoughtId = Shopware()->Modules()->Marketing()->sGetAlsoBoughtArticles($articleID);
        $alsoBoughts = array();
        if(!empty($alsoBoughtId))
            foreach ($alsoBoughtId as $alsoBoughtItem) {
                $temp = Shopware()->Modules()->Articles()->sGetPromotionById('fix',0,(int) $alsoBoughtItem['id']);
                if (!empty($temp)) {
                    $alsoBoughts[] = $temp;
                }
            }
        return $alsoBoughts;
    }

    /**
     * Get configured minimum charge to check in order processing
     *
     * @return bool
     */
    public function getMinimumCharge()
    {
        return $this->basket->sCheckMinimumCharge();
    }

    /**
     * Check if order is possible under current conditions (dispatch)
     *
     * @return bool
     */
    public function getDispatchNoOrder()
    {
        return !empty(Shopware()->Config()->PremiumShippingNoOrder) && (empty($this->session['sDispatch']) || empty($this->session['sCountry']));
    }

    /**
     * Get all premium products that are configured and available for this order
     *
     * @return array
     */
    public function getPremiums()
    {
        $sql = 'SELECT `id` FROM `s_order_basket` WHERE `sessionID`=? AND `modus`=1';
        $result = Shopware()->Db()->fetchOne($sql, array(Shopware()->SessionID()));
        if(!empty($result)) return array();
        return Shopware()->Modules()->Marketing()->sGetPremiums();
    }

    /**
     * Check if any electronically distribution product is in basket
     *
     * @return boolean
     */
    public function getEsdNote()
    {
        $payment = empty($this->View()->sUserData['additional']['payment']) ? $this->session['sOrderVariables']['sUserData']['additional']['payment'] : $this->View()->sUserData['additional']['payment'];
        return $this->basket->sCheckForESD() && !$payment['esdactive'];
    }

    /**
     * Check if a custom inquiry possibility should displayed on cart page
     * Compare configured inquirevalue with current amount
     *
     * @return boolean
     */
    public function getInquiry()
    {
        if (Shopware()->Config()->get('sINQUIRYVALUE')) {
            $factor = Shopware()->System()->sCurrency['factor'] ? 1 : Shopware()->System()->sCurrency['factor'];
            $value = Shopware()->Config()->get('sINQUIRYVALUE')*$factor;
            if ((!Shopware()->System()->sUSERGROUPDATA['tax'] && Shopware()->System()->sUSERGROUPDATA['id'])) {
                $amount = $this->View()->sBasket['AmountWithTaxNumeric'];
            } else {
                $amount = $this->View()->sBasket['AmountNumeric'];
            }
            if (!empty($amount) && $amount >= $value) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get link to inquiry form if getInquiry returend true
     *
     * @return string
     */
    public function getInquiryLink()
    {
        return Shopware()->Config()->get('sBASEFILE').'?sViewport=support&sFid='.Shopware()->Config()->get('sINQUIRYID').'&sInquiry=basket';
    }

    /**
     * Get all countries from database via sAdmin object
     *
     * @return array list of countries
     */
    public function getCountryList()
    {
        return $this->admin->sGetCountryList();
    }

    /**
     * Get all dispatches available in selected country from sAdmin object
     *
     * @return array list of dispatches
     */
    public function getDispatches()
    {
        $country = $this->getSelectedCountry();
        $state = $this->getSelectedState();
        if (empty($country)) {
            return false;
        }
        $stateId = !empty($state['id']) ? $state['id'] : null;
        return $this->admin->sGetPremiumDispatches($country['id'], null, $stateId);
    }

    /**
     * Returns all available payment methods from sAdmin object
     *
     * @return array list of payment methods
     */
    public function getPayments()
    {
        return $this->admin->sGetPaymentMeans();
    }

    /**
     * Get current selected country - if no country is selected, choose first one from list
     * of available countries
     *
     * @return array with country information
     */
    public function getSelectedCountry()
    {
        if (!empty($this->View()->sUserData['additional']['countryShipping'])) {
            $this->session['sCountry'] = (int) $this->View()->sUserData['additional']['countryShipping']['id'];
            $this->session['sArea'] = (int) $this->View()->sUserData['additional']['countryShipping']['areaID'];

            return $this->View()->sUserData['additional']['countryShipping'];
        }
        $countries = $this->getCountryList();
        if (empty($countries)) {
            unset($this->session['sCountry']);
            return false;
        }
        $country = reset($countries);
        $this->session['sCountry'] = (int) $country['id'];
        $this->session['sArea'] = (int) $country['areaID'];
        $this->View()->sUserData['additional']['countryShipping'] = $country;
        return $country;
    }

    /**
     * Get current selected country - if no country is selected, choose first one from list
     * of available countries
     *
     * @return array with country information
     */
    public function getSelectedState()
    {
        if (!empty($this->View()->sUserData['additional']['stateShipping'])) {
            $this->session['sState'] = (int) $this->View()->sUserData['additional']['stateShipping']['id'];
            return $this->View()->sUserData['additional']['stateShipping'];
        }
        return array("id" => $this->session['sState']);
    }

    /**
     * Get selected payment or do payment mean selection automatically
     *
     * @return array
     */
    public function getSelectedPayment()
    {
        if (!empty($this->View()->sUserData['additional']['payment'])) {
            $payment = $this->View()->sUserData['additional']['payment'];
        } elseif (!empty($this->session['sPaymentID'])) {
            $payment = $this->admin->sGetPaymentMeanById($this->session['sPaymentID'], $this->View()->sUserData);
        }

        $paymentClass = $this->admin->sInitiatePaymentClass($payment);
        if ($payment && $paymentClass instanceof \ShopwarePlugin\PaymentMethods\Components\BasePaymentMethod) {
            $data = $paymentClass->getCurrentPaymentDataAsArray(Shopware()->Session()->sUserId);
            if (!empty($data)) {
                $payment['data'] = $data;
            }
        }

        if (!empty($payment)) {
            return $payment;
        }
        $payments = $this->getPayments();
        if (empty($payments)) {
            unset($this->session['sPaymentID']);
            return false;
        }
        $payment = reset($payments);
        $this->session['sPaymentID'] = (int) $payment['id'];
        return $payment;
    }

    /**
     * Get selected dispatch or select a default dispatch
     *
     * @return boolean|array
     */
    public function getSelectedDispatch()
    {
        if (empty($this->session['sCountry'])) {
            return false;
        }

        $dispatches = $this->admin->sGetPremiumDispatches($this->session['sCountry'], null, $this->session['sState']);
        if (empty($dispatches)) {
            unset($this->session['sDispatch']);
            return false;
        }

        foreach ($dispatches as $dispatch) {
            if ($dispatch['id'] == $this->session['sDispatch']) {
                return $dispatch;
            }
        }
        $dispatch = reset($dispatches);
        $this->session['sDispatch'] = (int) $dispatch['id'];
        return $dispatch;
    }

    /**
     * Ajax add article action
     *
     * Loads the ajax padding plugin.
     */
    public function ajaxAddArticleAction()
    {
        Enlight()->Plugins()->Controller()->Json()->setPadding();
    }

    /**
     * Ajax cart action
     *
     * Loads the cart in order to send via ajax.
     */
    public function ajaxCartAction()
    {
        Enlight()->Plugins()->Controller()->Json()->setPadding();

        //$this->View()->sUserData = $this->getUserData();
        $this->View()->sBasket = $this->getBasket();

        $this->View()->sShippingcosts = $this->View()->sBasket['sShippingcosts'];
        $this->View()->sShippingcostsDifference = $this->View()->sBasket['sShippingcostsDifference'];
        $this->View()->sAmount = $this->View()->sBasket['sAmount'];
        $this->View()->sAmountWithTax = $this->View()->sBasket['sAmountWithTax'];
        $this->View()->sAmountTax = $this->View()->sBasket['sAmountTax'];
        $this->View()->sAmountNet = $this->View()->sBasket['AmountNetNumeric'];
    }

    /**
     * Get current amount from cart via ajax to display in realtime
     */
    public function ajaxAmountAction()
    {
        Enlight()->Plugins()->Controller()->Json()->setPadding();

        $this->View()->sBasketQuantity = $this->basket->sCountBasket();
        $amount = $this->basket->sGetAmount();
        $this->View()->sBasketAmount = empty($amount) ? 0 : array_shift($amount);
    }
}
