<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License and of our
 * proprietary license can be found at and
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
 * @subpackage SkrillPayment
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Skrill Holdings Ltd.
 * @author     $Author$
 */

class Shopware_Plugins_Frontend_PaymentSkrill_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public static $paymentMethods = array('WLT' => 'Moneybookers eWallet',
					'ACC' => 'All credit/debit cards',
					'VSA' => 'VISA',
					'MSC' => 'MASTERCARD',
					'VSD' => 'DELTA / VISA DEBIT',
					'VSE' => 'VISA ELECTRON',
					'AMX' => 'AMERICAN EXPRESS',
					'DIN' => 'DINERS',
					'JCB' => 'JCB',
					'MAE' => 'MAESTRO',
					'LSR' => 'LASER',
					'SLO' => 'SOLO',
					'GCB' => 'Carte Bleue',
					'SFT' => 'Sofortueberweisung',
					'DID' => 'direct debit',
					'GIR' => 'Giropay',
					'ENT' => 'Enets',
					'EBT' => 'Solo sweden',
					'SO2' => 'Solo finland',
					'NPY' => 'eps (NetPay)',
					'PLI' => 'POLi',
					'DNK' => 'Dankort',
					'CSI' => 'CartaSi',
					'PSP' => 'Postepay',
					'EPY' => 'ePay Bulgaria',
					'BWI' => 'BWI',
					'PWY' => 'All Polish Banks',
                                        'PAY' => 'Payolution');

    public static $logos = array('WLT'	=> 'skrill-powered-btn-digitalwallet-rgb_90x45.gif',
				 'VSA'	=> 'skrill-powered-btn-visa-rgb_90x45.gif',
				 'MSC'	=> 'skrill-powered-btn-mc-rgb_90x45.gif',
				 'VSD'	=> 'skrill-powered-btn-visadebit-rgb_90x45.gif',
				 'VSE'	=> 'skrill-powered-btn-visa-electron_90x45.gif',
				 'AMX'	=> 'skrill-powered-btn-amex-rgb_90x45.gif',
				 'DIN'	=> 'skrill-powered-btn-diners-rgb_90x45.gif',
				 'JCB'	=> 'skrill-powered-btn-jcb-rgb_90x45.gif',
				 'MAE'	=> 'skrill-powered-btn-maestro-rgb_90x45.gif',
				 'LSR'	=> 'skrill-powered-btn-laser_90x45.gif',
				 'SLO'	=> 'skrill-powered-btn-solo-rgb_90x45.gif',
				 'GCB'	=> 'skrill-powered-btn-cartebleue-rgb_90x45.gif',
				 'SFT'	=> 'skrill-powered-btn-sofort-rgb_90x45.gif',
				 'DID'	=> 'skrill-powered-btn-ec_90x45.gif',
				 'GIR'	=> 'skrill-powered-btn-giropay-rgb_90x45.gif',
				 'ENT'	=> 'skrill-powered-btn-enets-rgb_90x45.gif',
				 'EBT'	=> 'skrill-powered-btn-nordea-rgb_90x45.gif',
				 'SO2'	=> 'skrill-powered-btn-nordea-rgb_90x45.gif',
				 'NPY'	=> 'skrill-powered-btn-eps_90x45.gif',
				 'PLI'	=> 'skrill-powered-btn-poli-rgb_90x45.gif',
				 'DNK'	=> 'skrill-powered-btn-dankort-rgb_90x45.gif',
				 'CSI'	=> 'skrill-powered-btn-cartasi-rgb_90x45.gif',
				 'PSP'	=> 'skrill-powered-btn-postepay-rgb_90x45.gif',
				 'EPY'	=> 'skrill-powered-btn-epay.bg_90x45.gif',
				 'BWI'	=> 'skrill-powered-btn-bank-rgb_90x45.gif',
				 'PWY'	=> 'skrill-powered-btn-p24-rgb_90x45.gif');

    public static function onSaveForm(Enlight_Hook_HookArgs $args)
    {
        $class = $args->getSubject();
        $request = $class->Request();
        $pluginId = (int)$request->id;
        $elements = $request->getPost('elements');

        foreach ($elements as $element_id => $element_data) {
            foreach (self::$paymentMethods as $pAbbrMethod => $pMethod) {
                if ($element_data['name'] != 'skrill_' . strtolower($pAbbrMethod)) {
                    continue;
                }

                $pMethodElement = new Shopware_Components_PaymentSkrill_Checkbox('skrill_' . strtolower($pAbbrMethod), $pluginId);
                $pMethodElement->setValue($element_data['values'][0]['value']);
                $pMethodElement->description = 'Skrill ' . $pMethod;
                if (self::$logos[$pAbbrMethod]) {
                    $pMethodElement->logoName = self::$logos[$pAbbrMethod];
                }
                $pMethodElement->save();
            }
        }
    }

    protected function createPayments()
    {
        $payment = Shopware()->Payments()->fetchRow(array('name=?' => 'skrill'));

        if (!$payment) {
            Shopware()->Payments()->createRow(array('name' => 'skrill', 'description' => 'Skrill', 'action' => 'payment_skrill', 'active' => 1, 'pluginID' => $this->getId(), 'additionaldescription' => '<!-- Skrill -->
				    <img src="https://www.moneybookers.com/ads/skrill-brand-centre/resources/images/skrill-chkout_de_110x62.gif"/>
				    <!-- Skrill --><br/><br/>' . '<div id="skrill_desc">
					Skrill (Moneybookers) ist die sichere Art, weltweit zu bezahlen, ohne ihre Bezahldaten jedesmal neu einzugeben.
					Sie können in 200 Ländern über 100 verschiedene Zahlungsoptionen nutzen, einschließlich aller wichtigen Kredit- und EC- Karten.
				    </div>'))->save();
        }
    }

    public function install()
    {
        Shopware()->Loader()->registerNamespace('Shopware_Components_PaymentSkrill', dirname(__FILE__) . '/Components/Skrill/');

        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');

        $event = $this->createEvent('Enlight_Controller_Action_PostDispatch', 'onPostDispatch');
        $this->subscribeEvent($event);

        $event = $this->createEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentSkrill', 'onGetControllerPath');
        $this->subscribeEvent($event);

        $this->createPayments();
        $this->createForm();

        return true;
    }

    public function uninstall()
    {
        if ($payment = $this->Payment()) {
            $payment->delete();
        }

        $form = $this->Form();

        foreach (self::$paymentMethods as $pAbbrMethod => $pMethod) {
            $pMethodElement = $form->getElement('skrill_' . strtolower($pAbbrMethod));
            if (!$pMethodElement) {
                continue;
            }

            $pMethodNew = new Shopware_Components_PaymentSkrill_Checkbox('skrill_' . strtolower($pAbbrMethod), $this->getId());
            $pMethodNew->deletePayment();
        }

        return parent::uninstall();
    }

    public function enable()
    {
        $payment = $this->Payment();
        if ($payment !== null) {
            $payment->active = 1;
        }

        return true;
    }

    public function disable()
    {
        $payment = $this->Payment();
        if ($payment !== null) {
            $payment->active = 0;
        }
        return true;
    }

    public function Payment()
    {
        return Shopware()->Payments()->fetchRow(array('name=?' => 'skrill'));
    }

    public static function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');
        return dirname(__FILE__) . '/Controllers/frontend/Skrill.php';
    }

    public static function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();

        if ($request->getActionName() == 'saveForm' && $request->getModuleName() == 'backend' && $request->getControllerName() == 'config') {
            self::onSaveForm($args);
            return;
        }

        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || !$view->hasTemplate()) {
            return;
        }
    }

    public function createForm()
    {
        $form = $this->Form();

        $form->setElement('text', 'skrillUrl', array('label' => 'Gateway URL', 'value' => 'https://www.moneybookers.com/app/payment.pl', 'required' => true));

        $form->setElement('text', 'merchantEmail', array('label' => 'Merchant Email', 'required' => true));

        $form->setElement('text', 'secretWord', array('label' => 'Secret Word', 'required' => true));

        $form->setElement('text', 'logoUrl', array('label' => 'Shop Logo URL'));

        $form->setElement('checkbox', 'hideLogin', array('label' => 'Hide Login', 'value' => true));

        foreach (self::$paymentMethods as $pAbbrMethod => $pMethod) {
            $pMethodElement = new Shopware_Components_PaymentSkrill_Checkbox('skrill_' . $pAbbrMethod, $this->getId());
            $pMethodElement->setLabel($pMethod);
            $pMethodElement->description = 'Skrill ' . $pMethod;
            if (self::$logos[$pAbbrMethod]) {
                $pMethodElement->logoName = self::$logos[$pAbbrMethod];
            }
            $pMethodElement->setValue(false);
            $pMethodElement->save();

            $form->setElement('checkbox', $pMethodElement->name, array('label' => $pMethodElement->description, 'value' => false));
        }
        /* All checkboxes must be replaced by the following code whenever we can save/load multiple option fields
         * in Shopware 4.0
         *
         * $skrillmultiple = new Shopware_Components_Skrill_Multiselect('paymentMethods');
         * $skrillmultiple->setLabel('Payment methods');
         * $skrillmultiple->addMultiOptions($paymentMethods);
         * $form->addElement($skrillmultiple);
         *
         * */
    }

    public function getVersion()
    {
        return '2.0.0';
    }

    public function getInfo()
	{
    	return array('version'	=> $this->getVersion(),
		    'autor'	=> 'Skrill Holdings Ltd.',
		    'label'	=> $this->getName(),
		    'source'	=> $this->getSource(),
		    'description'
				=> 'Skrill (Moneybookers) ist die sichere Art, weltweit zu bezahlen, ohne ihre Bezahldaten jedesmal neu einzugeben.
				    Sie können in 200 Ländern über 100 verschiedene Zahlungsoptionen nutzen, einschließlich aller wichtigen Kredit- und EC- Karten.',
		    'license'	=> '',
		    'support'	=> 'http://wiki.shopware.de',
		    'link'	=> 'http://www.skrill.com/',

		    'changes'	=> '[changelog]',
		    'revision'	=> '[revision]');
	}

    }