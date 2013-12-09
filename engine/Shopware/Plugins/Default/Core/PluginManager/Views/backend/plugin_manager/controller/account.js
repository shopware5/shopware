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

//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/controller/account"}
Ext.define('Shopware.apps.PluginManager.controller.Account', {

    /**
     * The parent class that this class extends.
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * References for the controller for easier accessing.
     * @array
     */
    refs: [
        { ref: 'mainWindow', selector: 'plugin-manager-main-window' },
        { ref: 'managerNavigation', selector: 'plugin-manager-manager-navigation' },
        { ref: 'storeNavigation', selector: 'plugin-manager-store-navigation' }
    ],

    licenseViewCreated: false,

	snippets: {
		account:{
			title: '{s name=account/title}Plugin manager{/s}',
			downloadsuccessful: '{s name=account/downloadsuccessful}Plugin was downloaded successfully{/s}',
			downloadfailed: '{s name=account/downloadfailed}An error occurred while downloading the plugin. Please check the file permissions of the directories /files/downloads and engine/Shopware/Plugins/Community{/s}',
			downloadfailedlicense: '{s name=account/downloadfailedlicense}An error occurred while downloading the plugin. Please check your directory-rights and license for this plugin.{/s}',
			updatesuccessful: '{s name=account/updatesuccessful}Plugin [0] have been updated successfully{/s}',
			updatefailed: '{s name=account/updatefailed}An error occurred while updating the plugin. Do you want to load the backup?{/s}',
			backupsuccessful: '{s name=account/backupsuccessful}Backup loaded successfully{/s}',
			backupfailed: '{s name=account/backupfailed}Backup could not be loaded!{/s}',
			loginfailed: '{s name=account/loginfailed}Login failed{/s}'
		}
	},

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.control({
            'plugin-manager-account-login-window': {
                'login': me.onDoLogin
            },
            'plugin-manager-account-login-window textfield': {
                specialkey: function(field, event) {
                    var win = field.up('window'),
                        form = win.down('form'),
                        targetParams = win.targetParams;

                    if(event.getKey() !== event.ENTER) {
                        return false;
                    }
                    me.onDoLogin(win, form, targetParams);
                }
            },

            'plugin-manager-manager-grid': {
                updateDummyPlugin: me.onUpdateDummyPlugin
            },

            'plugin-manager-manager-navigation': {
                'openAccount': me.onOpenAccount,
                'openLicense': me.onOpenLicense,
                'openUpdates': me.onOpenUpdates
            },
            'plugin-manager-store-navigation': {
                'openAccount': me.onOpenAccount,
                'openLicense': me.onOpenLicense,
                'openUpdates': me.onOpenUpdates
            },
            'plugin-manager-account-licenses': {
                downloadplugin: me.onDownloadPlugin
            },
            'plugin-manager-account-updates': {
                updateplugin: me.onUpdatePlugin
            }
        });
    },

    onDownloadPlugin: function(grid, rowIndex, colIndex, item, eOpts, record) {
		var me = this;

        if (!record || record.get('download').length === 0) {
            return;
        }

		if(grid) {
			grid.setLoading(true);
		}

        Ext.Ajax.request({
            url:'{url controller="Store" action="download"}',
            method: 'POST',
            params: {
                url: record.get('download')
            },
            callback: function(request, opts, operation) {
                var response = Ext.decode(operation.responseText),
                    message;

				if(grid) {
					grid.setLoading(false);
				}

                if (response.success) {
                    Shopware.Notification.createGrowlMessage(me.snippets.account.title, me.snippets.account.downloadsuccessful);
                } else {
                    message = me.snippets.account.downloadfailed;
                    if (response.message) {
                        message = Ext.String.format(message, ':<br>' + response.message + '<br>')
                    } else {
                        message = Ext.String.format(message, ' ');
                    }
                    Shopware.Notification.createStickyGrowlMessage({
                       title: me.snippets.account.title,
                       text: message,
                       log: true
                    });
                }
            }
        });
    },

    onUpdateDummyPlugin: function(grid, rowIndex, colIndex, item, eOpts, record) {
        var me = this;
        var window = me.getMainWindow();

        if (window) {
            window.setLoading(true);
        }

        Ext.Ajax.request({
            url:'{url controller="PluginManager" action="downloadDummy"}',
            method: 'POST',
            params: {
                name: record.get('name')
            },
            callback: function(request, opts, operation) {
                var response = Ext.decode(operation.responseText);

                if (window) {
                    window.setLoading(false);
                }

                if (response.success === true) {
                       var pluginStore = me.subApplication.pluginStore;
                       pluginStore.load({
                           callback: function(records, operation, success) {
                               Ext.Array.each(records, function(localRecord) {
                                   if (record.get('id') == localRecord.get('id')) {
                                       var controller = me.getController('Manager');

                                       localRecord.set('wasActivated', 0);
                                       localRecord.set('wasInstalled', 0);
                                       localRecord.set('installed', new Date());
                                       localRecord.set('capabilityDummy', true);

                                       controller.onInstallPlugin(localRecord, me.subApplication.pluginStore);
                                   }
                               });
                               // do something after the load finishes
                           }
                       });
                } else {
                    var message = response.message + '';
                    if (message.length === 0) {
                        message = me.snippets.account.downloadfailedlicense;
                    }
                    Shopware.Notification.createStickyGrowlMessage({
                       title: me.snippets.account.title,
                       text: message,
                       log: true
                    });
                }
            }
        });
    },

    onUpdatePlugin: function(grid, rowIndex, colIndex, item, eOpts, record) {
        var me = this;
        var window = me.getMainWindow();

        if (window) {
            window.setLoading(true);
        }

        Ext.Ajax.request({
            url:'{url controller="PluginManager" action="downloadUpdate"}',
            method: 'POST',
            params: {
                ordernumber: record.get('ordernumber'),
                articleId: record.get('articleId'),
                name: record.get('name')
            },
            callback: function(request, opts, operation) {
                var response = Ext.decode(operation.responseText);

                if (window) {
                    window.setLoading(false);
                }

                if (response.success === true) {
                    if (response.activated) {
                        record.set('wasActivated', 1);
                    } else {
                        record.set('wasActivated', 0);
                    }
                    if (response.installed)  {
                        record.set('wasInstalled', 1);
                    } else {
                        record.set('wasInstalled', 0);
                    }

                    me.refreshPluginList(record);
                } else {
                    var message = response.message + '';
                    if (message.length === 0) {
                        message = me.snippets.account.downloadfailedlicense
                    }
                    Shopware.Notification.createStickyGrowlMessage({
                       title: me.snippets.account.title,
                       text: message,
                       log: true
                    });
                }
            }
        });
    },

    refreshPluginList: function(record) {
        var me = this;

        Ext.Ajax.request({
            url:'{url controller="PluginManager" action="refreshPluginList"}',
            method: 'POST',
            callback: function(request, opts, operation) {
                if (record) {
                    var response = Ext.decode(operation.responseText);
                    me.updatePlugin(record);
                }
            }
        });
    },

    updatePlugin: function(record) {
        var me = this;

        Ext.Ajax.request({
            url:'{url controller="PluginManager" action="updatePlugin"}',
            method: 'POST',
            params: {
                name: record.get('name'),
                availableVersion: record.get('availableVersion'),
                activated: record.get('wasActivated'),
                installed: record.get('wasInstalled')
            },
            callback: function(request, opts, operation) {
                var response = Ext.decode(operation.responseText);

               if (response.success) {
                   if (record.get('capabilityDummy')) {
//                       var pluginStore = me.subApplication.pluginStore;
//                       pluginStore.load({
//                           callback: function(records, operation, success) {
//                               Ext.Array.each(records, function(localRecord) {
//                                   if (record.get('id') == localRecord.get('id')) {
//                                       var controller = me.getController('Manager');
//
//                                       console.log("Updated record", localRecord.data);
//
//
//                                       localRecord.set('installed', new Date());
//                                       localRecord.set('capabilityDummy', false);
//
//                                       controller.onInstallPlugin(localRecord, me.subApplication.pluginStore);
//                                   }
//                               });
//                               // do something after the load finishes
//                           }
//                       });
                   }

                   var message = Ext.String.format(me.snippets.account.updatesuccessful, record.get('name'));
                    Shopware.Notification.createGrowlMessage(me.snippets.account.title, message);
                    if (response.configRequired) {
                        var plugin = me.subApplication.pluginStore.getById(record.get('pluginId'));
                        me.subApplication.getController('Manager').onEditPlugin(null, null, null, null, null, plugin);
                    }
                    if (response.invalidateCache) {
                        var managerCtl = me.subApplication.getController('Manager');
                        managerCtl.displayCacheClearMessage(response.invalidateCache, record);
                    }
                } else {
                    Ext.MessageBox.confirm(me.snippets.account.title, me.snippets.account.updatefailed, function(btn) {
                        if(btn == 'yes') {
                            me.restorePluginBackup(record);
                        } else {
                            return false;
                        }
                    });
                }
            }
        });
    },

    restorePluginBackup: function(record) {
        var me = this;
        Ext.Ajax.request({
            url:'{url controller="PluginManager" action="restorePlugin"}',
            method: 'POST',
            params: {
                name: record.get('name'),
                activated: record.get('wasActivated'),
                installed: record.get('wasInstalled'),
                version: record.get('currentVersion')
            },
            callback: function(request, opts, operation) {
                var response = Ext.decode(operation.responseText);
                if (response.success) {
                    Shopware.Notification.createGrowlMessage(me.snippets.account.title, me.snippets.account.backupsuccessful);
                    me.refreshPluginList(null);
                } else {
                    if (response.message) {
                        Shopware.Notification.createGrowlMessage(me.snippets.account.title, response.message);
                    } else {
                        Shopware.Notification.createStickyGrowlMessage({
                           title: me.snippets.account.title,
                           text: me.snippets.account.backupfailed,
                           log: true
                        });
                    }
                }
            }
        });
    },

    onOpenLogin: function(targetParams) {
        var me = this;
        me.getView('account.LoginWindow').create({ targetParams: targetParams, account: me.subApplication.myAccount });
    },

    onDoLogin: function(view, formPanel, targetParams) {
        var me = this,
            values = formPanel.getValues();

        if (!formPanel.getForm().isValid()) {
            return;
        }
        view.setLoading(true);
        me.subApplication.myAccount = Ext.create('Shopware.apps.PluginManager.model.Account', values);
        me.subApplication.myAccount.save({
            success: function() {
                view.setLoading(false);
                view.destroy();
                var storeCtl = me.subApplication.getController('Store');
                var ctl = me.subApplication.getController(targetParams.controller);
                if(targetParams.action == 'sendBuyRequest') {
                    ctl.sendBuyRequest(targetParams.record, targetParams.detail);
                } else if(targetParams.action == 'sendTaxRequest') {
                    ctl.sendTaxRequest(targetParams.record, targetParams.detail);
                } else if(targetParams.action == 'onOpenLicense') {
                    ctl.onOpenLicense(targetParams.view, targetParams.record);
                } else if(targetParams.action == 'onOpenUpdates') {
                    ctl.onOpenUpdates(targetParams.view, targetParams.record);
                // At last allow more dynamic callbacks
                } else if(targetParams.action) {
                    ctl[targetParams.action](targetParams.params);
                }
                me.subApplication.licencedProductStore.load();
                me.subApplication.updatesStore.load();
                storeCtl.refreshAccountNavigation();
            },
            failure: function(records, operation) {
                var rawData = operation.records[0].getProxy().reader.rawData;
                view.setLoading(false);
                if (rawData.message) {
                    Shopware.Notification.createGrowlMessage(me.snippets.account.title, rawData.message);
                } else {
                    Shopware.Notification.createStickyGrowlMessage({
                       title: me.snippets.account.title,
                       text: me.snippets.account.loginfailed,
                       log: true
                    });
                }
            }
        });
    },

    onOpenConfirm: function(price, record, detail) {
        var me = this;
        me.getView('account.Confirm').create({
            price: price,
            record: record,
            detail: detail
        });
    },

    onOpenAccount: function(view, record) {
        var me = this;
        if(me.subApplication.myAccount.get('accountUrl')) {
            window.open(me.subApplication.myAccount.get('accountUrl'));
        } else {
            window.open('https://account.shopware.de');
        }
    },
    onOpenLicense: function(view, record) {
        var me = this,
            mainWindow = me.getMainWindow(),
            activeTab = mainWindow.tabPanel.getActiveTab(),
            navigation,
            container;

        if(!me.checkLogin()) {
            me.onOpenLogin({
                controller: 'Account',
                action: 'onOpenLicense',
                record: record,
                view: view
            });
            return false;
        }

        // Set selected record
        var store = view.store;
        store.each(function(item) {
            item.set('selected', false);
        });
        record.set('selected', true);

        if(activeTab.initialTitle === 'manager') {
            navigation = me.getManagerNavigation();
            var store = navigation.extensionCategoryStore;
            store.each(function(item) {
                item.set('selected', false);
            });
            container = mainWindow.managerContainer;
        } else {
            navigation = me.getStoreNavigation();
            var store = navigation.categoryStore;
            store.each(function(item) {
                item.set('selected', false);
            });
            container = mainWindow.storeContainer;
        }

        if(container.items.getCount() > 1) {
            container.items.getAt(container.items.getCount() -1).destroy();
        }
        if (me.subApplication.licencedProductStore.getCount() === 0) {
            me.subApplication.licencedProductStore.load();
        }
        var view = me.getView('account.Licenses').create({
            licensedStore: me.subApplication.licencedProductStore
        });
        container.add(view);
        container.getLayout().setActiveItem(1);
    },

    onOpenUpdates: function(view, record) {
        var me = this;
        if(!me.checkLogin()) {
           me.onOpenLogin({
               controller: 'Account',
               action: 'onOpenUpdates',
               record: record,
               view: view
           });
           return false;
        }

        var managerNavigation = me.getManagerNavigation().accountCategoryStore,
            storeNavigation = me.getStoreNavigation().accountCategoryStore,
            stores = [ managerNavigation, storeNavigation ],
            mainWindow = me.getMainWindow(),
            activeTab = mainWindow.tabPanel.getActiveTab(),
            container;


        Ext.each(stores, function(store) {
            var storeRecord = store.getAt(store.getCount()-1);
            storeRecord.set('badge', record.get('badge'));
        });

        if(activeTab.initialTitle === 'manager') {
            var store = me.getManagerNavigation().extensionCategoryStore;
            store.each(function(item) {
                item.set('selected', false);
            });
            container = mainWindow.managerContainer;
        } else {
            var store = me.getStoreNavigation().categoryStore;
            store.each(function(item) {
                item.set('selected', false);
            });
            container = mainWindow.storeContainer;
        }

        // Set selected record
        var store = view.store;
        store.each(function(item) {
            item.set('selected', false);
        });
        record.set('selected', true);

        if(container.items.getCount() > 1) {
            container.items.getAt(container.items.getCount() -1).destroy();
        }
        var view = me.getView('account.Updates').create({
            updatesStore: me.subApplication.updatesStore
        });

        container.add(view);
        container.getLayout().setActiveItem(1);

    },

    checkLogin: function() {
        var me = this,
            record = me.subApplication.myAccount;

        return record.get('shopwareID').length || record.get('account_id').length;
    }
});
//{/block}
