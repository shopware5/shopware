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
 * Shopware Template Plugin
 *
 * todo@all: Documentation
 */
class Shopware_Plugins_Frontend_SwagTemplateSimple_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Installs the plugin
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvents();
        //$this->createForm();
        $this->createTemplate(array(
            'name' => 'Simple',
            'template' => 'emotion_simple',
            'version' => 2,
            'description' => null,
            'author' => 'shopware AG',
            'license' => '(c) shopware AG, 2012',
            'esi' => true
        ));

        return true;
    }

    /**
     * Creates and subscribe the events and hooks.
     */
    protected function subscribeEvents()
    {
        $this->subscribeEvent(
            'Enlight_Template_Manager_ResolveTemplateDir',
            'onResolveTemplateDir'
        );
    }

    /**
     * Creates and stores the payment config form.
     */
    protected function createForm()
    {
        $form = $this->Form();
        $form->setElement('boolean', 'simpleTemplateLogo', array(
            'label' => 'Logo anzeigen',
            'value' => true,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
        $form->setElement('number', 'simpleTemplateColumns', array(
            'label' => 'Anzahl-Spalten',
            'required' => true,
            'value' => 3,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
    }

    /**
     * Returns the path to a frontend controller for an event.
     *
     * @param Enlight_Event_EventArgs $args
     * @return string
     */
    public function onResolveTemplateDir(Enlight_Event_EventArgs $args)
    {
        $return = $args->getReturn();
        if($return === 'emotion_simple') {
            return $this->Path() . 'Views/';
        }
        return $return;
    }
}
