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
 * @package    PluginManager
 * @subpackage Controller
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Oliver Denter
 * @author     $Author$
 */

//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/controller/update"}
Ext.define('Shopware.apps.PluginManager.controller.Update', {

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
        { ref: 'storeNavigation', selector: 'plugin-manager-store-navigation' },
        { ref: 'pluginGrid', selector: 'plugin-manager-manager-grid' },
    ],

    snippets: {
   		update:{
   			title: '{s name=account/title}Plugin manager{/s}',
   			downloadsuccessful: '{s name=account/downloadsuccessful}Plugin was downloaded successfully{/s}',
   			downloadfailed: '{s name=account/downloadfailed}An error occurred while downloading the plugin. Please check the file permissions of the directories /files/downloads and engine/Shopware/Plugins/Community{/s}',
   			downloadfailedlicense: '{s name=account/downloadfailedlicense}An error occurred while downloading the plugin. Please check your directory-rights and license for this plugin.{/s}',
   			updatesuccessful: '{s name=account/updatesuccessful}Plugin [0] have been updated successfully{/s}',
   			updatefailed: '{s name=account/updatefailed}An error occurred while updating the plugin. Do you want to load the backup?{/s}',
            wantToStartUpdate: '{s name=update/wantToStartUpdate}You are about to update the plugin [0]. Do you want to proceed?{/s}'
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
        var me = this,
            updatePlugin;

        me.callParent(arguments);

        /**
         * Check if the plugin manager was invoked in order to update a given plugin
         *
         * {if $storeApiAvailable}
         */
        if (me.subApplication.params && me.subApplication.params.updatePlugin) {
            updatePlugin = me.subApplication.params.updatePlugin;

            Ext.MessageBox.confirm(me.snippets.update.title, Ext.String.format(me.snippets.update.wantToStartUpdate, updatePlugin), function(btn) {
                if(btn == 'yes') {
                    me.startPluginUpdate(updatePlugin);
                } else {
                    return false;
                }
            });
        }
        /** {/if} */
    },

    /**
     * Inits an update for a given plugin.
     *
     * @param updatePlugin
     */
    startPluginUpdate: function(updatePlugin) {
        var me = this,
            accountController = me.subApplication.getController('Account');

        // Check if user is logged in into the store and then triggers the first update step
        if(!accountController.checkLogin()) {
            accountController.onOpenLogin({
               controller: 'Update',
               action: 'doAutoUpdateFirstStep',
               params: updatePlugin
           });
        } else {
            me.doAutoUpdateFirstStep(updatePlugin);
        }
    },

    /**
     * Performs the actual plugin download
     *
     * In order to update a plugin, two steps are required
     *
     * 1. The new version of the plugin needs to be downloaded
     * 2. The new version of the plugin needs to be installed
     *
     * Currently this cannot be done in one request, as we need to create a new instance of the new plugin
     *
     * @param updatePlugin
     */
    doAutoUpdateFirstStep: function(updatePlugin) {
        var me = this;

        var window = me.getMainWindow();

        if (window) {
            window.setLoading(true);
        }

        Ext.Ajax.request({
            url:'{url controller="PluginManager" action="downloadPluginByName"}',
            method: 'POST',
            params: {
                name: updatePlugin
            },
            callback: function(request, opts, operation) {
                var response = Ext.decode(operation.responseText);

                if (window) {
                    window.setLoading(false);
                }

                if (response.success === true) {
                    Shopware.Notification.createGrowlMessage(
                       me.snippets.update.title,
                       me.snippets.update.downloadsuccessful
                    );

                    me.onAutoUpdateSecondStep(updatePlugin, response.articleId, response.activated, response.installed, response.availableVersion);

                } else {
                    var message = response.message + '';
                    if (message.length === 0) {
                        message = me.snippets.update.downloadfailedlicense
                    }
                    Shopware.Notification.createStickyGrowlMessage({
                       title: me.snippets.update.title,
                       text: message,
                       log: true
                    });
                }
            }
        });
    },

    /**
     * Performs the update of the new plugin
     *
     * In order to update a plugin, two steps are required
     *
     * 1. The new version of the plugin needs to be downloaded
     * 2. The new version of the plugin needs to be installed
     *
     * Currently this cannot be done in one request, as we need to create a new instance of the new plugin
     *
     * @param updatePlugin      The name of the plugin to update
     * @param articleId         The store's article id of the plugin
     * @param activated         Was the old plugin version activated?
     * @param installed         Was the olf plugin version installed?
     * @param availableVersion  Version to update to
     */
    onAutoUpdateSecondStep: function(updatePlugin, articleId, activated, installed, availableVersion) {
        var me = this;

        var window = me.getMainWindow();

        if (window) {
            window.setLoading(true);
        }

        Ext.Ajax.request({
            url:'{url controller="PluginManager" action="updatePlugin"}',
            method: 'POST',
            params: {
                name: updatePlugin,
                articleId: articleId,
                activated: activated,
                installed: installed,
                availableVersion: availableVersion
            },
            callback: function(request, opts, operation) {
                var response = Ext.decode(operation.responseText);

                if (window) {
                    window.setLoading(false);
                }

                if (response.success === true) {
                    Shopware.Notification.createGrowlMessage(
                       me.snippets.update.title,
                       Ext.String.format(me.snippets.update.updatesuccessful, updatePlugin)
                    );
                    me.filterForPlugin(updatePlugin);
                } else {
                    var message = response.message + '';
                    if (message.length === 0) {
                        message = me.snippets.update.downloadfailedlicense
                    }
                    Shopware.Notification.createStickyGrowlMessage({
                       title: me.snippets.update.title,
                       text: message,
                       log: true
                    });
                }
            }
        });
    },


    /**
     * Activates the "installed plugin" tab and searches for the updated plugin
     *
     * @param plugin
     */
    filterForPlugin: function(plugin) {
        var me = this, category = null, pluginStore = me.subApplication.pluginStore,
            mainWindow = me.getMainWindow(),
            navigation = me.getManagerNavigation(),
            grid       = me.getPluginGrid(),
            store      = navigation.extensionCategoryStore;

        // Set record active
        store.each(function(item) {
            if (item.raw.requestParam == null) {
                item.set('selected', true);
            } else {
                item.set('selected', false);
            }
        });

        if (navigation && navigation.accountCategoryStore instanceof Ext.data.Store) {
            var accountCategoryStore = navigation.accountCategoryStore;
            accountCategoryStore.each(function(item) {
               item.set('selected', false);
            });
        }

        var items = mainWindow.managerContainer.items,
            length = items.length;

        mainWindow.managerContainer.getLayout().setActiveItem(0);
        if(length > 1) {
            items.getAt(length-1).destroy();
        }


        if (grid && grid.searchField) {
            grid.searchField.setValue(plugin);
        }
    }

});
//{/block}
