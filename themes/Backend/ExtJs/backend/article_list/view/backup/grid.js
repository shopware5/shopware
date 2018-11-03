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
 */

//{namespace name=backend/article_list/main}
//{block name="backend/article_list/view/backup/grid"}
Ext.define('Shopware.apps.ArticleList.view.Backup.Grid', {
    extend: 'Ext.grid.Panel',


    alias: 'widget.multi-edit-backup-grid',

    /**
     * Init the grid
     */
    initComponent: function() {
        var me = this;

        me.bbar = me.getPagingBar();
        me.columns = me.getColumns();

        me.addEvents('deleteBackup', 'restoreBackup');

        me.callParent(arguments);
    },

    /**
     * Returns the columns for the grid
     *
     * @returns Array
     */
    getColumns: function()  {
        var me = this;

        return [{
            width: 120,
            header: '{s name="backup/date"}Date{/s}',
            dataIndex: 'date',
            xtype: 'datecolumn',
            format: 'Y-m-d H:i',
            menuDisabled: true,
            sortable: true
        },{
            width: 120,
            header: '{s name="backup/items"}Affected products{/s}',
            dataIndex: 'items',
            menuDisabled: true,
            sortable: true
        },{
            flex: 1,
            header: '{s name="backup/filter"}Filter string{/s}',
            dataIndex: 'filterString',
            menuDisabled: true,
            sortable: false,
            renderer: me.nl2brRenderer
        },{
            flex: 1,
            header: '{s name="backup/operations"}Operations applied{/s}',
            dataIndex: 'operationString',
            menuDisabled: true,
            sortable: false,
            renderer: me.nl2brRenderer
        }, {
            width: 80,
            header: '{s name="backup/size"}Size{/s}',
            dataIndex: 'size',
            menuDisabled: true,
            sortable: true,
            renderer: function(value) {
                value = Math.round(value / 1024 / 1024);
                if (value == 0) {
                    value = '< 1';
                }
                return value + ' MB';
            }
        }, {
            /**
             * Special column type which provides
             * clickable icons in each row
             */
            xtype: 'actioncolumn',
            width: 50,
            items: [
                {
                    iconCls: 'sprite-arrow-circle-225-left',
                    tooltip: '{s name=restoreBackup}Restore{/s}',
                    handler: function (view, rowIndex, colIndex, item, e) {
                        me.fireEvent('restoreBackup', rowIndex);
                    }
                },
                {
                    iconCls: 'sprite-minus-circle-frame',
                    tooltip: '{s name=deleteBackup}Delete backup{/s}',
                    handler: function (view, rowIndex, colIndex, item, e) {
                        me.fireEvent('deleteBackup', rowIndex);
                    }
                }
            ]
        }];

    },

    /**
     * Replaces newline chars with <br>
     *
     * @param value
     * @returns string
     */
    nl2brRenderer: function(value) {
        value = value.replace(' AND ', "\nAND\n");
        value = value.replace(' OR ', "\nOR\n");
        return value.replace(/[\r\n]+/g, "<br>");
    },


    /**
     * Creates pagingbar
     *
     * @return Ext.toolbar.Paging
     */
    getPagingBar: function() {
        var me = this;

        var pagingBar = Ext.create('Ext.toolbar.Paging', {
            store: me.store,
            dock:'bottom',
            displayInfo:true
        });

        return pagingBar;
    }
});
//{/block}
