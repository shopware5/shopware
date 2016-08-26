
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
 * @package    PluginManager
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/plugin_manager/translation}
//{block name="backend/plugin_manager/controller/main"}
Ext.define('Shopware.apps.PluginManager.controller.Main', {
    extend:'Ext.app.Controller',
    mainWindow: null,

    refs: [
        { ref: 'navigation', selector: 'plugin-manager-listing-window plugin-category-navigation' },
        { ref: 'localListing', selector: 'plugin-manager-local-plugin-listing' },
        { ref: 'updatePage', selector: 'plugin-manager-update-page' },
        { ref: 'listingWindow', selector: 'plugin-manager-listing-window' }
    ],

    init: function() {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=PluginManager action=pingStore}',
            method: 'POST',
            success: function (operation, opts) {
                var response = Ext.decode(operation.responseText);

                Shopware.app.Application.sbpAvailable = response.success;

                if (me.subApplication.params) {
                    if (me.subApplication.params.displayPlugin) {
                        Shopware.app.Application.fireEvent('display-plugin-by-name', me.subApplication.params.displayPlugin);
                    }

                    if (me.subApplication.params.hidden) {
                        return;
                    }
                }

                if (!Shopware.app.Application.sbpAvailable) {
                    Shopware.Notification.createGrowlMessage('', '{s name="sbp_not_available"}Shopware store not available, store features disabled.{/s}');
                }
                me.mainWindow = me.getView('list.Window').create();
                me.mainWindow.show();
            }
        });

        me.control({
            'plugin-manager-listing-window': {
                'plugin-manager-loaded': me.afterPluginManagerLoaded
            },
            'plugin-manager-connect-introduction-page{ isVisible(true) }': {
                'connect-introduction-remove': me.removeConnectIntroduction,
                'connect-introduction-install': me.installConnectIntroduction
            }
        });

        Shopware.app.Application.on({
            'load-update-listing': me.loadUpdateListing,
            'enable-premium-plugins-mode': me.enablePremiumPluginsMode,
            'enable-expired-plugins-mode': me.enableExpiredPluginsMode,
            'enable-connect-introduction-mode': me.enableConnectIntroductionMode,
            scope: me
        });

        this.callParent(arguments);
    },

    enablePremiumPluginsMode: function() {
        var me = this;

        me.getListingWindow().setWidth(1028);
        me.getListingWindow().setTitle('{s name="premium_plugins/title"}Try features{/s}');
        me.getNavigation().hide();
    },

    enableExpiredPluginsMode: function() {
        var me = this,
            listingWindow = me.getListingWindow();

        listingWindow.setWidth(1028);
        listingWindow.setTitle('{s name="expired_plugins/title"}Expired plugins{/s}');
        me.getNavigation().hide();
    },

    enableConnectIntroductionMode: function() {
        var me = this,
            listingWindow = me.getListingWindow();

        listingWindow.setWidth(800);
        listingWindow.setHeight(710);
        listingWindow.setTitle('{s name="connect_introduction/title"}{/s}');
        me.getNavigation().hide();
    },

    loadUpdateListing: function(callback) {
        var me = this,
            navigation = me.getNavigation(),
            updatePage = me.getUpdatePage();

        updatePage.listing.resetListing();

        updatePage.updateStore.load({
            callback: function(records, operation, success) {
                if (operation.response && operation.response.responseText) {
                    var result = Ext.JSON.decode(operation.response.responseText);
                    if (result.loginRecommended) {
                        Shopware.app.Application.fireEvent('open-login', function() {});
                    }
                }

                if (records) {
                    navigation.setUpdateCount(records.length);
                }

                if (Ext.isFunction(callback)) {
                    callback(records);
                }
            }
        });
    },

    afterPluginManagerLoaded: function() {
        var me = this,
            localListing = me.getLocalListing();

        localListing.getStore().on('load', function(operation) {
            try {
                var data = operation.proxy.reader.rawData;
                if (data.error) {
                    Shopware.Notification.createGrowlMessage('', data.error);
                }
            } catch (e) {
            }
        });

        if (!Shopware.app.Application.sbpAvailable) {
            var navController = me.subApplication.getController('Navigation');
            navController.displayLocalPluginPage();
        }

        if (me.subApplication.action == 'PremiumPlugins') {
            Shopware.app.Application.fireEvent('display-premium-plugins');
            return;
        }
        if (me.subApplication.action == 'ExpiredPlugins') {
            Shopware.app.Application.fireEvent('display-expired-plugins');
        }
        if (me.subApplication.action == 'ShopwareConnect') {
            Shopware.app.Application.fireEvent('display-connect-introduction');

            return;
        }

        Ext.Function.defer(function () {
            localListing.getStore().load({
                callback: function(records) {
                    Shopware.app.Application.fireEvent('load-update-listing');
                }
            });
        }, 1000);
    },

    removeConnectIntroduction: function() {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller="PluginManager" action="disableConnectMenu"}',
            callback: function(options, success, response) {
                if (success) {
                    me.getListingWindow().close();
                    var connectMenu = Ext.ComponentQuery.query('mainmenu [iconCls=shopware-connect]')[0];
                    if (connectMenu) {
                        connectMenu.previousSibling().destroy();
                        connectMenu.nextSibling().nextSibling().destroy();
                        connectMenu.destroy();
                    }
                }
            }
        }, me);
    },

    installConnectIntroduction: function() {
        var me = this,
            plugin = Ext.create('Shopware.apps.PluginManager.model.Plugin', {
                technicalName: 'SwagConnect',
                iconPath: '{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/shopware_connect.png"}'
            });

        Shopware.app.Application.fireEvent('update-dummy-plugin', plugin, function(response) {
            if (response.success) {
                Shopware.app.Application.fireEvent('install-plugin', plugin, function(response) {
                    if (response.success) {
                        Shopware.app.Application.fireEvent('activate-plugin', plugin, function(response) {
                            me.getListingWindow().close();
                            Shopware.app.Application.addSubApplication({
                                name: 'Shopware.apps.Connect',
                                action: 'Register'
                            });
                        });
                    }
                }, me);
            }
        }, me);
    }
});
//{/block}