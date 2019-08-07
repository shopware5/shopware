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

//{block name="backend/config/view/base/table"}
Ext.define('Shopware.apps.Config.view.base.Table', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.config-base-table',

    region: 'center',
    border: false,

    deletable: true,
    addable: true,

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            dockedItems: [
                me.getPagingToolbar(),
                me.getToolbar()
            ],
            columns: me.getColumns()
        });

        me.callParent(arguments);

        me.store.clearFilter(true);
        me.store.load();
    },

    getColumns: function() {
        var me = this;
        return [];
    },

    getPagingToolbar: function() {
        return Ext.create('Ext.toolbar.Paging', {
            store: this.store,
            dock:'bottom',
            displayInfo:true
        });
    },

    onPageSizeChange: function(combo, records) {
        var record = records[0],
            me = this;

        me.store.pageSize = record.get('value');
        me.store.loadPage(1);
    },

    getToolbar: function() {
        var me = this;
        return {
            xtype: 'toolbar',
            ui: 'shopware-ui',
            dock: 'top',
            border: false,
            items: me.getTopBar()
        };
    },

    getTopBar:function () {
        var me = this;
        var items = [];
        if(me.addable) {
            items.push({
                iconCls:'sprite-plus-circle-frame',
                text:'{s name=table/add_text}Add entry{/s}',
                tooltip:'{s name=table/add_tooltip}Add (ALT + INSERT){/s}',
                action:'add'
            });
        }
        if(me.deletable) {
            items.push({
                iconCls:'sprite-minus-circle-frame',
                text:'{s name=table/delete_text}Delete entry{/s}',
                tooltip:'{s name=table/delete_tooltip}Delete (ALT + DELETE){/s}',
                disabled:true,
                action:'delete'
            });
        }
        items.push('->', {
            xtype:'config-base-search'
        }, {
            xtype:'tbspacer', width:6
        });
        return items;
    }
});
//{/block}
