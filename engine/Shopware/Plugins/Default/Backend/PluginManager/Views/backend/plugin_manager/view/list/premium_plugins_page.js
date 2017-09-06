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
 * @subpackage List
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/plugin_manager/translation}

//{block name="backend/plugin_manager/view/list/premium_plugins_page"}
Ext.define('Shopware.apps.PluginManager.view.list.PremiumPluginsPage', {
    extend: 'Shopware.apps.PluginManager.view.list.StoreListingPage',
    alias: 'widget.plugin-manager-premium-plugins-page',

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    initComponent: function() {
        var me = this;

        me.callParent(arguments);

        Shopware.app.Application.on('plugin-reloaded', function(plugin) {
            me.communityStore.each(function(record, index) {
                if (record && record.get('technicalName') === plugin.get('technicalName')) {
                    me.communityStore.remove(record);
                }
            });

            if (plugin.get('id') > 0) {
                plugin.set('groupingState', null);
                plugin.dirty = false;
                try {
                    me.communityStore.add(plugin);
                } catch (e) {
                    me.communityStore.load();
                }
            }

            me.communityStore.sort();
            me.communityStore.group();
            me.hideLoadingMask();
        });
    },

    createStoreListing: function() {
        var me = this;

        var content = me.callParent(arguments);

        me.communityStore.filter({ property: "premium", value: true });
        me.communityStore.load();

        return content;
    },

    createListing: function() {
        var me = this;

        var listing = me.callParent(arguments);

        listing.addItems = function(records) {
            var self = this, plugins = [];

            Ext.each(records, function (record) {
                plugins.push(me.createListItem(record));
            });

            self.listingContainer.add(plugins);
        };


        return listing;
    },

    createFilterPanel: function() {
        return Ext.create('Ext.container.Container', {
            border: false,
            items: [
                Ext.create('Ext.container.Container', {
                    html: '{s name="premium_plugins/headline"}Shopware Premium Plugins - Try for free!{/s}',
                    cls: 'headline',
                    padding: '30 30 0 30'
                }),
                Ext.create('Ext.container.Container', {
                    html: '{s name="premium_plugins/description_text"}Try our premium plugins 30 days free of charge and without obligation.{/s}',
                    padding: '10 30 0 30'
                })
            ]
        });
    },

    createListItem: function(record) {
        var me = this;

        return Ext.create('PluginManager.components.StorePlugin', {
            record: record,
            onClickElement: function(record) {
                var me = this;
                me.displayPluginEvent(record, function(detailWindow) {
                    detailWindow.setActivePriceTab('test');
                });
            },
            createButton: function() {
                var me = this,
                         cls,
                         text,
                         handlerCallback = function() {
                            me.displayPluginEvent(record, function(detailWindow) {
                                detailWindow.setActivePriceTab('test');
                            });
                        };

                switch(true) {
                    case record.allowUpdate():
                        return Ext.create('PluginManager.container.Container', {
                            cls: 'button update',
                            html: '{s name="update_plugin"}Update{/s}',
                            handler: function() {
                                me.updatePluginEvent(record);
                            }
                        });

                    case record.allowInstall():
                        return Ext.create('PluginManager.container.Container', {
                            cls: 'button install',
                            html: '{s name="install"}Install{/s}',
                            handler: function() {
                                me.registerConfigRequiredEvent(record);
                                me.installPluginEvent(record);
                            }
                        });

                    case record.allowActivate():
                        return Ext.create('PluginManager.container.Container', {
                            cls: 'button activate',
                            html: '{s name="activate"}Activate{/s}',
                            handler: function() {
                                me.activatePluginEvent(record);
                            }
                        });

                    case record.allowConfigure():
                        return Ext.create('PluginManager.container.Container', {
                            cls: 'button configure',
                            html: '{s name="configure"}Configure{/s}',
                            handler: handlerCallback
                        });

                    case record.isAdvancedFeature():
                    case record.isLocalPlugin():
                        return Ext.create('PluginManager.container.Container', {
                            cls: 'button locale',
                            html: '{s name="open"}Open{/s}',
                            handler: handlerCallback
                        });
                }


                return Ext.create('PluginManager.container.Container', {
                    cls: 'button configure',
                    html: '{s name="premium_plugins/try_button"}Try now{/s}',
                    handler: handlerCallback
                });
            }
        });
    }

});
//{/block}