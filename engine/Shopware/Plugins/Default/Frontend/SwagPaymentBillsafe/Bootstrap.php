<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Plugins
 * @subpackage Plugin
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Billsafe Plugin
 *
 * todo@all: Documentation
 */
class Shopware_Plugins_Frontend_SwagPaymentBillsafe_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Installs the plugin
     *
     * Creates and subscribe the events and hooks
     * Creates and save the payment row
     * Creates the payment table
     * Creates payment menu item
     *
     * @return bool
     */
    public function install()
    {
        $this->createEvents();
        $this->createPaymentRow();
        $this->createTable();
        $this->createMenu();
        $this->createForm();
        return true;
    }

    /**
     * Fetches and returns billsafe payment row instance.
     *
     * @return Shopware\Models\Payment\Payment
     */
    public function Payment()
    {
        return $this->Payments()->findOneBy(array(
            'name' => 'billsafe_invoice'
        ));
    }

    /**
     * Activate the plugin billsafe plugin.
     *
     * Sets the active flag in the payment row.
     *
     * @return bool
     */
    public function enable()
    {
        $payment = $this->Payment();
        $payment->setActive(true);
        if (!empty($this->Config()->checkoutLogoId)) {
            $payment->setAdditionalDescription(preg_replace(
                '#<!--BillSAFE start-->.+?<!--BillSAFE end-->#',
                '<!--BillSAFE start--><img src="https://images.billsafe.de/image/image/id/'
                    . $this->Config()->checkoutLogoId
                    . '"/><!--BillSAFE end-->',
                $payment->getAdditionalDescription()
            ));
        }
        return true;
    }

    /**
     * Disable plugin method and sets the active flag in the payment row
     *
     * @return bool
     */
    public function disable()
    {
        $payment = $this->Payment();
        if ($payment) {
            $payment->setActive(false);
        }
        return true;
    }

    /**
     * Creates and subscribe the events and hooks.
     */
    protected function createEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentBillsafe',
            'onGetControllerPathFrontend'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaymentBillsafe',
            'onGetControllerPathBackend'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Backend_Config',
            'onPostDispatchConfig'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatch',
            110
        );

        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_BillsafeClient',
            'onInitResourceBillsafeClient'
        );

        $this->subscribeEvent(
            'Shopware_Components_Document::assignValues::after',
            'onBeforeRenderDocument'
        );
    }

    /**
     * Creates and save the payment row.
     */
    protected function createPaymentRow()
    {
        if ($this->Payment()) {
            return;
        }
        $this->createPayment(array(
            'name' => 'billsafe_invoice',
            'description' => 'BillSAFE',
            'action' => 'payment_billsafe',
            'active' => 0,
            'position' => 0,
            'additionalDescription' => '<!--BillSAFE start--><img src="https://images.billsafe.de/image/image/id/2114233bfb5d"/><!--BillSAFE end-->'
                . '<br/><br/>'
                . 'Bezahlen Sie bequem per Rechnung.'
        ));
    }

    /**
     * Creates the payment table.
     * Adds a few default settings to payment.
     */
    protected function createTable()
    {
        $payment = $this->Payment();

        $sql = 'DELETE FROM `s_core_rulesets` WHERE `paymentID`=?';
        Shopware()->Db()->query($sql, array($payment->getId()));
        
        $sql = "
			INSERT INTO `s_core_rulesets` (`paymentID`, `rule1`, `value1`, `rule2`, `value2`) VALUES
			({$payment->getId()}, 'CURRENCIESISOISNOT', 'EUR', '0', ''),
			-- ({$payment->getId()} 'LANDISNOT', 'DE', '0', ''),
			-- ({$payment->getId()}, 'DIFFER', '', '0', ''),
			({$payment->getId()}, 'ORDERVALUELESS', '-0.01', '0', '');
		";
        Shopware()->Db()->exec($sql);

        $sql = 'INSERT IGNORE INTO `s_core_paymentmeans_countries` (`paymentID`, `countryID`) VALUES (?, ?);';
        Shopware()->Db()->query($sql, array($payment->getId(), 2));

        $sql = 'DELETE FROM `s_core_documents_box` WHERE `name` LIKE ?';
        Shopware()->Db()->query($sql, array('Billsafe_%'));

        $sql = "
			INSERT INTO `s_core_documents_box` (`documentID`, `name`, `style`, `value`) VALUES
			(1, 'Billsafe_Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', ?),
			(1, 'Billsafe_Content_Info', ?, ?);
		";
        Shopware()->Db()->query($sql, array(
            '<table style="height: 90px;" border="0" width="100%">'
                . '<tbody>'
                . '<tr valign="top">'
                . '<td style="width: 33%;">'
                . '<p><span style="font-size: xx-small;">Demo GmbH</span></p>'
                . '<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>'
                . '</td>'
                . '<td style="width: 33%;">'
                . '<p><span style="font-size: xx-small;">AGB<br /></span></p>'
                . '<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt</span></p>'
                . '</td>'
                . '<td style="width: 33%;">'
                . '<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>'
                . '<p><span style="font-size: xx-small;">Max Mustermann</span></p>'
                . '</td>'
                . '</tr>'
                . '</tbody>'
                . '</table>',
            '.payment_instruction, .payment_instruction td, .payment_instruction tr {'
                . '	margin: 0;'
                . '	padding: 0;'
                . '	border: 0;'
                . '	font-size:8px;'
                . '	font: inherit;'
                . '	vertical-align: baseline;'
                . '}'
                . '.payment_note {'
                . '	font-size: 10px;'
                . '	color: #333;'
                . '}',
            '<div class="payment_note">'
                . '<br/>'
                . '{$instruction.legalNote}<br/>'
                . '{$instruction.note}<br/><br/>'
                . '</div>'
                . '<table class="payment_instruction">'
                . '<tr>'
                . '	<td>Empfänger:</td>'
                . '	<td>{$instruction.recipient}</td>'
                . '</tr>'
                . '<tr>'
                . '	<td>Kontonr.:</td>'
                . '	<td>{$instruction.accountNumber}</td>'
                . '</tr>'
                . '<tr>'
                . '	<td>BLZ:</td>'
                . '	<td>{$instruction.bankCode}</td>'
                . '</tr>'
                . '<tr>'
                . '	<td>Bank:</td>'
                . '	<td>{$instruction.bankName}</td>'
                . '</tr>'
                . '<tr>'
                . '	<td>Betrag:</td>'
                . '	<td>{$instruction.amount|currency}</td>'
                . '</tr>'
                . '<tr>'
                . '	<td>Verwendungszweck 1:</td>'
                . '	<td>{$instruction.reference}</td>'
                . '</tr>'
                . '<tr>'
                . '	<td>Verwendungszweck 2:</td>'
                . '	<td>{config name=host}</td>'
                . '</tr>'
                . '</table>'
        ));
    }

    /**
     * Creates and stores a payment item.
     */
    protected function createMenu()
    {
        $parent = $this->Menu()->findOneBy('label', 'Zahlungen');
        $this->createMenuItem(array(
            'label' => 'BillSAFE',
            'onclick' => 'openAction(\'payment_billsafe\');',
            'class' => 'ico2 date2',
            'active' => 1,
            'parent' => $parent,
            'style' => 'background-position: 5px 5px;'
        ));
    }

    /**
     * Creates and stores the payment config form.
     */
    protected function createForm()
    {
        $form = $this->Form();
        $form->setElement('text', 'merchantId', array(
            'label' => 'Verkäufer-ID',
            'required' => true
        ));
        $form->setElement('text', 'merchantLicense', array(
            'label' => 'Verkäufer-Lizenzschlüssel',
            'required' => true
        ));
        $form->setElement('text', 'logo', array(
            'label' => 'Shop-Logo für das Payment-Gateway',
            'value' => 'frontend/_resources/images/logo.jpg',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'logoBlock', array(
            'label' => 'Template-Block für das Payment-Logo',
            'value' => 'frontend_index_left_campaigns_bottom',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'logoId', array(
            'label' => 'Logo-ID des Payment-Logos',
            'value' => '03142335e8c4',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('combo', 'paymentStatusId', array(
            'label' => 'Zahlstatus nach der Bestellung',
            'value' => 18,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('checkbox', 'paymentStatusMail', array(
            'label' => 'eMail bei Zahlstatus-Änderung verschicken',
            'value' => false,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('checkbox', 'layer', array(
            'label' => 'Layered-Payment-Gateway aktivieren',
            'value' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('checkbox', 'prevalidate', array(
            'label' => 'Vorvalidierung aktivieren',
            'value' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('checkbox', 'debug', array(
            'label' => 'Sandbox-Modus aktivieren',
            'value' => false
        ));
    }

    /**
     * Uninstalled the plugin.
     *
     * Deletes the payment row.
     *
     * @return bool
     */
    public function uninstall()
    {
        if ($payment = $this->Payment()) {
            $sql = 'DELETE FROM `s_core_rulesets` WHERE `paymentID`=?';
            Shopware()->Db()->query($sql, array($payment->getId()));
            $sql = 'DELETE FROM `s_core_paymentmeans_countries` WHERE `paymentID`=?';
            Shopware()->Db()->query($sql, array($payment->getId()));
            $sql = 'DELETE FROM `s_core_paymentmeans_subshops` WHERE `paymentID`=?';
            Shopware()->Db()->query($sql, array($payment->getId()));
        }
        $sql = 'DELETE FROM `s_core_documents_box` WHERE `name` LIKE ?';
        Shopware()->Db()->query($sql, array('Billsafe_%'));
        return parent::uninstall();
    }

    /**
     * Returns the path to a frontend controller for an event.
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public static function onGetControllerPathFrontend(Enlight_Event_EventArgs $args)
    {
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');
        return dirname(__FILE__) . '/Controllers/Frontend/PaymentBillsafe.php';
    }

    /**
     * Returns the path to a backend controller for an event.
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public static function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
    {
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');
        return dirname(__FILE__) . '/Controllers/Backend/PaymentBillsafe.php';
    }

    /**
     * Makes some adjustments in the document prior to rendering
     *
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public static function onBeforeRenderDocument(Enlight_Hook_HookArgs $args)
    {
        $document = $args->getSubject();

        if ($document->_order->payment['name'] != 'billsafe_invoice') {
            return;
        }

        $view = $document->_view;
        $client = Shopware()->BillsafeClient();

        $documentData = $view->getTemplateVars('Document');
        $orderData = $view->getTemplateVars('Order');

        $transactionId = $orderData['_order']['transactionID'];
        $invoiceNumber = $documentData['id'];

        if (empty($document->_config['_preview'])) {
            $client->setInvoiceNumber(array(
                'transactionId' => $transactionId,
                'invoiceNumber' => $invoiceNumber
            ));
        }

        $paymentInstruction = $client->getPaymentInstruction(array(
            'transactionId' => $transactionId
        ));
        $document->_template->addTemplateDir(dirname(__FILE__) . '/Views/');
        $document->_template->assign('instruction', (array)$paymentInstruction->instruction);
        //$comment = $document->_template->fetch('frontend/payment_billsafe/instruction.tpl');

        $containerData = $view->getTemplateVars('Containers');
        $containerData['Footer'] = $containerData['Billsafe_Footer'];
        $containerData['Content_Info'] = $containerData['Billsafe_Content_Info'];
        $containerData['Content_Info']['value'] = $document->_template->fetch('string:' . $containerData['Content_Info']['value']);
        $containerData['Content_Info']['style'] = '}' . $containerData['Content_Info']['style'] . ' #info {';
        $view->assign('Containers', $containerData);
    }

    /**
     * Returns the customer parameter as array
     *
     * @return array
     */
    protected static function getCustomerParameter($user)
    {
        $customer = array(
            'id' => $user['billingaddress']['customernumber'],
            'company' => $user['billingaddress']['company'],
            'gender' => $user['billingaddress']['salutation'] == 'ms' ? 'f' : 'm',
            'firstname' => $user['billingaddress']['firstname'],
            'lastname' => $user['billingaddress']['lastname'],
            'street' => $user['billingaddress']['street'],
            'houseNumber' => $user['billingaddress']['streetnumber'],
            'postcode' => $user['billingaddress']['zipcode'],
            'city' => $user['billingaddress']['city'],
            'country' => $user['additional']['country']['countryiso'],
            'email' => $user['additional']['user']['email'],
            'phone' => $user['billingaddress']['phone'],
        );
        if (!empty($user['billingaddress']['birthday']) && $user['billingaddress']['birthday'] != '0000-00-00') {
            $customer['dateOfBirth'] = $user['billingaddress']['birthday'];
        }
        if (!empty($user['billingaddress']['company'])) {
            $customer['company'] = $user['billingaddress']['company'];
        }
        return $customer;
    }

    /**
     * Returns delivery address parameter as array
     *
     * @return array
     */
    protected static function getDeliveryAddressParameter($user)
    {
        $customer = array(
            'id' => $user['shippingaddress']['customernumber'],
            'company' => $user['shippingaddress']['company'],
            'gender' => $user['shippingaddress']['salutation'] == 'ms' ? 'f' : 'm',
            'firstname' => $user['shippingaddress']['firstname'],
            'lastname' => $user['shippingaddress']['lastname'],
            'street' => $user['shippingaddress']['street'],
            'houseNumber' => $user['shippingaddress']['streetnumber'],
            'postcode' => $user['shippingaddress']['zipcode'],
            'city' => $user['shippingaddress']['city'],
            'country' => $user['additional']['countryShipping']['countryiso']
        );
        return $customer;
    }

    /**
     * Adds template directory to template path
     * @param Enlight_Event_EventArgs $arguments
     */
    public function onPostDispatchConfig(Enlight_Event_EventArgs $arguments)
    {
        $view = $arguments->getSubject()->View();

        //if the controller action name equals "load" we have to load all application components.
        if ($arguments->getRequest()->getActionName() === 'load') {
            $view->addTemplateDir($this->Path() . 'Views/');
            $view->extendsTemplate(
                'backend/config/view/form/document_billsafe.js'
            );
        }
    }
    /**
     * Returns the path to a backend controller for an event.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();

        if (!$request->isDispatched()
            || $response->isException()
            || $request->getModuleName() != 'frontend'
        ) {
            return;
        }

        $config = $this->Config();
        if ($view->hasTemplate() && !empty($config->logoBlock) && !empty($config->logoId)) {
            $view->assign('BillsafeConfig', $config);
            $view->addTemplateDir(dirname(__FILE__) . '/Views/');
            $view->extendsBlock($config->logoBlock, '{include file="frontend/payment_billsafe/logo.tpl"}', 'append');
        }

        if (!empty($config->layer)) {
            if ($request->getControllerName() == 'checkout' && $request->getActionName() == 'confirm') {
                $view->assign('BillsafeConfig', $config);
                $view->addTemplateDir(dirname(__FILE__) . '/Views/');
                $view->extendsTemplate('frontend/payment_billsafe/layer.tpl');
            }
        }

        if (($request->getControllerName() == 'account' && $request->getActionName() == 'payment')
            || $request->getControllerName() == 'checkout' && $request->getActionName() == 'confirm'
        ) {
            $view->addTemplateDir(dirname(__FILE__) . '/Views/');
            $view->extendsTemplate('frontend/payment_billsafe/prevalidate.tpl');

            if (!empty($config->prevalidate)) {
                $amount = isset($view->sAmount) ? $view->sAmount : $view->sBasketAmount;
                $view->BillsafeConfig = $this->Config();
                if ($amount > 0) {
                    $view->BillsafePrevalidate = Shopware()->BillsafeClient()->prevalidateOrder(array(
                        'order' => array(
                            'amount' => number_format($amount, 2, '.', ''),
                            'currencyCode' => Shopware()->Currency()->getShortName()
                        ),
                        'customer' => self::getCustomerParameter($view->sUserData),
                        'deliveryAddress' => self::getDeliveryAddressParameter($view->sUserData)
                    ));
                }
            }
            if ($request->getParam('sChange')) {
                $view->BillsafePaymentChange = true;
            }
        }
    }

    /**
     * Returns the informations of plugin as array.
     *
     * @return array
     */
    public function getInfo()
    {
        return include(dirname(__FILE__) . '/Meta.php');
    }

    /**
     * Returns the version of plugin as string.
     *
     * @return string
     */
    public function getVersion()
    {
        return '2.0.2';
    }

    /**
     * Creates and returns the billsafe client for an event.
     *
     * @param Enlight_Event_EventArgs $args
     * @return \Shopware_Components_Billsafe_Client
     */
    public function onInitResourceBillsafeClient(Enlight_Event_EventArgs $args)
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components_Billsafe',
            $this->Path() . 'Components/Billsafe/'
        );

        $client = new Shopware_Components_Billsafe_Client($this->Config());
        return $client;
    }
}
