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
 * @package   Shopware\Plugins\Frontend\SofortPayment
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Frontend_SofortPayment_BootstrapDummy
    extends Shopware_Components_DummyPlugin_Bootstrap
{
    private $label = array(
        'de_DE' => 'SOFORT AG Shopware Module',
        'en_GB' => 'SOFORT AG Shopware Modules',
        'es_ES' => 'SOFORT AG Shopware Module',
        'fr_FR' => 'SOFORT Shopware Module',
        'it_IT' => 'SOFORT AG modulo Shopware',
        'nl_NL' => 'SOFORT shopware module',
        'pl_PL' => 'Moduł SOFORT AG Shopware'
    );

    /**
     * Returns the correct Label for the given language
     *
     * @return string
     */
    public function getLabel()
    {
        $currentLocale = Shopware()->Instance()->locale()->toString();

        return array_key_exists($currentLocale, $this->label) ? $this->label[$currentLocale] : $this->label['en_GB'];
    }

    /**
     * Get Info for the Pluginmanager
     *
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'author' => "PayIntelligent GmbH",
            'support' => "http://www.sofort.com/",
            'link' => "http://www.payintelligent.de",
            'copyright' => "Copyright (c) 2013, SOFORT AG",
            'label' => $this->getLabel(),
        );
    }
}
