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
 * @package   Shopware\Plugins\Frontend\PaymentSkrill
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Frontend_PaymentSkrill_Bootstrap extends Shopware_Components_DummyPlugin_Bootstrap
{
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'autor' => 'Skrill Holdings Ltd.',
            'label' => $this->getName(),
            'source' => $this->getSource(),
            'description' => 'Skrill (Moneybookers) ist die sichere Art, weltweit zu bezahlen, ohne ihre Bezahldaten jedesmal neu einzugeben.
				    Sie können in 200 Ländern über 100 verschiedene Zahlungsoptionen nutzen, einschließlich aller wichtigen Kredit- und EC- Karten.',
            'license' => '',
            'support' => 'http://wiki.shopware.de',
            'link' => 'http://www.skrill.com/',
            'changes' => '[changelog]',
            'revision' => '[revision]'
        );
    }
}
