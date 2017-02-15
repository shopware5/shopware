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

//{namespace name=backend/custom_search/translation}

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
                me.createFacetTab(),
                me.createSortingTab()
            ]
        });
        return me.tabPanel;
    },

    createFacetTab: function() {
        var me = this;

        me.facetForm = Ext.create('Shopware.apps.Config.view.custom_search.facet.Detail', {
            width: 550,
            disabled: true,
            listeners: {
                'facet-saved': function() {
                    me.facetListing.getStore().load();
                }
            }
        });

        me.facetStore = Ext.create('Shopware.apps.Base.store.CustomFacet', {
            pageSize: 200
        }).load();

        me.facetListing = Ext.create('Shopware.apps.Config.view.custom_search.facet.Listing', {
            store: me.facetStore,
            flex: 2,
            subApp: me.subApp,
            facetForm: me.facetForm
        });

        return Ext.create('Ext.container.Container', {
            title: '{s name="facet_tab"}{/s}',
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            items: [me.facetListing, me.facetForm]
        });
    },

    createSortingTab: function() {
        var me = this;

        me.sortingDetail = Ext.create('Shopware.apps.Config.view.custom_search.sorting.Detail', {
            record: Ext.create('Shopware.apps.Base.model.CustomSorting')
        });

        me.sortingForm = Ext.create('Ext.form.Panel', {
            items: [ me.sortingDetail ],
            width: 550,
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
                items: ['->', {
                    xtype: 'button',
                    cls: 'primary',
                    handler: Ext.bind(me.saveSorting, me),
                    text: '{s name="apply_button"}{/s}'
                }]
            }]
        });

        me.sortingListing = Ext.create('Shopware.apps.Config.view.custom_search.sorting.Listing', {
            store: Ext.create('Shopware.apps.Base.store.CustomSorting', { pageSize: 200 }).load(),
            flex: 2,
            sortingForm: me.sortingForm,
            sortingDetail: me.sortingDetail,
            subApp: me.subApp
        });

        return Ext.create('Ext.container.Container', {
            title: '{s name="sorting_tab"}{/s}',
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            items: [me.sortingListing, me.sortingForm]
        });
    },

    saveSorting: function() {
        var me = this,
            record = me.sortingForm.getRecord();

        if (!me.sortingForm.getForm().isValid()) {
            return;
        }

        me.sortingForm.getForm().updateRecord(record);
        me.sortingForm.setDisabled(true);
        record.save({
            callback: function() {
                me.sortingListing.getStore().load();
            }
        });
    }
});

//{/block}
