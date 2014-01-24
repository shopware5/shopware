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
 * @subpackage View
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Stephan Pohl
 * @author     $Author$
 */

//{namespace name=backend/plugin_manager/main}
//{block name="backend/plugin_manager/view/manager/grid"}
Ext.define('Shopware.apps.PluginManager.view.manager.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.plugin-manager-manager-grid',
    border: 0,
    cls: Ext.baseCSSPrefix + 'plugin-manager-manager-grid',

    /**
     * Snippets for the component.
     * @object
     */
    snippets: {
		plugin_name: '{s name=manager/grid/plugin_name}Plugin name{/s}',
		supplier: '{s name=manager/grid/supplier}Supplier{/s}',
		license: '{s name=manager/grid/license}License{/s}',
		version: '{s name=manager/grid/version}Version{/s}',
		added: '{s name=manager/grid/added}Added on{/s}',
		active: '{s name=manager/grid/active}Active{/s}',
		inactive: '{s name=manager/grid/inactive}Inactive{/s}',
		actions: '{s name=manager/grid/actions}Action(s){/s}',
		edit_plugin: '{s name=manager/grid/edit_plugin}Edit plugin{/s}',
		install_plugin: '{s name=manager/grid/install_plugin}Install plugin{/s}',
		install_uninstall_plugin: '{s name=manager/grid/install_uninstall_plugin}Install / uninstall plugin{/s}',
		delete_plugin: '{s name=manager/grid/delete_plugin}Delete plugin{/s}',
		update_plugin_info: '{s name=manager/grid/update_plugin_info}Update plugin{/s}',
		reinstall_info: '{s name=manager/grid/reinstall_info}Reinstall plugin (Uninstall -> Install){/s}',
		manual_add_plugin: '{s name=manager/grid/manual_add}Add plugin manually{/s}',
		search: '{s name=manager/grid/search}Search...{/s}',
		bought: '{s name=manager/grid/bought}Bought{/s}',
		rented: '{s name=manager/grid/rented}Rented{/s}',
		tested: '{s name=manager/grid/tested}Tested{/s}',
		days_left: '{s name=manager/grid/days_left}([0] days left){/s}',
		active_plugins: '{s name=manager/grid/active_plugins}Active plugins{/s}',
		inactive_plugins: '{s name=manager/grid/inactive_plugins}Inactive plugins{/s}'
    },

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addAdditionalEvents();
        me.groupingPlugin = me.createGrouping();

        me.features = [ me.groupingPlugin ];
        me.store = me.pluginStore;
        me.columns = me.createColumns();
        me.tbar = me.createActionToolbar();
        me.bbar = me.createPagingToolbar();
        me.plugins = [
            Ext.create('Ext.grid.plugin.CellEditing', {
                clicksToEdit: 1
            })
        ];

        me.callParent(arguments);
    },

    /**
     * Adds additional events for the component.
     *
     * @public
     * @return void
     */
    addAdditionalEvents: function() {
        var me = this;

        me.addEvents(
            'search',
            'uninstallInstall',
            'reinstallPlugin',
            'editPlugin',
            'manualInstall',
            'selectionChange',
            'updatePluginInfo',
            'deleteplugin',
            'updateDummyPlugin'
        );
    },

    /**
     * Creates the grid column model for the grid panel.
     *
     * @public
     * @return Array - computed columns
     */
    createColumns: function() {
        var me = this;

        return [{
            dataIndex: 'label',
            header: me.snippets.plugin_name,
            flex: 2,
            renderer: me.pluginNameRenderer
        }, {
            dataIndex: 'author',
            header: me.snippets.supplier,
            flex: 1,
            renderer: me.merchantRenderer
        }, {
            dataIndex: 'license',
            header: me.snippets.license,
            flex: 1,
            renderer: me.licenseRenderer
        }, {
            dataIndex: 'version',
            header: me.snippets.version,
            width: 50,
            renderer: me.versionRenderer
        }, {
            dataIndex: 'added',
            xtype: 'datecolumn',
            header: me.snippets.added,
            flex: 1
        }, {
            dataIndex: 'active',
            header: me.snippets.active,
            width: 70,
            xtype: 'booleancolumn',
            trueText: me.snippets.active,
            falseText: me.snippets.inactive,
            editor: {
                xtype: 'checkbox',
                allowBlank: false
            }
        }, {
            xtype: 'actioncolumn',
            header: me.snippets.actions,
            width: 90,
            items: [
        /*{if {acl_is_allowed privilege=install}}*/
            {
                iconCls: 'sprite-plus-circle',
                tooltip: me.snippets.install_plugin,

                handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                    me.fireEvent('updateDummyPlugin', grid, rowIndex, colIndex, item, eOpts, record);
                },

                getClass: function(value, metadata, record, rowIdx) {
                    if (!record.get('capabilityDummy')) {
                        return Ext.baseCSSPrefix + 'hidden';
                    }
                }
            },
        /*{/if}*/


        /*{if {acl_is_allowed privilege=update}}*/
			{
                iconCls: 'sprite-pencil',
                tooltip: me.snippets.edit_plugin,
                handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                    me.fireEvent('editPlugin', grid, rowIndex, colIndex, item, eOpts, record);
                },

                getClass: function(value, metaData, record) {
                    if (record.get('capabilityDummy')) {
                        return Ext.baseCSSPrefix + 'hidden';
                    }

                    if(record.get('installed') == null) {
                        return Ext.baseCSSPrefix + 'hidden';
                    }
                }
            },
        /*{/if}*/
        /*{if {acl_is_allowed privilege=install}}*/
			{
                iconCls: 'sprite-minus-circle',
                tooltip: me.snippets.install_uninstall_plugin,
                handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                    if (record.get('updateVersion')) {
                        record.set('version', record.get('updateVersion'));
                    }
                    me.fireEvent('uninstallInstall', grid, item, eOpts, record);
                },

                getClass: function(value, metadata, record, rowIdx) {
                    if (record.get('capabilityDummy')) {
                        return Ext.baseCSSPrefix + 'hidden';
                    }

                    if (!record.get('capabilityInstall')) {
                        return Ext.baseCSSPrefix + 'hidden';
                    }
                    if (record.get('installed') == null)  {
                        return 'sprite-plus-circle';
                    }
                }
            },
        /*{/if}*/
        /*{if {acl_is_allowed privilege=install}}*/
			{
                iconCls: 'sprite-bin-metal-full',
                tooltip: me.snippets.delete_plugin,
                handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                    me.fireEvent('deleteplugin', grid, rowIndex, colIndex, item, eOpts, record);
                },

                getClass: function(value, metadata, record, rowIdx) {
                    if (record.get('capabilityDummy')) {
                        return Ext.baseCSSPrefix + 'hidden';
                    }

                   if (record.get('installed') != null || record.get('source') == 'Default')  {
                       return Ext.baseCSSPrefix + 'hidden';
                   }
               }
            },
            {
                iconCls: 'sprite-arrow-circle-135',
                tooltip: me.snippets.update_plugin_info,
                handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                    record.set('version', record.get('updateVersion'));
                    me.fireEvent('updatePluginInfo', record, me.pluginStore);
                },
                getClass: function(value, metadata, record, rowIdx) {
                    if (record.get('capabilityDummy')) {
                        return Ext.baseCSSPrefix + 'hidden';
                    }

                    if (record.get('updateVersion') == null) {
                        return Ext.baseCSSPrefix + 'hidden';
                    }
                }
            },
            {
                iconCls: 'sprite-arrow-continue',
                tooltip: me.snippets.reinstall_info,
                handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                    me.fireEvent('reinstallPlugin', record, me);
                },
                getClass: function(value, metadata, record, rowIdx) {
                    if (record.get('capabilityDummy')) {
                        return Ext.baseCSSPrefix + 'hidden';
                    }

                    if (!record.get('capabilityInstall')) {
                        return Ext.baseCSSPrefix + 'hidden';
                    }

                    if (!record.get('installed'))  {
                        return Ext.baseCSSPrefix + 'hidden';
                    }
                }
            },
        /*{/if}*/
        ]
        }];
    },

    /**
     * Creates the action toolbar which is located above the
     * grid panel.
     *
     * @public
     * @return [object] Ext.toolbar.Toolbar
     */
    createActionToolbar: function() {
        var me = this;

        /*{if {acl_is_allowed privilege=install}}*/
        me.manualInstallBtn = Ext.create('Ext.button.Button', {
            text: me.snippets.manual_add_plugin,
            iconCls: 'sprite-plus-circle',
            handler: function(button) {
                me.fireEvent('manualInstall', button);
            }
        });
        /*{/if}*/

        me.searchField = Ext.create('Ext.form.field.Text', {
            cls: 'searchfield',
            emptyText: me.snippets.search,
            enableKeyEvents:true,
            checkChangeBuffer: 500,
            width: 170,
            listeners: {
                scope: me,
                change: function(field, value, oldValue, event, eOpts) {
                    me.fireEvent('search', field, value, event, eOpts);
                }
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
        /*{if {acl_is_allowed privilege=install}}*/
				me.manualInstallBtn,
        /*{/if}*/
        /*{if {acl_is_allowed privilege=read}}*/
				'->', me.searchField, ' ' ]
        /*{/if}*/
		});
    },

    /**
     * Creates the paging toolbar which is located under the
     * grid panel.
     *
     * @public
     * @return [object] Ext.toolbar.Paging
     */
    createPagingToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Paging', {
            store: me.store
        });
    },

    /**
     * Creates the grouping feature of the grid panel.
     *
     * @oublic
     * @return [object] Ext.grid.feature.Grouping
     */
    createGrouping: function() {
        var me = this;

        return Ext.create('Ext.grid.feature.Grouping', {
            groupHeaderTpl: new Ext.XTemplate(
                '{literal}{name:this.formatName} ({rows.length} Plugins){/literal}',
                {
                    formatName: function(name) {
                        if(name == me.snippets.active) {
                            name = me.snippets.active_plugins;
                        } else {
                            name = me.snippets.inactive_plugins;
                        }
                        return name;
                    }
                }
            )
        });
    },

    /**
     * Render method which formats / converts the name of the plugin name
     * and adds the icon to the row.
     *
     * @public
     * @param [string] value - value of the current row
     * @param [string] meta - additional meta data of the row
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin of the current row
     * @return [string] formatted / converted plugin name
     */
    pluginNameRenderer: function(value, meta, record) {
        var iconPath = record.get('icon'), iconEl, license = '', me = this;

        if(record.get('active') === false) {
            meta.style = 'opacity: 0.65';
        } else {
            meta.style = 'opacity: 1';
        }
        if (record && record.getLicense() instanceof Ext.data.Store && record.getLicense().first() instanceof Ext.data.Model) {
            var licenseModel = record.getLicense().first();
            switch(licenseModel.get('type')) {
                case 2:
                case 3:
                    var expiration = licenseModel.get('expiration');
                    var today = new Date();
                    if (Ext.isDate(expiration)) {
                        var days = Math.ceil((expiration.getTime()-today.getTime())/(86400000))
                        license = '<span style="color: #999; font-weight: bold;"> '+Ext.String.format(me.snippets.days_left, days)+'</span>'
                    }
                    break;
            }
        }

        if(iconPath) {
            iconEl = '<div style="display: inline-block;margin-right: 4px;width:16px;height:16px;background:url('+ iconPath +') no-repeat"></div>';
        } else {
            iconEl = '<div class="sprite-puzzle" style="display: inline-block;margin-right: 4px;"></div>';
        }

        return iconEl + '<strong>' + value + '</strong>' + license;
    },

    /**
     * Render method which formats / converts the merchant of the plugin.
     *
     * @public
     * @param [string] value - value of the current row
     * @param [string] meta - additional meta data of the row
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin of the current row
     * @return [string] formatted / converted plugin merchant
     */
    merchantRenderer: function(value, meta, record) {
        var link = record.get('link')

        if(link.length) {
            return '<a href="'+link+'" target="_blank">' + value + '</a>';
        } else {
            return value;
        }
    },

    /**
     * Render method which formats / converts the merchant of the license.
     *
     * @public
     * @param [string] value - value of the current row
     * @param [string] meta - additional meta data of the row
     * @param [object] record - Shopware.apps.PluginManager.model.Plugin of the current row
     * @return [string] formatted / converted plugin license
     */
    licenseRenderer: function(value, meta, record) {
		var me = this;
        if (record && record.getLicense() instanceof Ext.data.Store && record.getLicense().first() instanceof Ext.data.Model) {
            var licenseModel = record.getLicense().first();
            switch (licenseModel.get('type')) {
                case 1:
                    return me.snippets.bought;
                    break;
                case 2:
                    return me.snippets.rented;
                    break;
                case 3:
                    return me.snippets.tested;
                    break;
            }
        } else {
            return '';
        }
    },

    /**
     * Renderer function for the version column.
     * @param value
     * @param meta
     * @param record
     */
    versionRenderer: function(value, meta, record) {
        var me = this, fragments, i;
        value += '';

        fragments = value.split('.');
        for(i = 0; i < 3; i++) {
            if (fragments.length < 3) {
                fragments.push('0');
            }
        }
        return fragments.join('.');
    }

});
//{/block}
