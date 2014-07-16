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
 * Shopware Google Plugin
 */
class Shopware_Plugins_Frontend_Google_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Install plugin method
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch',
            'onPostDispatch'
        );

        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(array('name' => 'Interface'));
        $form->setParent($parent);
        $form->setElement('text', 'tracking_code', array(
            'label' => 'Google Analytics-ID',
            'value' => null,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('text', 'conversion_code', array(
            'label' => 'Google Conversion-ID',
            'value' => null,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('checkbox', 'anonymize_ip', array(
            'label' => 'IP-Adresse anonymisieren',
            'value' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        return true;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'label' => 'Google Analytics'
        );
    }

    /**
     * Event listener method
     *
     * @param Enlight_Event_EventArgs $args
     */
    public static function onPostDispatch(Enlight_Event_EventArgs $args)
    {
        $request = $args->getSubject()->Request();
        $response = $args->getSubject()->Response();
        $view = $args->getSubject()->View();

        if (!$request->isDispatched()
            || $response->isException()
            || $request->getModuleName() != 'frontend'
            || $request->isXmlHttpRequest()
            || !$view->hasTemplate()
        ) {
            return;
        }

        $config = Shopware()->Plugins()->Frontend()->Google()->Config();

        if (empty($config->tracking_code) && empty($config->conversion_code)) {
            return;
        }

        $view->extendsTemplate('frontend/plugins/google/index.tpl');

        if (!empty($config->conversion_code)) {
            $view->GoogleConversionID = $config->conversion_code;
            $view->GoogleConversionLanguage = Shopware()->Locale()->getLanguage();
        }
        if (!empty($config->tracking_code)) {
            $view->GoogleTrackingID = $config->tracking_code;
            $view->GoogleAnonymizeIp = $config->anonymize_ip;
        }
    }
}
