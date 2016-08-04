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
 * @package    Log
 * @subpackage View
 * @version    $Id$
 * @author VIISON GmbH
 */

//{namespace name=backend/log/shared}

/**
 * Shopware UI - Core log view list
 *
 * This grid contains all entries of the core log and its information.
 */
//{block name="backend/log/view/log/shared/list"}
Ext.define('Shopware.apps.Log.view.log.shared.List', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.log-shared-main-list',
    ui: 'shopware-ui',
    border: 0,
    autoScroll: true,

    /**
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.columns = me.getColumns();
        me.dockedItems = [{
            dock: 'bottom',
            xtype: 'pagingtoolbar',
            displayInfo: true,
            store: me.store
        }];

        me.callParent(arguments);
    },

    /**
     *  @return Ext.grid.Column[]
     */
    getColumns: function(){
        var me = this;

        var columns = [{
            xtype: 'datecolumn',
            header: '{s name=model/field/timestamp}Date{/s}',
            dataIndex: 'timestamp',
            width: 150,
            format: 'Y-m-d H:i:s'
        }, {
            header: '{s name=model/field/level}Level{/s}',
            dataIndex: 'level',
            width: 100,
            sortable: false
        }, {
            header: '{s name=model/field/message}Message{/s}',
            dataIndex: 'message',
            flex: 1,
            sortable: false
        }, {
            header: '{s name=model/field/code}Error code{/s}',
            dataIndex: 'exception',
            width: 75,
            sortable: false,
            renderer: function(exception) {
                return (Ext.isObject(exception) && exception.code) ? exception.code : 0;
            }
        }, {
            xtype: 'actioncolumn',
            width: 30,
            items: [{
                iconCls: 'sprite-magnifier',
                action: 'openLog',
                tooltip: '{s name=grid/action/tooltip/open_log}Open log{/s}',
                handler: function(view, rowIndex, colIndex, item, event, record) {
                    me.fireEvent('openLog', record);
                }
            }]
        }];

        return columns;
    }
});
//{/block}
