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

/**
 * todo@all: Documentation
 */

//{namespace name=backend/config/view/main}

//{block name="backend/config/view/base/property"}
Ext.define('Shopware.apps.Config.view.base.Property', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.config-base-property',

    plugins: [{
        ptype: 'cellediting',
        pluginId: 'cellediting',
        clicksToEdit: 1
    }],

    margin: '10 0 0 0',
    border: false,
    viewConfig: {
        emptyText: '{s name=property_table/empty_text}No entries{/s}'
    },

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            dockedItems: [
                me.getToolbar()
            ],
            columns: []
        });

        me.columns = Ext.Array.merge(me.columns, me.getColumns());

        me.callParent(arguments);
    },

    getColumns: function() {
        var me = this;

        return [me.getActionColumn()];
    },

    getActionColumn: function() {
        var me = this;
        return {
            xtype: 'actioncolumn',
            width: 25,
            items: [{
              iconCls: 'sprite-minus-circle-frame',
              action: 'delete',
              tooltip: '{s name=property_table/delete_tooltip}Delete entry{/s}',
              handler: function (view, rowIndex, colIndex, item, opts, record) {
                  me.fireEvent('delete', view, record, rowIndex);
              }
            }]
        };
    },

    getToolbar: function() {
        var me = this;
        return {
            xtype: 'toolbar',
            dock: 'top',
            border: false,
            items: me.getTopBar()
        };
    },

    getTopBar: function () {
        var me = this;
        return [{
            iconCls:'sprite-plus-circle-frame',
            text:'{s name=property_table/add_text}Add entry{/s}',
            action:'add'
        }];
    }
});
//{/block}
