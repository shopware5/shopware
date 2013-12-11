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
 * @package    Order
 * @subpackage Controller
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */

/**
 *
 */
//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/controller/main"}
Ext.define('Shopware.apps.PluginManager.controller.Main', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init:function () {
        var me = this;

        /*{if {acl_is_allowed privilege=read}}*/
        me.subApplication.pluginStore = me.getStore('Plugin');
        me.subApplication.pluginStore.getProxy().extraParams.category = 'Community';
        me.subApplication.pluginStore.load({
            callback: function(records, operation, success) {
                if(success) {
                    return true;
                }

                var error = Ext.util.Format.stripTags(operation.error);
                Ext.MessageBox.alert('Error-Report', error);
            }
        });

        /** {if $storeApiAvailable} */
        me.subApplication.communityStore = me.getStore('Community');
        me.subApplication.topSellerStore = me.getStore('TopSeller');
        me.subApplication.categoryStore = me.getStore('Category');

        me.subApplication.licencedProductStore = me.getStore('LicencedProduct');
        me.subApplication.updatesStore = me.getStore('Updates').load();
        me.subApplication.myAccount = Ext.create('Shopware.apps.PluginManager.model.Account');
        /** {/if} */

        me.getView('main.Window').create({
            categoryStore: me.subApplication.categoryStore,
            pluginStore: me.subApplication.pluginStore,
            /** {if $storeApiAvailable} */
            communityStore: me.subApplication.communityStore,
            accountStore: me.subApplication.accountStore,
            licencedProductStore: me.subApplication.licencedProductStore,
            topSellerStore: me.subApplication.topSellerStore,
            updatesStore: me.subApplication.updatesStore
            /** {/if} */
        });
		/** {/if} */

        me.callParent(arguments);
    }

});
//{/block}
