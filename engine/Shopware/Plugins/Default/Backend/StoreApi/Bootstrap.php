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
class Shopware_Plugins_Backend_StoreApi_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

    /**
     * Returns an array with the capabilities of the store ap
     * @return array
     */
    public function getCapabilities()
    {
        return array(
            'install' => false,
            'enable' => false,
            'update' => true
        );
    }

    /**
     * The install routine of the plugin
     *
     * - adds store api the resource
     * - adds a global pre dispatch
     *
     * @return bool
     */
    public function install() {

        //Create the new resource StoreApi
        $event = $this->createEvent(
            'Enlight_Bootstrap_InitResource_StoreApi',
            'onInitResourceStoreApi'
        );
        $this->subscribeEvent($event);

        //Registers the global pre dispatcher event
        $event = $this->createEvent(
            'Enlight_Controller_Action_PreDispatch',
            'onPreDispatch'
        );
        $this->subscribeEvent($event);

        //creates the standard plugin form
        $form = $this->Form();
        $form->setElement('text', 'StoreApiUrl', array('label' => 'Store API Url', 'value' => 'http://store.shopware-preview.de/storeApi', 'scope'=>Shopware_Components_Form::SCOPE_SHOP));
        $form->save();


        return true;
    }

    /**
     * Returns an instance of the Shopware_Components_Api object
     * The instance will be initializes as a singleton
     *
     * @static
     * @param Enlight_Event_EventArgs $args
     * @return Shopware_Components_Api
     */
    public static function onInitResourceStoreApi(Enlight_Event_EventArgs $args)
    {
        //Setup the alternative path for the namespace Shopware_Components
        Shopware()->Loader()->registerNamespace('Shopware_Components', dirname(__FILE__).'/Components/');
        Shopware()->Loader()->registerNamespace('Shopware_StoreApi', dirname(__FILE__).'/StoreApi/');

        return new Shopware_Components_StoreApi();
    }

    /**
     * Global pre dispatcher
     * - Adds the local components folder to the loader
     * - Adds the local template path as a new template dir
     *
     * @static
     * @param Enlight_Event_EventArgs $args
     */
    public static function onPreDispatch(Enlight_Event_EventArgs $args)
    {
        //Fetches the subject and request object of the controller instance
        $me = $args->getSubject();
        $request = $me->Request();

        //Aborts if the current is not a backend controller
        if(strtolower($request->getModuleName()) != 'backend') {
            return;
        }

        //Setup the alternative path for the namespace Shopware_Components
        Shopware()->Loader()->registerNamespace('Shopware_Components', dirname(__FILE__).'/Components/');
        Shopware()->Loader()->registerNamespace('Shopware_StoreApi', dirname(__FILE__).'/StoreApi/');

        //Adds the local template path as a new template dir
        $me->View()->addTemplateDir(dirname(__FILE__).'/Views/');
    }
}
