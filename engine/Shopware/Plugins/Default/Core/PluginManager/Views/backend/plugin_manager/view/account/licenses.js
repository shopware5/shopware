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
//{block name="backend/plugin_manager/view/account/licenses"}
Ext.define('Shopware.apps.PluginManager.view.account.Licenses', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.plugin-manager-account-licenses',
    border: 0,
    cls: Ext.baseCSSPrefix + 'plugin-manager-account-licenses',

	snippets: {
		order_number: '{s name=account/licenses/order_number}Order number{/s}',
		license: '{s name=account/licenses/license}Lizenz{/s}',
		plugin: '{s name=account/licenses/plugin}Plugin name{/s}',
		actions: '{s name=account/licenses/actions}Action(s){/s}',
		download_plugin: '{s name=account/licenses/download_plugin}Download plugin{/s}'
	},

    /**
     * Initializes the component.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('downloadplugin');
        me.store = me.licensedStore;
        me.columns = me.createColumns();
        me.bbar = me.createPagingToolbar();
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
            header: me.snippets.plugin,
            dataIndex: 'plugin',
            flex: 2,
            renderer: function(value) { return '<strong>'+value+'</strong>' }
        }, {
            header: me.snippets.order_number,
            dataIndex: 'ordernumber',
            flex: 1
        }, {
            header: me.snippets.license,
            dataIndex: 'license',
            flex: 1
        },
        /*{if {acl_is_allowed privilege=install}}*/{
            xtype: 'actioncolumn',
            width: 70,
            header: me.snippets.actions,
            items: [{
                iconCls: 'sprite-inbox-download',
                tooltip: me.snippets.download_plugin,
                handler: function(grid, rowIndex, colIndex, item, eOpts, record) {
                    me.fireEvent('downloadplugin', grid, rowIndex, colIndex, item, eOpts, record);
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
