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
 * @author     Stephan Pohl
 * @author     $Author$
 */

/**
 *
 */
//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/controller/manager"}
Ext.define('Shopware.apps.PluginManager.controller.Manager', {

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
        { ref: 'pluginGrid', selector: 'plugin-manager-manager-grid' },
        { ref: 'mainWindow', selector: 'plugin-manager-main-window' },
        { ref: 'managerNavigation', selector: 'plugin-manager-manager-navigation' }
    ],

	snippets: {
		manager: {
			title: '{s name=manager/title}Plugin manager{/s}',
			successful_delete: '{s name=manager/successful_delete}Plugin have been deleted successfully{/s}',
			failed_delete: '{s name=manager/failed_delete}Plugin could not be deleted{/s}',
            deleteMessage: '{s name=manager/delete_message}Are you sure you want to delete the selected plugin?{/s}',
			failed_edit: '{s name=manager/failed_edit}Plugin details could not be loaded from [0]{/s}',
			successful_install: '{s name=manager/successful_install}Plugin [0] have been installed successfully{/s}',
			failed_install: '{s name=manager/failed_install}Plugin [0] could not be installed{/s}',
			clear_cache: '{s name=manager/clear_cache}This plugin needs a new initialise in the following caches: [0]Clear cache?{/s}',
			clear_cache_successful: '{s name=manager/clear_cache_successful}Shop cache cleared{/s}',
			clear_cache_failed: '{s name=manager/clear_cache_failed}Shop cache could not be cleared{/s}',
			successful_uninstall: '{s name=manager/successful_uninstall}Plugin [0] have been uninstalled successfully{/s}',
			failed_uninstall: '{s name=manager/failed_uninstall}Plugin [0] could not be uninstalled{/s}',
			successful_upload: '{s name=manager/successful_upload}plugin was uploaded successfully{/s}',
			failed_upload_namespace: '{s name=manager/failed_upload_namespace}The Plugin is not in the specified format. The namespace could not be determined{/s}',
			failed_upload: '{s name=manager/failed_upload}An error occurred while uploading the plugin{/s}',
            data_not_available: '{s name=manager/data_not_available}No plugin community store data available{/s}'
		}
	},

    /**
     * A template method that is called when your application boots.
     * It is called before the Application's launch function is executed
     * so gives a hook point to run any code before your Viewport is created.
     *
     * @returns { Void }
     */
    init: function () {
        var me = this;

        me.control({
            'plugin-manager-manager-options': {
                'activatePlugin': me.onActivatePlugin,
                'configurePlugin': me.onConfigurePlugin
            },
            'plugin-manager-manager-navigation': {
                'changeCategory': me.onChangeCategory
            },
            'plugin-manager-manager-grid': {
                'updatePluginInfo' : me.onUpdatePluginInfo,
                'search': me.onSearchPlugin,
                'editPlugin': me.onEditPlugin,
                'edit': me.onAfterCellEditing,
                'uninstallInstall': me.onInstallUninstallPlugin,
                'reinstallPlugin': me.onReinstallPlugin,
                'manualInstall': me.onOpenManualInstallWindow,
                'itemdblclick': me.onDblClick,
                'beforeedit': me.onBeforeEdit,
                'deleteplugin': me.onDeletePlugin
            },
            'plugin-manager-manager-manual-install': {
                'uploadPlugin': me.onUploadPlugin
            },
            'plugin-manager-detail-window': {
                'saveConfiguration': me.onSaveConfiguration,
                'pluginTabChanged': me.onPluginTabChanged
            }
        });
    },

    /**
     * Event listener function which is fired when the user change
     * the tab of the plugin detail page.
     *
     * This function loads the plugin store data for the specify plugin
     *
     * @param detailWindow
     * @param tabPanel
     * @param newCard
     * @param oldCard
     */
    onPluginTabChanged: function(detailWindow, tabPanel, newCard, oldCard) {
        var me = this;

        if (newCard.name == 'product-wrapper' && detailWindow.productWrapper) {
            tabPanel.setLoading(true);
            var store = me.getStore('Product');
            store.getProxy().extraParams.pluginId = detailWindow.plugin.get('id');
            store.load({
                callback: function(products, operation) {
                    if (!(operation.wasSuccessful()) || !(products[0] instanceof Ext.data.Model)) {
                        detailWindow.productWrapper.removeAll();
                        var container = Ext.create('Ext.container.Container', {
                            padding: 20,
                            items: [ Shopware.Notification.createBlockMessage(me.snippets.manager.data_not_available, 'notice') ]
                        });
                        detailWindow.productWrapper.add(container);
                    } else {
                        var product = products[0];
                        detailWindow.voteStore.getProxy().extraParams.productId = product.get('id');
                        detailWindow.voteStore.load();

                        detailWindow.productWrapper.removeAll();
                        detailWindow.productWrapper.add({
                            xtype: 'plugin-manager-detail-description',
                            article: product,
                            voteStore: detailWindow.voteStore
                        });
                    }

                    tabPanel.setLoading(false);
                }
            });
        }
    },


    /**
     * Event listener method which sets the plugin active.
     *
     * @param { Ext.window.Window } optionWindow
     * @returns { Void }
     */
    onActivatePlugin: function(optionWindow) {
        var me = this,
            record = optionWindow.record,
            store = me.subApplication.pluginStore;

        record.set('active', true);
        record.save();
        store.sort();
        optionWindow.destroy();
    },

    /**
     * Event listener method which will be fired when the user wants to edit
     * the plugins configuration.
     *
     * @param { Ext.window.Window } optionWindow
     * @returns { Boolean|Void } Falsy if the plugin isn't installed, otherwise void.
     */
    onConfigurePlugin: function(optionWindow) {
        var record = optionWindow.record;

        optionWindow.destroy();
        if(record.get('installed') == null) {
            return false;
        }
        this.editPlugin(record);
    },

    /**
     * Event listener method which will be fired when the user clicks the delete
     * icon in the plugin list.
     *
     * @param { Ext.grid.Panel } grid
     * @param { Integer } rowIndex
     * @param { Integer } colIndex
     * @param { HTMLDOMNode } item
     * @param { Ext.EventImpl } eOpts
     * @param { Ext.data.Record } record
     * @returns { Void }
     */
    onDeletePlugin: function(grid, rowIndex, colIndex, item, eOpts, record) {
        var me = this, confirmMessage;

        if (record && record.get('id')) {
            Ext.MessageBox.confirm(me.snippets.manager.title, me.snippets.manager.deleteMessage, function(btn) {
                if(btn == 'yes') {
                    record.destroy({
                        callback: function(record, operation) {
                           if (operation.wasSuccessful()) {
                               Shopware.Notification.createGrowlMessage(me.snippets.manager.title, me.snippets.manager.successful_delete);
                               me.getPluginGrid().getStore().load();
                           } else {
                               var message = '';
                               var rawData = operation.records[0].getProxy().reader.rawData;
                               if (rawData.message) {
                                   message = '<br>' + rawData.message;
                               }

                               Shopware.Notification.createStickyGrowlMessage({
                                   title: me.snippets.manager.title,
                                   text: me.snippets.manager.failed_delete + message,
                                   log: true
                               });
                           }
                        }
                    });
                }
            });
        }
    },

    /**
     * Event listener method which will be triggered when the user
     * clicks on the active column to edit a specifc row.
     *
     * If the plugin is installed the method allows the user to
     * edit the cell, otherwise the editing operation will be canceled.
     *
     * @public
     * @event beforeedit
     * @param [object] grid - Shopware.apps.PluginManager.view.manager.Grid
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin
     * @return [boolean] - Truthy if editing is allowed, falsy if editing isn't allowed.
     */
    onBeforeEdit: function(grid, params) {
        if (!params.record.get('capabilityEnable')) {
            return false;
        }
        return params.record.get('installed') != null;
    },

    /**
     * Event listener method which will be trigged when the user selects a new
     * category in the navigation panel.
     *
     * Deselects all other navigation and sets an extra parameter to the plugin store.
     *
     * @public
     * @event changeCategory
     * @param [object] view - Shopware.apps.PluginManager.view.manager.Navigation
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin
     * @param [object] dom - HTML DOM node of the clicked element
     * @return void
     */
    onChangeCategory: function(view, record, dom) {
        var me = this, store = view.store, category, pluginStore = me.subApplication.pluginStore,
            mainWindow = me.getMainWindow(),
            navigation = me.getManagerNavigation();

        // Set record active
        store.each(function(item) {
            item.set('selected', false);
        });
        record.set('selected', true);

        if (navigation && navigation.accountCategoryStore instanceof Ext.data.Store) {
            var accountCategoryStore = navigation.accountCategoryStore;
            accountCategoryStore.each(function(item) {
               item.set('selected', false);
            });
        }

        // Terminate the category
        category = dom.getAttribute('data-action');
        if(category === 'null') {
            category = null;
        }

        var items = mainWindow.managerContainer.items,
            length = items.length;

        mainWindow.managerContainer.getLayout().setActiveItem(0);
        if(length > 1) {
            items.getAt(length-1).destroy();
        }

        pluginStore.getProxy().extraParams = { category: category };
        pluginStore.load();
    },

    /**
     * Event listener method which will be triggered when the user changes
     * the value of the search field in the grid (upper right corner).
     *
     * Filters the plugin store with the typed value.
     *
     * @public
     * @event change
     * @param [object] field - Ext.form.field.Text
     * @param [string] value - typed value of the user
     * @return void
     */
    onSearchPlugin: function(field, value) {
        var me = this, pluginStore = me.subApplication.pluginStore

        pluginStore.filters.clear();
        pluginStore.getProxy().extraParams = { category: null };
        pluginStore.filter({ property: 'free', value: value });
    },

    /**
     * Event listener method which will be trigged when the user double clicks
     * a row in the plugin grid.
     *
     * Checks if the plugin is installed. If truthy the detail page of the plugin
     * will be called. If falsy the operation will canceled.
     *
     * @public
     * @event itemdblclick
     * @param [object] grid - Shopware.apps.PluginManager.view.manager.Grid
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin
     * @param [object] item - HTML DOM node of the clicked element
     * @param [object] event - Ext.EventImplObj
     * @param [object] eOpts - additional event parameter
     * @return [boolean]
     */
    onDblClick: function(grid, record, item, event, eOpts) {
        if(record.get('installed') == null) {
            return false;
        }
        this.editPlugin(record);

    },

    /**
     * Event listener method which will be triggered when the user clicks on the
     * action column with the iconCls "sprite-pencil".
     *
     * This method loads the detail store and opens the detail page of the associated
     * plugin.
     *
     * @event click
     * @param [object] grid - Shopware.apps.PluginManager.view.manager.Grid
     * @param [integer] rowIndex - index of the clicked row
     * @param [integer] colIndex - index of the clicked column
     * @param [object] item - HTML DOM node of the clicked element
     * @param [object] eOpts - additional event parameter
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin
     */
    onEditPlugin: function(grid, rowIndex, colIndex, item, eOpts, record) {
        this.editPlugin(record);
    },


    /**
     * Helper function to open the plugin detail page with the whole
     * community store product data.
     * If you want to set the active flag of the passsed plugin, set the
     * active parameter to true or false.
     * @param record
     * @param active
     */
    editPlugin: function(record, active) {
        var me = this;

        if (!record) {
            return;
        }

        var store = me.getStore('Detail');
        store.getProxy().extraParams.pluginId = record.get('id');
        store.load({
            callback: function(records, operation) {
                if (operation.wasSuccessful()) {
                    var plugin = records[0];

                    if (active === false || active === true) {
                        plugin.set('active', active);
                    }

                    me.getView('detail.Window').create({
                        plugin: plugin,
                        voteStore: me.getStore('Votes'),
                        flag: 'local'
                    });
                } else {
                    Shopware.Notification.createStickyGrowlMessage({
                        title: me.snippets.manager.title,
                        text: Ext.String.format(me.snippets.manager.failed_edit, record.get('label'))
                    });
                }
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user clicks on the
     * action column with the iconCls "sprite-plus-circle" or "sprite-minus-circle".
     *
     * Helper method which terminates if the user wants to install or deinstall a plugin.
     *
     * @public
     * @event click
     * @param [object] view - Shopware.apps.PluginManager.view.manager.Grid
     * @param [object] actionColumn - HTML DOM node of the clicked element
     * @param [object] event - Ext.EventImplObj
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin
     * @return void
     */
    onInstallUninstallPlugin: function(view, actionColumn, event, record) {
        var me = this, pluginStore = me.subApplication.pluginStore;

        if(record.get('installed') === null) {
            record.set('installed', new Date());
        } else {
            record.set('installed', null);
        }
        me.onInstallPlugin(record, pluginStore);
    },

    onUpdatePluginInfo: function(record, store) {
        var me = this;
        var listing = me.getPluginGrid();

        if (listing) {
            listing.setLoading(true);
        }
        record.save({
            callback: function(record, operation) {
                if (listing) {
                    listing.setLoading(false);
                }
                store.sort();
            }
        });
    },

    /**
     * Helper function to reinstall a plugin with one click
     * @param record
     * @param grid
     */
    onReinstallPlugin: function(record, grid) {
        var me = this, active = record.get('active');

        record.set('installed', null);
        me.onInstallPlugin(record, me.subApplication.pluginStore, {
            callback: function() {
                record.set('active', active);
                record.set('installed', new Date());
                me.onInstallPlugin(record, me.subApplication.pluginStore);
            }
        });
    },

    /**
     * Installs a plugin based on the passed record and the associated store.
     *
     * @public
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin
     * @param [object] store - Shopware.apps.PluginManager.store.Plugin
     * @param [object] options - Helper parameter for callback functions.
     * @return void
     */
    onInstallPlugin: function(record, store, options) {
        var me = this;

        var listing = me.getPluginGrid();
        if (listing) {
            listing.setLoading(true);
        }

        var isDummy = record.get('capabilityDummy');

        record.save({
           callback: function(record, operation) {
               var rawData = null,
                   result = operation.records[0];

               if (isDummy) {
                   // update version on-the-fly
                   record.set('version', record.get('updateVersion'));
                   record.set('updateVersion', null);
                   record.set('capabilityDummy', false);
               }

               if (listing) {
                   listing.setLoading(false);
               }

               if (result instanceof Ext.data.Model
                       && operation.records[0].getProxy()
                       && operation.records[0].getProxy().reader
                       && operation.records[0].getProxy().reader.rawData) {

                   rawData = operation.records[0].getProxy().reader.rawData;
               }

               if(operation.wasSuccessful()) {

                   //uninstalled?
                   if (record.get('installed') === null) {
                       Shopware.Notification.createGrowlMessage(me.snippets.manager.title, Ext.String.format(me.snippets.manager.successful_uninstall, record.get('label')));
                   } else {
                       Shopware.Notification.createGrowlMessage(me.snippets.manager.title, Ext.String.format(me.snippets.manager.successful_install, record.get('label')));
                   }

                   //sort store to regroup the store records
                   store.sort();

                   if (rawData.invalidateCache) {
                      me.displayCacheClearMessage(rawData.invalidateCache, record);
                   }

                   if (record.get('installed') !== null) {
                       me.editPlugin(record, true);
                   }

                   if (options !== Ext.undefined && options !== null && Ext.isFunction(options.callback)) {
                       options.callback(record);
                   }

               } else {
                   var message = Ext.String.format(me.snippets.manager.failed_install, record.get('label'));

                   if (rawData.message) {
                       message = message + '<br>' + rawData.message;
                   }

                   Shopware.Notification.createStickyGrowlMessage({
                       title: me.snippets.manager.title,
                       text: message,
                       log: true
                   });
                   store.sort();
               }
           }
       });
    },

    displayCacheClearMessage: function(caches, record) {
        var me = this,
            message = Ext.String.format(me.snippets.manager.clear_cache, caches.join(', ') + '<br><br>');

        Ext.MessageBox.confirm('Plugin Manager', message, function(btn) {
            if(btn == 'yes') {
                var params = {};
                Ext.each(caches, function(cacheKey) {
                    params['cache[' + cacheKey + ']'] = 'on';
                });
                Ext.Ajax.request({
                    url:'{url controller="Cache" action="clearCache"}',
                    method: 'POST',
                    params: params,
                    callback: function(records, operation) {
                        if(operation) {
                            Shopware.Notification.createGrowlMessage(me.snippets.manager.title, me.snippets.manager.clear_cache_successful);
                        } else {
                            Shopware.Notification.createStickyGrowlMessage({
                               title: me.snippets.manager.title,
                               text: me.snippets.manager.clear_cache_failed,
                               log: true
                            });
                        }
                    }
                });
            } else {
                return false;
            }
        });
    },

    /**
     * Installs a plugin based on the passed record and the associated store.
     *
     * @public
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin
     * @param [object] store - Shopware.apps.PluginManager.store.Plugin
     * @return void
     */
    onUninstallPlugin: function(record, store) {
        var me = this;

        record.save({
           callback: function(record, operation) {
               var rawData = operation.records[0].getProxy().reader.rawData;

               if(operation.wasSuccessful()) {
                   Shopware.Notification.createGrowlMessage(me.snippets.manager.title, Ext.String.format(me.snippets.manager.successful_uninstall, record.get('label')));
               } else {
                   var message = Ext.String.format(me.snippets.manager.failed_uninstall, record.get('label'));
                   if (rawData.message) {
                       message = message + '<br>' + rawData.message;
                   }

                   Shopware.Notification.createStickyGrowlMessage({
                      title: me.snippets.manager.title,
                      text: message,
                      log: true
                   });
               }
               store.sort();
           }
       });
    },

    /**
     * Event listener method which will be triggered when the user
     * finished the cell editing of the "active" column.
     *
     * The method saves the changed record and sorts the store.
     *
     * @public
     * @event event
     * @param [object] editor - Ext.grid.plugin.CellEditing
     * @param [object] event - Ext.EventImplObj
     * @return void
     */
    onAfterCellEditing: function(editor, event) {
        var me = this,
            record = event.record,
            store = me.subApplication.pluginStore;

        record.save();
        store.sort();
    },

    /**
     * Event listener which will be triggered when the user clicks
     * on the manual install plugin button in the toolbar of the plugin
     * grid.
     *
     * The method simply opens the manual install window.
     *
     * @public
     * @event click
     * @return void
     */
    onOpenManualInstallWindow: function() {
        var me = this;

        me.getView('manager.ManualInstall').create({
            width: 400,
            height: 250
        });
    },

    /**
     * Event listener method which will be triggered when the user clicks on
     * the "upload plugin" button in the manual install window.
     *
     * Uploads the selected plugin from the local data system to the remote one
     * on the server side and checks if the request was successful.
     *
     * @param [object] win - Shopware.apps.PluginManager.view.manager.ManualInstall
     * @param [object] formPanel - Ext.form.Panel
     * @param [object] btn - Ext.button.Button
     * @return [boolean]
     */
    onUploadPlugin: function(win, formPanel, btn) {
        var me = this,
            form = formPanel.getForm();

        if(!form.isValid()) {
            return false;
        }
        form.submit({
            success: function(form, action) {
                Shopware.Notification.createGrowlMessage(me.snippets.manager.title, me.snippets.manager.successful_upload);
                win.close();
                var accountCtl = me.subApplication.getController('Account');
                accountCtl.refreshPluginList(null);
                me.getPluginGrid().getStore().load();
            },
            failure: function(form, action) {
                var response = Ext.decode(action.response.responseText);
                if (response.noNamespace) {
                    Shopware.Notification.createStickyGrowlMessage({
                       title: me.snippets.manager.title,
                       text: me.snippets.manager.failed_upload_namespace,
                       log: true
                    });
                } else {
                    var message = me.snippets.manager.failed_upload;
                    if (response.message) {
                        message = message + ':<br>' + response.message;
                    }

                    Shopware.Notification.createStickyGrowlMessage({
                       title: me.snippets.manager.title,
                       text: message,
                       log: true
                    });
                }
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user attemps
     * to save the plugin configuration.
     *
     * Saves the plugin configuration and resorts the store.
     *
     * @public
     * @event click
     * @param [object] view - Shopware.apps.PluginManager.view.detail.Settings
     * @return void
     */
    onSaveConfiguration: function(view) {
        var me = this,
            pluginForm = view.down('plugin-form-panel'),
            form = view.down('plugin-manager-detail-settings');

        view.plugin.set('active', form.checkbox.getValue());
        if(pluginForm) {
            pluginForm.onSaveForm(pluginForm, false, function() {
                view.plugin.save();
                me.subApplication.pluginStore.sort();
            });
        } else {
            view.plugin.save();
            me.subApplication.pluginStore.sort();
        }

        view.destroy();
    }
});
//{/block}
