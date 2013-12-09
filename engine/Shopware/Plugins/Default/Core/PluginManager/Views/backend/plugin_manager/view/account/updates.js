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
//{block name="backend/plugin_manager/view/account/updates"}
Ext.define('Shopware.apps.PluginManager.view.account.Updates', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.plugin-manager-account-updates',
    border: 0,
    cls: Ext.baseCSSPrefix + 'plugin-manager-account-updates',

	snippets:{
		plugin_name: '{s name=account/updates/plugin_name}Plugin name{/s}',
		plugin_key: '{s name=account/updates/plugin_key}Plugin key{/s}',
		order_number: '{s name=account/updates/order_number}Order number{/s}',
		current_version: '{s name=account/updates/current_version}Current installed version{/s}',
		available_versions: '{s name=account/updates/available_versions}Available versions{/s}',
		actions: '{s name=account/updates/actions}Action(s){/s}',
		update_plugin: '{s name=account/updates/update_plugin}Update plugin{/s}'
	},

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('updateplugin');
        me.store = me.updatesStore;
        me.columns = me.createColumns();
        me.bbar = me.createPagingToolbar();
        me.callParent(arguments);
    },

    /**
     * Creates the grid column model for the grid panel.
     *
     * @public
     * @return [array] - computed columns
     */
    createColumns: function() {
        var me = this;

        return [{
            dataIndex: 'plugin',
            header: me.snippets.plugin_name,
            flex: 2,
            renderer: function(value) { return '<strong>'+value+'</strong>'; }
        }, {
            dataIndex: 'name',
            header: me.snippets.plugin_key,
            flex: 2,
            renderer: function(value) { return '<strong>'+value+'</strong>'; }
        }, {
            dataIndex: 'ordernumber',
            header: me.snippets.order_number,
            flex: 1
        }, {
            dataIndex: 'currentVersion',
            header: me.snippets.current_version,
            flex: 1
        }, {
            dataIndex: 'availableVersion',
            header: me.snippets.available_versions,
            flex: 1
        },
        /*{if {acl_is_allowed privilege=update}}*/ {
            xtype: 'actioncolumn',
            width: 70,
            header: me.snippets.actions,
            items: [{
                iconCls: 'sprite-inbox-download',
                tooltip: me.snippets.update_plugin,
                handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                    me.fireEvent('updateplugin', grid, rowIndex, colIndex, item, eOpts, record);
                }
            }]
        }
        /*{/if}*/];
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
    }
});
//{/block}
