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
 * @subpackage Skrill
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Skrill Holdings Ltd.
 * @author     $Author$
 */

class Shopware_Plugins_Frontend_PaymentSkrill_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    public function install()
    {
        Shopware()->Loader()->registerNamespace('Shopware_Components_PaymentSkrill', dirname(__FILE__) . '/Components/Skrill/');
        $this->createPayments();
        $this->createForm();
        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');

        $event = $this->createEvent('Enlight_Controller_Action_PostDispatch', 'onPostDispatch');
        $this->subscribeEvent($event);

        $event = $this->createEvent('Enlight_Controller_Dispatcher_ControllerPath_Frontend_PaymentSkrill', 'onGetControllerPath');
        $this->subscribeEvent($event);

        return true;
    }

    public function uninstall()
    {
        if ($payment = $this->Payment()) {
            $payment->delete();
        }

        return parent::uninstall();
    }

    public function enable()
    {
        $payment         = $this->Payment();
        $payment->active = 1;
        $payment->save();

        return parent::enable();
    }

    public function disable()
    {
        $payment         = $this->Payment();
        $payment->active = 0;
        $payment->save();

        return parent::disable();
    }

    protected function createPayments()
        {
        $paymentRow = Shopware()->Payments()->createRow(array(
                            'name' => 'skrill',
                            'description' => 'Skrill',
                            'action' => 'payment_skrill',
                            'active' => 1,
                            'pluginID' => $this->getId(),
                            'additionaldescription' =>
                            '<!-- Skrill -->
                            <img src="https://www.moneybookers.com/ads/skrill-brand-centre/resources/images/skrill-chkout_de_110x62.gif"/>
                            <!-- Skrill --><br/><br/>' .
                            '<div id="skrill_desc">
                            Skrill (Moneybookers) ist die sichere Art, weltweit zu bezahlen, ohne ihre Bezahldaten jedesmal neu einzugeben.
                            Sie können in 200 Ländern über 100 verschiedene Zahlungsoptionen nutzen, einschließlich aller wichtigen Kredit- und EC- Karten.
                            </div>'
                            ))->save();
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
        $request  = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view     = $args->getSubject()->View();

        Shopware()->Template()->addTemplateDir(dirname(__FILE__) . '/Views/');

        if (!$request->isDispatched() || $response->isException() || $request->getModuleName() != 'frontend' || !$view->hasTemplate()) {
            return;
        }
    }

    public function createForm()
    {
        $form = $this->Form();

        $form->setElement('text', 'skrillUrl', array(
            'label' => 'Gateway URL',
            'value' => 'https://www.moneybookers.com/app/payment.pl',
            'required' => true
        ));

        $form->setElement('text', 'merchantEmail', array(
            'label' => 'Merchant Email',
            'required' => true
        ));

        $form->setElement('text', 'secretWord', array(
            'label' => 'Secret Word',
            'required' => true
        ));

        $form->setElement('text', 'logoUrl', array(
            'label' => 'Shop Logo URL'
        ));

        $form->setElement('checkbox', 'hideLogin', array(
            'label' => 'Hide Login',
            'value' => true
        ));
    $form->save();
    }
}