/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

/**
 * todo@all: Documentation
 */

//{namespace name=backend/config/view/plugin}

//{block name="backend/config/view/plugin/view"}
Ext.define('Shopware.apps.Config.view.plugin.Table', {
    extend: 'Shopware.apps.Config.view.base.Table',
    alias: 'widget.config-plugin-table',

    store: 'form.Plugin',
    searchField: 'search',

	snippets: {
		table: {
			columns:{
				name: '{s name=table/columns/name}Name{/s}',
				active: '{s name=table/columns/active}Active{/s}',
				path: '{s name=table/columns/path}Path{/s}',
				version: '{s name=table/columns/version}Version{/s}',
				author:'{s name=table/columns/author}Author / Support{/s}'
			},
			topBar:{
				upload: '{s name=table/top_bar/upload}Upload plugin{/s}',
				install: '{s name=table/top_bar/install}Install plugin{/s}',
				uninstall: '{s name=table/top_bar/uninstall}Uninstall plugin{/s}',
				delete: '{s name=table/top_bar/delete}Delete plugin{/s}',
				filter: '{s name=table/top_bar/filter}Filter by...{/s}',
				filterActive: '{s name=table/top_bar/filter_active}Only active plugins{/s}',
				filterInactive: '{s name=table/top_bar/filter_inactive}Only inactive plugins{/s}',
				filterPayment: '{s name=table/top_bar/filter_payment}Only payment plugins{/s}',
				filterCommunity: '{s name=table/top_bar/filter_community}Only community plugins{/s}',
				tooltipUpload: '{s name=table/top_bar/tooltip_upload}Add (ALT + INSERT){/s}',
				tooltipInstall: '{s name=table/top_bar/tooltip_install}Install (ALT + ENTER){/s}',
				tooltipUninstall: '{s name=table/top_bar/tooltip_uninstall}Uninstall (ALT + RETURN){/s}',
				tooltipDelete: '{s name=table/top_bar/tooltip_delete}Delete (ALT + DELETE){/s}'
			},
			actionColumn:{
				tooltipInstall: '{s name=table/action_column/tooltip_install}Install (ALT + ENTER){/s}',
				tooltipEdit: '{s name=table/action_column/tooltip_edit}Edit plugin config{/s}',
				tooltipDelete: '{s name=table/action_column/tooltip_delete}Delete plugin{/s}',
                tooltipUninstall: '{s name=table/action_column/tooltip_uninstall}Uninstall plugin (ALT + RETURN){/s}'
			}
		}
	},

    initComponent: function() {
        var me = this;

        me.addEvents('installPlugin', 'deletePlugin', 'editPlugin');

        me.callParent(arguments);
    },

    getColumns: function() {
        var me = this;
        return [{
            dataIndex: 'label',
            text: me.snippets.table.columns.name,
            flex: 1
        },{
            xtype: 'booleancolumn',
            dataIndex: 'active',
            text: me.snippets.table.columns.active,
            flex: .5
        },{
            dataIndex: 'path',
            text: me.snippets.table.columns.path,
            sortable: false,
            flex: 1
        },{
            dataIndex: 'version',
            text: me.snippets.table.columns.version,
            flex: .5
        },{
            text: me.snippets.table.columns.author,
            dataIndex: 'author',
            flex: 1,
            renderer: function(value, meta, record) {
                var link = record.get('link');

                if(link.length) {
                    return '<a href="'+link+'" target="_blank">' + value + '</a>';
                }

                return value;
            }
        }, me.getActionColumn()];
    },

    getTopBar:function () {
        var me = this;
        return [{
            iconCls:'sprite-drive-upload',
            text: me.snippets.table.topBar.upload,
            tooltip: me.snippets.table.topBar.tooltipUpload,
            action:'upload'
        }, {
            iconCls:'sprite-puzzle--plus',
            text: me.snippets.table.topBar.install,
            tooltip: me.snippets.table.topBar.tooltipInstall,
            disabled:true,
            action:'install'
        }, {
            iconCls:'sprite-puzzle--minus',
            text: me.snippets.table.topBar.uninstall,
            tooltip: me.snippets.table.topBar.tooltipUninstall,
            hidden:true,
            action:'uninstall'
        }, {
            iconCls:'sprite-puzzle--minus',
            text: me.snippets.table.topBar.delete,
            tooltip: me.snippets.table.topBar.tooltipDelete,
            hidden:true,
            action:'delete'
        }, {
            xtype: 'config-element-select',
            name: 'filter',
            emptyText: me.snippets.table.topBar.filter,
            store: [
                ['active', me.snippets.table.topBar.filterActive],
                ['inactive', me.snippets.table.topBar.filterInactive],
                ['payment', me.snippets.table.topBar.filterPayment],
                ['community', me.snippets.table.topBar.filterCommunity]
            ]
        }, '->', {
            xtype:'config-base-search'
        }, {
            xtype:'tbspacer', width:6
        }];
    },

    getActionColumn: function() {
        var me = this;
        return {
            xtype: 'actioncolumn',
            width: 80,
            items: [{
                iconCls: 'sprite-puzzle--plus',
                action: 'install',
                tooltip: me.snippets.table.actionColumn.tooltipInstall,
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    view.getSelectionModel().select(rowIndex);
                    me.fireEvent('installPlugin', { action: 'install' });
                },
                getClass: function(value, metadata, record, rowIdx) {
                    if (record.get('installed')) {
                        return 'x-hidden';
                    }
                }
            },{
                iconCls: 'sprite-puzzle--plus',
                action: 'update',
                tooltip: 'Update plugin',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    view.getSelectionModel().select(rowIndex);
                    me.fireEvent('updatePlugin', { action: 'update' });
                },
                getClass: function(value, metadata, record, rowIdx) {
                    if (!record.get('updateVersion'))  {
                        return 'x-hidden';
                    }
                }
            },{
                iconCls: 'sprite-puzzle--minus',
                action: 'uninstall',
                tooltip: me.snippets.table.actionColumn.tooltipUninstall,
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    view.getSelectionModel().select(rowIndex);
                    me.fireEvent('uninstallPlugin', { action: 'uninstall' });
                },
                getClass: function(value, metadata, record, rowIdx) {
                    if (!record.get('installed') || !record.get('capabilityInstall'))  {
                        return 'x-hidden';
                    }
                }
            },{
                iconCls: 'sprite-puzzle--minus',
                action: 'delete',
                tooltip: me.snippets.table.actionColumn.tooltipDelete,
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    view.getSelectionModel().select(rowIndex);
                    me.fireEvent('deletePlugin', { action: 'delete' });
                },
                getClass: function(value, metadata, record, rowIdx) {
                    if (record.get('installed') || record.get('source') != 'Community')  {
                        return 'x-hidden';
                    }
                }
            },{
                iconCls: 'sprite-gear--arrow',
                action: 'edit',
                tooltip: me.snippets.table.actionColumn.tooltipEdit,
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    view.getSelectionModel().select(rowIndex);
                    me.fireEvent('editPlugin', me, record);
                },
                getClass: function(value, metadata, record, rowIdx) {
                    if (!record.get('active') || !record.get('configFormId'))  {
                        return 'x-hidden';
                    }
                }
            }]
        };
    }
});
//{/block}
