/**
 * Shopware 5
 * Copyright (c) shopware AG
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
 * @package    Customer
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}

/**
 * Shopware Controller - Customer list backend module
 *
 * The customer module main controller handles the initialisation of the customer backend list.
 * It is possible to pass a customer id to the module to open the detail window directly. To
 * open the detail window directly pass the customer id in the parameter "customerId"
 */
// {block name="backend/customer/controller/main"}
Ext.define('Shopware.apps.Customer.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub application
     *
     * @params customerId - The main controller can handle a customerId parameter to open the customer detail page directly
     * @return void
     */
    init: function () {
        var me = this;

        if (me.subApplication.action && me.subApplication.action.toLowerCase() === 'detail') {
            if (me.subApplication.params && me.subApplication.params.customerId) {
                me.startEditApplication();
            } else {
                me.startCreateApplication();
            }
        } else {
            me.startListApplication();
        }

        me.callParent(arguments);
    },

    startListApplication: function() {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=UserConfig action=get}',
            params: {
                name: 'customer_module'
            },
            callback: function (request, success, response) {
                var config = Ext.JSON.decode(response.responseText);

                if (!config || config.length <= 0) {
                    config = { showWizard: true, autoIndex: false };
                }

                me.subApplication.userConfig = config;

                if (!config.showWizard) {
                    me.mainWindow = me.getView('main.Window').create();
                    return;
                }

                var wizard = me.getView('main.Wizard').create();
                wizard.on('finish', Ext.bind(me.switchWizardConfig, me));
                wizard.show();
            }
        });
    },

    switchWizardConfig: function() {
        var me = this, config = me.subApplication.userConfig;

        config.showWizard = false;

        Ext.Ajax.request({
            url: '{url controller=UserConfig action=save}',
            params: {
                config: Ext.JSON.encode(config),
                name: 'customer_module'
            },
            callback: function() {
                me.mainWindow = me.getView('main.Window').create();
            }
        });
    },

    startEditApplication: function() {
        var me = this, store;

        // open the customer detail page with the passed customer id
        store = me.subApplication.getStore('Detail');
        store.getProxy().extraParams = {
            customerID: me.subApplication.params.customerId
        };

        me.mainWindow = me.subApplication.getView('detail.Window').create().show();
        me.mainWindow.setLoading(true);

        store.load({
            callback: function (records) {
                var customer = records[0];
                var store = Ext.create('Shopware.apps.Customer.store.Batch');
                store.load({
                    callback: function (records) {
                        var storeData = records[0];
                        me.mainWindow.record = customer;
                        me.mainWindow.createTabPanel();
                        me.mainWindow.setLoading(false);
                        me.mainWindow.setStores(storeData);
                        me.subApplication.setAppWindow(me.mainWindow);
                    }
                });
            }
        });
    },

    startCreateApplication: function() {
        var me = this, store;

        store = Ext.create('Shopware.apps.Customer.store.Batch');
        store.load({
            callback: function (records) {
                var storeData = records[0];

                me.mainWindow = me.subApplication.getView('detail.Window').create().show();
                me.mainWindow.setLoading(true);
                me.mainWindow.record = Ext.create('Shopware.apps.Customer.model.Customer');
                me.mainWindow.createTabPanel();
                me.mainWindow.setStores(storeData);
                me.mainWindow.setLoading(false);
            }
        });
    }

});
// {/block}
