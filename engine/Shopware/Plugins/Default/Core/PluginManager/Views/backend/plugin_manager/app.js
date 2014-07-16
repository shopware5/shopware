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
 *
 * @category   Shopware
 * @package    Article
 * @subpackage Detail
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */

/**
 *
 */
//{block name="backend/plugin_manager/application"}
Ext.define('Shopware.apps.PluginManager', {

    /**
     * The name of the module. Used for internal purpose
     * @string
     */
    name:'Shopware.apps.PluginManager',

    /**
     * Extends from our special controller, which handles the sub-application behavior and the event bus
     * @string
     */
    extend:'Enlight.app.SubApplication',

    /**
     * Enable bulk loading
     * @boolean
     */
    bulkLoad:true,

    /**
     * Sets the loading path for the sub-application.
     *
     * @string
     */
    loadPath:'{url controller=PluginManager action=load}',

    /**
     * Array of views to require from AppName.view namespace.
     * @array
     */
    views:[
        'main.Window',
        'detail.Window',
        'manager.Navigation',
        'manager.Grid',
        'manager.Options',
        'manager.ManualInstall',
        'detail.Description',
        'detail.Settings'

        /** {if $storeApiAvailable} */
        , 'account.LoginWindow',
        'account.Confirm',
        'account.Licenses',
        'account.Updates',
        'store.View',
        'store.Navigation'
        /** {/if} */
    ],


    /**
     * Array of models to require from AppName.model namespace.
     * @array
     */
    models: [
        'Account',
        'Addon',
        'Attribute',
        'Category',
        'Detail',
        'LicencedProduct',
        'License',
        'Media',
        'Plugin',
        'Product',
        'Update',
        'Vote'
    ],

    /**
     * Array of stores to require from AppName.store namespace.
     * @array
     */
    stores:[
        'Category',
        'Community',
        'Plugin',
        'TopSeller',
        'Product',
        'Updates',
        'LicencedProduct',
        'Detail',
        'Votes'
    ],

    /**
     * Requires controllers for sub-application
     * @array
     */
    controllers: [ 'Main', 'Manager' /** {if $storeApiAvailable} */ ,'Store', 'Account', 'Update' /** {/if} */ ],

    /**
     * Returns the main application window for this is expected
     * by the Enlight.app.SubApplication class.
     * The class sets a new event listener on the "destroy" event of
     * the main application window to perform the destroying of the
     * whole sub application when the user closes the main application window.
     *
     * This method will be called when all dependencies are solved and
     * all member controllers, models, views and stores are initialized.
     *
     * @private
     * @return [object] mainWindow - the main application window based on Enlight.app.Window
     */
    launch: function() {
        var me = this,
            mainController = me.getController('Main');

        return mainController.mainWindow;
    }
});
//{/block}

