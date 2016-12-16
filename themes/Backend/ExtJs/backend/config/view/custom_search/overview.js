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

//{namespace name=backend/custom_search/sorting}

//{block name="backend/config/view/custom_search/overview"}

Ext.define('Shopware.apps.Config.view.custom_search.Overview', {
    extend: 'Shopware.apps.Config.view.base.Form',
    alias: 'widget.config-form-customsearch',
    flex: 1,

    getItems: function() {
        var me = this;

        return [
            me.createTab()
        ];
    },

    createTab: function() {
        var me = this;

        me.tabPanel = Ext.create('Ext.tab.Panel', {
            region: 'center',
            items: [
                me.createSortingTab()
            ]
        });
        return me.tabPanel;
    },

    createSortingTab: function() {
        var me = this;

        me.sortingListing = Ext.create('Shopware.apps.Config.view.custom_search.sorting.Listing', {
            store: Ext.create('Shopware.apps.Base.store.CustomSorting', {
                pageSize: 200
            }).load(),
            flex: 2,
            subApp: me.subApp,
            onAddItem: function() {
                me.formPanel.setDisabled(false);
                me.formPanel.loadRecord(
                    Ext.create('Shopware.apps.Base.model.CustomSorting', {
                        displayInCategories: true,
                        active: true
                    })
                );
            },
            onSelectionChange: function(selModel, selection) {
                if (selection.length <= 0) {
                    me.formPanel.setDisabled(true);
                    return;
                }
                me.onLoadSorting(selection[0]);
            }
        });

        me.sortingDetail = Ext.create('Shopware.apps.Config.view.custom_search.sorting.Detail', {
            record: Ext.create('Shopware.apps.Base.model.CustomSorting'),
            width: 550
        });

        me.formPanel = me.createFormPanel();

        return Ext.create('Ext.container.Container', {
            title: '{s name="sorting_tab"}{/s}',
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            items: [me.sortingListing, me.formPanel]
        });
    },

    createFormPanel: function() {
        var me = this;

        return Ext.create('Ext.form.Panel', {
            items: [ me.sortingDetail ],
            disabled: true,
            bodyPadding: '20 5',
            plugins: [{
                ptype: 'translation',
                translationMerge: true,
                translationType: 'custom_sorting'
            }],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                items: me.createToolbarItems()
            }]
        });
    },

    createToolbarItems: function() {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            handler: Ext.bind(me.saveSorting, me),
            text: '{s name="apply_button"}{/s}'
        });
        return ['->', me.saveButton];
    },

    saveSorting: function() {
        var me = this,
            record = me.formPanel.getRecord();

        if (!me.formPanel.getForm().isValid()) {
            return;
        }

        me.formPanel.getForm().updateRecord(record);
        me.formPanel.setDisabled(true);
        record.save({
            callback: function() {
                me.sortingListing.getStore().load();
            }
        });
    },

    onLoadSorting: function(record) {
        var me = this;

        me.formPanel.setDisabled(false);
        me.formPanel.loadRecord(record);
    }
});

//{/block}
