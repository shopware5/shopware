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
 * @package    Category
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/sorting} */

//{block name="backend/category/view/sorting/tab"}
Ext.define('Shopware.apps.Category.view.sorting.Tab', {
    extend: 'Ext.panel.Panel',
    title: '{s name="title"}{/s}',
    layout: 'fit',
    alias: 'widget.manual-sort-tab',
    currentCategoryId: null,

    initComponent: function () {
        this.tbar = this.createActionToolbar();
        this.table = Ext.create('Shopware.apps.Category.view.sorting.Table');
        this.grid = Ext.create('Shopware.apps.Category.view.sorting.Grid', {
            hidden: true,
        });
        this.items = [
            this.table,
            this.grid
        ];

        this.callParent(arguments);
    },

    createActionToolbar: function () {
        var me = this;

        this.displayTypeBtn = Ext.create('Ext.button.Cycle', {
            showText: true,
            prependText: '{s name=display_as}Display as{/s} ',
            action: 'mediamanager-media-view-layout',
            handler: function (btn) {
                me.fireEvent('layout-button-click', me, btn.getActiveItem());
            },
            menu: {
                items: [
                    {
                        text: '{s name=table}Table{/s}',
                        layout: 'table',
                        iconCls: 'sprite-application-table'
                    }, {
                        text: '{s name=grid}Grid{/s}',
                        layout: 'grid',
                        checked: true,
                        iconCls: 'sprite-application-icon-large'
                    }]
            }
        });

        this.resetBtn = Ext.create('Ext.button.Button', {
            text: '{s name="reset_category_btn"}{/s}',
            iconCls: 'sprite-minus-circle',
            handler: function () {
                me.fireEvent('reset-category', me.currentCategoryId);
            }
        });

        this.sortingBtn = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: 'Sortierung',
            valueField: 'id',
            displayField: 'name',
            queryMode: 'local',
            listeners: {
                change: function (field, id) {
                    me.table.store.getProxy().extraParams.sortingId = id;
                    me.table.store.currentPage = 1;
                    me.table.store.load();
                }
            },
            store: Ext.create('Ext.data.Store', {
                fields: [
                    {
                        name: 'id',
                        type: 'int'
                    },
                    {
                        name: 'name',
                        type: 'string'
                    }
                ],
                proxy: {
                    type: 'ajax',
                    url: '{url controller="ManualSorting" action="getSortings"}',
                    reader: {
                        type: 'json',
                        root: 'data',
                        totalProperty: 'total'
                    }
                }
            })
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
                this.displayTypeBtn,
                this.resetBtn,
                this.sortingBtn,
                '->',
                {
                    xtype: 'productsearchfield',
                    cls: 'searchfield',
                    store: Ext.create('Shopware.apps.Base.store.Article'),
                    searchScope: ['articles'],
                    emptyText: '{s name="search/emptytext"}{/s}'
                }
            ]
        });
    },

    loadCategory: function (record) {
        var me = this;
        this.enable();

        this.store = Ext.create('Shopware.apps.Category.store.ManualSorting');
        this.store.getProxy().extraParams.categoryId = this.currentCategoryId = record.get('id');

        this.table.reconfigure(this.store);
        this.grid.reconfigure(this.store);

        this.sortingBtn.store.getProxy().extraParams.categoryId = this.currentCategoryId;
        this.sortingBtn.store.load(function () {
            // Force fire change event
            me.sortingBtn.lastValue = null;

            me.sortingBtn.setValue(me.sortingBtn.store.getAt(0).get('id'));
        });
    }
});
//{/block}
