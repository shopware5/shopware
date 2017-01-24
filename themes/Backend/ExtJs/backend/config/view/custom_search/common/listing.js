
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

//{namespace name=backend/custom_search/common}

//{block name="backend/config/view/custom_search/common/listing"}

Ext.define('Shopware.apps.Config.view.custom_search.common.Listing', {
    extend: 'Shopware.grid.Panel',
    alias: 'widget.config-custom-common-listing',
    changePositionUrl: '',

    createColumns: function() {
        var me = this,
            columns = me.callParent(arguments);

        columns = Ext.Array.insert(columns, 0, [me.createSortingColumn()]);

        me.viewConfig = {
            plugins: {
                ptype: 'gridviewdragdrop',
                ddGroup: me.alias + '-drag-and-drop'
            },
            listeners: {
                'drop': Ext.bind(me.onDrop, me)
            }
        };

        return columns;
    },

    createSortingColumn: function() {
        var me = this;

        return {
            width: 24,
            hideable: false,
            renderer : me.renderSorthandleColumn
        };
    },

    renderSorthandleColumn: function (value, metadata) {
        return '<div style="cursor: n-resize;">&#009868;</div>';
    },

    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.RowModel', {
            listeners: {
                selectionchange: function (selModel, selection) {
                    return me.onSelectionChange(selModel, selection);
                }
            }
        });
    },

    onDrop: function(node, data, overModel, dropPosition, eOpts ) {
        var me = this,
            position = 0,
            model = data.records.shift();

        if (dropPosition == 'before') {
            position = overModel.get('position') - 1;
        } else {
            position = overModel.get('position') + 1;
        }

        Ext.Ajax.request({
            url: me.changePositionUrl,
            method: 'POST',
            params: {
                id: model.get('id'),
                position: position
            },
            success: function(operation, opts) {
                me.getStore().load();
            }
        });
    }
});

//{/block}
