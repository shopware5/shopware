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
 * @author     Stefan Hamann
 * @author     Stephan Pohl
 * @author     $Author$
 */

/**
 * Shopware Plugin - Left column for the home site of the store front
 *
 * This plugin re-enables the left column on the home site of the store front. The user gets the
 * ability to customize the container width of the center column to match the template width.
 *
 * The plugin will only be triggered in the "emotion"-template which was release with Shopware 4.
 *
 * Please keep in mind that this plugin doesn't alter any emotion settings to match the new width
 * of the center column, so the user needs to rearrange the width of emotion container themself.
 */
class Shopware_Plugins_Frontend_SwagHomeLeftColumn_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

    /**
     * Returns an array with the capabilities of the plugin.
     *
     * @public
     * @return array
     */
    public function getCapabilities()
    {
        return array(
            'install' => true,
            'enable' => true,
            'update' => true
        );
    }

    /**
     * Returns the version of the plugin as a string
     *
     * @public
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * Returns the well-formatted name of the plugin
     * as a sting
     *
     * @public
     * @return string
     */
    public function getLabel()
    {
        return "Linke Spalte auf Startseite";
    }

    /**
     * Returns the meta informations about the plugin
     * as an array.
     * Keep in mind that the plugin description located
     * in the info.txt.
     *
     * @public
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version'     => $this->getVersion(),
            'label'       => $this->getLabel(),
            'link'        => 'http://www.shopware.de',
            'description' => file_get_contents($this->Path() . 'info.txt')
        );
    }

    /**
     * Installs the plugin and registers the configuration
     * form
     *
     * @public
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvents();
        $this->createConfigForm();
        return true;
    }

    /**
     * Creates and subscribe the events and hooks.
     *
     * @protected
     * @return void
     */
    protected function subscribeEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PostDispatch_Frontend_Index',
            'onHomeAction'
        );
    }

    /**
     * Creates the configuration form for the plugin
     *
     * @protected
     * @return void
     */
    protected function createConfigForm()
    {
        $form = $this->Form();
        $form->setElement('number', 'middleContainerWidth', array(
            'label' => 'Breite der mittleren Spalte (in Pixel)',
            'required' => true,
            'value' => 798,
            'scope' => \Shopware\Models\Config\Element::SCOPE_SHOP
        ));
    }

    /**
     * Event listener method which triggers on the post-dispatch
     * of the frontend-controller "index".
     *
     * @public
     * @param Enlight_Event_EventArgs $args
     * @return void
     */
    public function onHomeAction(Enlight_Event_EventArgs $args)
    {
        $subject   = $args->getSubject();
        $request   = $subject->Request();
        $response  = $subject->Response();
        $view      = $subject->View();
        $config    = $this->Config();
        $isEmotion = Shopware()->Shop()->getTemplate()->getVersion() > 1;

        if (!$request->isDispatched()
            || $response->isException()
            || $request->getModuleName() != 'frontend'
            || strtolower($request->getControllerName()) != 'index'
            || !$isEmotion) {
            return;
        }

        $view->addTemplateDir($this->Path() . 'Views/');
        $view->extendsTemplate('frontend/plugins/swag_leftcolumn/index.tpl');
        $view->assign('middleContainerWidth', $config->middleContainerWidth);
    }
}