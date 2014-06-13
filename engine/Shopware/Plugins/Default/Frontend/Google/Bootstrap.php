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
        /** @var \Shopware\Models\Config\Form $parent */
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
        $form->setElement('combo', 'trackingLib', array(
            'label' => 'Tracking Bibliothek',
            'value' => 'ga',
            'store' => array(
                array('ga', 'Google Analytics'),
                array('ua', 'Universal Analytics'),
            ),
            'description' => 'Welche Tracking Bibliothek soll benutzt werden? Standardmäßig wird die veraltete Google Analytics verwendet. Der Wechsel zur Universal-Analytics-Bibliothek erfordert, das Sie Ihre Google Analytics Einstellungen aktualisieren. Für mehr Informationen besuchen Sie die offizielle Google-Dokumentation.',
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));

        $this->addFormTranslations(
            array(
                'en_GB' => array(
                    'plugin_form' => array(
                        'label' => 'Google Analytics'
                    ),
                    'trackingLib' => array(
                        'label' => 'Tracking library',
                        'description' => 'Tracking library to use. Defaults to legacy Google Analytics. Switching to Universal Analytics requires that you update you settings in your Google Analytics Admin page. Please check Google\'s official documentation for more info.'
                    ),
                    'tracking_code' => array(
                        'label' => 'Google Analytics ID'
                    ),
                    'conversion_code' => array(
                        'label' => 'Google Conversion ID'
                    ),
                    'anonymize_ip' => array(
                        'label' => 'Anonymous IP address'
                    ),
                )
            )
        );

        return true;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version'   => $this->getVersion(),
            'label'     => 'Google Analytics'
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
            $view->GoogleTrackingLibrary = $config->trackingLib;
        }
    }

    /**
     * Returns the version of the plugin as a string
     *
     * @return string
     */
    public function getVersion() {
        return '2.0.0';
    }
}
