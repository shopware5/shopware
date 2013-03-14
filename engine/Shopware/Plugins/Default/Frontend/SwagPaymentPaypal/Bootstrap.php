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
 * Shopware Paypal Plugin
 *
 * todo@all: Documentation
 */
class Shopware_Plugins_Frontend_SwagPaymentPaypal_Bootstrap extends Shopware_Components_Plugin_Bootstrap
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
        $this->createMyEvents();
        $this->createMyPayment();
        $this->createMyMenu();
        $this->createMyForm();
        $this->createMyTranslations();

        try {
            $this->Application()->Models()->addAttribute(
                's_order_attributes', 'swag_payal',
                'billing_agreement_id', 'VARCHAR(255)'
            );
        } catch(Exception $e) { }

        $this->Application()->Models()->generateAttributeModels(array(
            's_order_attributes'
        ));

        return true;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        try {
            $this->Application()->Models()->removeAttribute(
                's_order_attributes',
                'swag_payal',
                'billing_agreement_id'
            );
            $this->Application()->Models()->generateAttributeModels(array(
                's_order_attributes'
            ));
        } catch(Exception $e) { }

        return true;
    }

    /**
     * @param string $version
     * @return bool
     */
    public function update($version)
    {
        if(strpos($version, '2.0.') === 0) {
            try {
                $this->Application()->Models()->removeAttribute(
                    's_user_attributes',
                    'swag_payal',
                    'billing_agreement_id'
                );
            } catch(Exception $e) { }
            try {
                $this->Application()->Models()->addAttribute(
                    's_order_attributes', 'swag_payal',
                    'billing_agreement_id', 'VARCHAR(255)'
                );
            } catch(Exception $e) { }

            $this->Application()->Models()->generateAttributeModels(array(
                's_order_attributes', 's_user_attributes'
            ));

            //Remove old element
            $element = $this->Form()->getElement('paypalAllowGuestCheckout');
            $this->Form()->getElements()->removeElement($element);
        }
        //Update form
        $this->createMyForm();
        return true;
    }

    /**
     * Fetches and returns paypal payment row instance.
     *
     * @return \Shopware\Models\Payment\Payment
     */
    public function Payment()
    {
        return $this->Payments()->findOneBy(
            array('name' => 'paypal')
        );
    }

    /**
     * @return \Shopware_Components_Paypal_Client
     */
    public function Client()
    {
        return $this->Application()->PaypalClient();
    }

    /**
     * Activate the plugin paypal plugin.
     * Sets the active flag in the payment row.
     *
     * @return bool
     */
    public function enable()
    {
        $payment = $this->Payment();
        $payment->setActive(true);
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
        if ($payment !== null) {
            $payment->setActive(false);
        }
        return true;
    }

    /**
     * Creates and subscribe the events and hooks.
     */
    protected function createMyEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentPaypal',
            'onGetControllerPathFrontend'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_PaymentPaypal',
            'onGetControllerPathBackend'
        );

        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatch',
            110
        );

        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_PaypalClient',
            'onInitResourcePaypalClient'
        );
    }

    /**
     * Creates and save the payment row.
     */
    protected function createMyPayment()
    {
        $this->createPayment(array(
            'name' => 'paypal',
            'description' => 'PayPal',
            'action' => 'payment_paypal',
            'active' => 0,
            'position' => 0,
            'additionalDescription' => '<!-- PayPal Logo -->' .
                '<a onclick="window.open(this.href, \'olcwhatispaypal\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=500\'); return false;"' .
                '    href="https://www.paypal.com/de/cgi-bin/webscr?cmd=xpt/cps/popup/OLCWhatIsPayPal-outside" target="_blank">' .
                '<img src="{link file="frontend/_resources/images/paypal/pp-corporate-Logo-small.png"}" alt="Logo \'PayPal empfohlen\'">' .
                '</a>' . '<!-- PayPal Logo --><p>PayPal. <em>Sicherererer.</em></p>' .
                'Bezahlung per PayPal - einfach, schnell und sicher.'
        ));
    }

    /**
     * Creates and stores a payment item.
     */
    protected function createMyMenu()
    {
        $parent = $this->Menu()->findOneBy('label', 'Zahlungen');
        $this->createMenuItem(array(
            'label' => 'PayPal',
            'controller' => 'PaymentPaypal',
            'action' => 'Index',
            'class' => 'ico2 date2',
            'active' => 1,
            'parent' => $parent
        ));
    }

    /**
     * Creates and stores the payment config form.
     */
    protected function createMyForm()
    {
        $form = $this->Form();

        // API settings
        $form->setElement('text', 'paypalUsername', array(
            'label' => 'API-Benutzername',
            'required' => true
        ));
        $form->setElement('text', 'paypalPassword', array(
            'label' => 'API-Passwort',
            'required' => true
        ));
        $form->setElement('text', 'paypalSignature', array(
            'label' => 'API-Unterschrift',
            'required' => true
        ));
        $form->setElement('text', 'paypalVersion', array(
            'label' => 'API-Version',
            'value' => '93.0',
            'required' => true,
            'readOnly' => true
        ));
        $form->setElement('button', 'paypalButtonApi', array(
            'label' => '<strong>Jetzt API-Signatur erhalten</strong>',
            'handler' => "function(btn) {
                //var sandbox = btn.up('form').down('[elementName=paypalSandbox]').getValue();
                //'https://www.sandbox.paypal.com/de/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true'
                var link = 'https://www.paypal.com/de/cgi-bin/webscr?cmd=_get-api-signature&generic-flow=true';
                window.open(link, '', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=540');
            }"
        ));
        $form->setElement('boolean', 'paypalSandbox', array(
            'label' => 'Sandbox-Modus aktivieren'
        ));
        $form->setElement('boolean', 'paypalErrorMode', array(
            'label' => 'Fehlermeldungen ausgeben'
        ));

        // Payment page settings
        $form->setElement('text', 'paypalBrandName', array(
            'label' => 'Alternativer Shop-Name auf der PayPal-Seite',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'paypalLogoImage', array(
            'label' => 'Shop-Logo auf der PayPal-Seite',
            'value' => 'frontend/_resources/images/logo.jpg',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('color', 'paypalCartBorderColor', array(
            'label' => 'Farbe des Warenkorbs auf der PayPal-Seite',
            'value' => '#E1540F',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        // Frontend settings
        $form->setElement('boolean', 'paypalFrontendLogo', array(
            'label' => 'Payment-Logo im Frontend ausgeben',
            'value' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'paypalFrontendLogoBlock', array(
            'label' => 'Template-Block für das Payment-Logo',
            'value' => 'frontend_index_left_campaigns_bottom',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        // Payment settings
        $form->setElement('boolean', 'paypalPaymentActionPending', array(
            'label' => 'Zahlungen nur autorisieren (Auth-Capture)',
            'value' => false,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('boolean', 'paypalBillingAgreement', array(
            'label' => 'Zahlungsvereinbarung treffen / „Sofort-Kaufen“ aktivieren',
            'description' => 'Achtung: Diese Funktion muss erst für Ihren PayPal-Account von PayPal aktiviert werden.',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('boolean', 'paypalTransferCart', array(
            'label' => 'Warenkorb an PayPal übertragen',
            'value' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('boolean', 'paypalExpressButton', array(
            'label' => 'Express-Kauf-Button im Warenkorb anzeigen',
            'value' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('boolean', 'paypalExpressButtonLayer', array(
            'label' => 'Express-Kauf-Button in der Modal-Box anzeigen',
            'value' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        //$form->setElement('boolean', 'paypalAddressOverride', array(
        //    'label' => 'Lieferadresse in PayPal ändern erlauben',
        //    'value' => false,
        //    'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        //));
        $form->setElement('select', 'paypalStatusId', array(
            'label' => 'Zahlstatus nach der kompletter Zahlung',
            'value' => 12,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('select', 'paypalPendingStatusId', array(
            'label' => 'Zahlstatus nach der Autorisierung',
            'value' => 18,
            'store' => 'base.PaymentStatus',
            'displayField' => 'description',
            'valueField' => 'id',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('boolean', 'paypalStatusMail', array(
            'label' => 'eMail bei Zahlstatus-Änderung verschicken',
            'value' => false,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
    }

    /**
     *
     */
    public function createMyTranslations()
    {
        $form = $this->Form();
        $translations = array(
            'en_GB' => array(
                'paypalUsername' => 'API username',
                'paypalPassword' => 'API password',
                'paypalSignature' => 'API signature',
                'paypalVersion' => 'API version',
                'paypalSandbox' => 'Activate sandbox mode',
                'paypalBrandName' => 'Alternative shop name on PayPal\'s site',
                'paypalLogoImage' => 'Shop image for PayPal',
                'paypalCartBorderColor' => 'Color of the basket on PayPal',
                'paypalFrontendLogo' => 'Show payment logo on frontend',
                'paypalFrontendLogoBlock' => 'Template block for the frontend logo',
                'paypalPaymentActionPending' => 'Only authorize payments (Auth-Capture)',
                'paypalBillingAgreement' => 'Billing agreement / Activate "Buy it now"',
                'paypalTransferCart' => 'Transfer basket to PayPal',
                'paypalExpressButton' => 'Show express-purchase button in basket',
                'paypalExpressButtonLayer' => 'Show express-purchase button in modal box',
                'paypalStatusId' => 'Payment state after completing the payment',
                'paypalPendingStatusId' => 'Payment state after being authorized',
                'paypalStatusMail' => 'Send mail on payment state change'
            )
        );
        $shopRepository = Shopware()->Models()->getRepository('\Shopware\Models\Shop\Locale');
        foreach($translations as $locale => $snippets) {
            $localeModel = $shopRepository->findOneBy(array(
                'locale' => $locale
            ));
            foreach($snippets as $element => $snippet) {
                if($localeModel === null){
                    continue;
                }
                $elementModel = $form->getElement($element);
                if($elementModel === null) {
                    continue;
                }
                $translationModel = new \Shopware\Models\Config\ElementTranslation();
                $translationModel->setLabel($snippet);
                $translationModel->setLocale($localeModel);
                $elementModel->addTranslation($translationModel);
            }
        }
    }

    /**
     *
     */
    protected function registerMyTemplateDir()
    {
        $this->Application()->Template()->addTemplateDir(
            $this->Path() . 'Views/', 'paypal'
        );
    }

    /**
     * Returns the path to a frontend controller for an event.
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetControllerPathFrontend(Enlight_Event_EventArgs $args)
    {
        $this->registerMyTemplateDir();
        return $this->Path() . 'Controllers/Frontend/PaymentPaypal.php';
    }

    /**
     * Returns the path to a backend controller for an event.
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onGetControllerPathBackend(Enlight_Event_EventArgs $args)
    {
        $this->registerMyTemplateDir();
        $this->Application()->Snippets()->addConfigDir(
            $this->Path() . 'Snippets/'
        );
        return $this->Path() . 'Controllers/Backend/PaymentPaypal.php';
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        $locale = $this->Application()->Locale()->toString();
        if(strpos($locale, 'de_') === 0) {
            $locale = 'de_DE';
        }
        return $locale;
    }

    /**
     * Returns the path to a backend controller for an event.
     *
     * @param Enlight_Event_EventArgs $args
     */
    public function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        /** @var $action Enlight_Controller_Action */
        $action = $args->getSubject();
        $request = $action->Request();
        $response = $action->Response();
        $view = $action->View();

        if (!$request->isDispatched()
            || $response->isException()
            || $request->getModuleName() != 'frontend'
        ) {
            return;
        }

        if ($request->getControllerName() == 'checkout' || $request->getControllerName() == 'account') {
            $this->registerMyTemplateDir();
        }

        $config = $this->Config();
        if ($view->hasTemplate() && !empty($config->paypalFrontendLogo)) {
            $this->registerMyTemplateDir();
            $view->extendsBlock(
                $config->paypalFrontendLogoBlock,
                '{include file="frontend/payment_paypal/logo.tpl"}' . "\n",
                'append'
            );
        }

        if (!empty($config->paypalExpressButtonLayer)
            && $request->getControllerName() == 'checkout' && $request->getActionName() == 'ajax_add_article') {
            $view->PaypalShowButton = true;
            $view->PaypalLocale = $this->getLocale();
            $view->extendsBlock(
                'frontend_checkout_ajax_add_article_action_buttons',
                '{include file="frontend/payment_paypal/layer.tpl"}' . "\n",
                'prepend'
            );
        }

        if (!empty($config->paypalExpressButton)
          && $request->getControllerName() == 'checkout' && $request->getActionName() == 'cart') {
            $view->PaypalShowButton = true;
            $view->PaypalLocale = $this->getLocale();
            $view->extendsBlock(
                'frontend_checkout_actions_confirm',
                '{include file="frontend/payment_paypal/express.tpl"}' . "\n",
                'prepend'
            );
        }

        if($view->hasTemplate() && isset($view->PaypalShowButton)) {
            $showButton = false;
            $admin = Shopware()->Modules()->Admin();
            $payments = isset($view->sPayments) ? $view->sPayments : $admin->sGetPaymentMeans();
            foreach($payments as $payment) {
                if($payment['name'] == 'paypal') {
                    $showButton = true;
                    break;
                }
            }
            $view->PaypalShowButton = $showButton;
        }

        if ($request->getControllerName() == 'checkout' && $request->getActionName() == 'confirm') {
            if(!empty(Shopware()->Session()->PaypalResponse)) {
                $view->sRegisterFinished = false;
            }
        }
    }

    /**
     * @param   string $paymentStatus
     * @return  int
     */
    public function getPaymentStatusId($paymentStatus)
    {
        switch($paymentStatus) {
            case 'Completed':
                $paymentStatusId = $this->Config()->get('paypalStatusId', 12); break;
            case 'Pending':
                $paymentStatusId = $this->Config()->get('paypalPendingStatusId', 18); break; //Reserviert
            case 'Processed':
                $paymentStatusId = 18; break; //In Bearbeitung > Reserviert
            case 'Refunded':
                $paymentStatusId = 20; break; //Wiedergutschrift
            case 'Partially-Refunded':
                $paymentStatusId = 20; break; //Wiedergutschrift
            case 'Cancelled-Reversal':
                $paymentStatusId = 12; break;
            case 'Expired': //Offen
            case 'Denied':
            case 'Voided':
                $paymentStatusId = 17; break;
            case 'Reversed':
            default:
                $paymentStatusId = 21; break;
        }
        return $paymentStatusId;
    }

    /**
     * @param string $transactionId
     * @param string $paymentStatus
     * @param string|null $note
     * @return void
     */
    public function setPaymentStatus($transactionId, $paymentStatus, $note = null)
    {
        $paymentStatusId = $this->getPaymentStatusId($paymentStatus);
        $sql = '
            SELECT id FROM s_order WHERE transactionID=? AND status!=-1
        ';
        $orderId = Shopware()->Db()->fetchOne($sql, array(
            $transactionId
        ));
        $order = Shopware()->Modules()->Order();
        $order->setPaymentStatus($orderId, $paymentStatusId, false, $note);
        if ($paymentStatus == 'Completed') {
            $sql  = '
                UPDATE s_order SET cleareddate=NOW()
                WHERE transactionID=?
                AND cleareddate IS NULL LIMIT 1
            ';
            Shopware()->Db()->query($sql, array(
                $transactionId
            ));
        }
    }

    /**
     *
     * @return array
     */
    public function getLabel()
    {
        return 'PayPal Payment';
    }

    /**
     * Returns the version of plugin as string.
     *
     * @return string
     */
    public function getVersion()
    {
        return '2.1.1';
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'description' => file_get_contents($this->Path() . 'info.txt')
        );
    }

    /**
     * Creates and returns the paypal client for an event.
     *
     * @param Enlight_Event_EventArgs $args
     * @return \Shopware_Components_Paypal_Client
     */
    public function onInitResourcePaypalClient(Enlight_Event_EventArgs $args)
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware_Components_Paypal',
            $this->Path() . 'Components/Paypal/'
        );
        $client = new Shopware_Components_Paypal_Client($this->Config());
        return $client;
    }
}
