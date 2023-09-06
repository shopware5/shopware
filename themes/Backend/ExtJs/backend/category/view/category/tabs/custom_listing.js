/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 *
 * @category   Shopware
 * @package    Category
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name="backend/custom_search/translation"}

//{block name="backend/category/view/tabs/custom_listing"}

Ext.define('Shopware.apps.Category.view.category.tabs.CustomListing', {
    extend: 'Ext.form.Panel',
    alias: 'widget.category-tab-custom-listing',
    title: '{s name="category/custom_listing_title"}{/s}',
    bodyPadding: 20,
    layout: 'anchor',
    name: 'custom-listing',
    cls: 'shopware-form',
    border: 0,
    autoScroll: true,
    mixins: {
        factory: 'Shopware.attribute.SelectionFactory'
    },

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.callParent(arguments);
    },

    /**
     * @return { Ext.form.FieldSet[] }
     */
    createItems: function() {
        var me = this;

        me.sortingFieldSet = Ext.create('Ext.form.FieldSet', {
            title: '{s name="category/sorting_title"}{/s}',
            anchor: '100%',
            items: [
                me.createHideSortingItem(),
                me.createActivateSortingItem(),
                me.createSortingSelection(),
                me.createCopySettingsButton(me.copySortingSettings)
            ]
        });

        me.facetFieldSet = Ext.create('Ext.form.FieldSet', {
            title: '{s name="category/facet_title"}{/s}',
            anchor: '100%',
            items: [
                me.createHideFacetItem(),
                me.createActivateFacetItem(),
                me.createFacetSelection(),
                me.createCopySettingsButton(me.copyFacetSettings)
            ]
        });

        return [me.sortingFieldSet, me.facetFieldSet];
    },

    /**
     * @return { Ext.form.field.Checkbox }
     */
    createHideFacetItem: function() {
        var me = this;

        me.hideFilterItem = Ext.create('Ext.form.field.Checkbox', {
            labelWidth: 155,
            name: 'hideFilter',
            inputValue: true,
            uncheckedValue: false,
            dataIndex: 'hideFilter',
            fieldLabel: '{s namespace=backend/category/main name="view/settings_default_settings_no_filter_label"}{/s}'
        });
        return me.hideFilterItem;
    },

    /**
     * @return { Ext.form.field.Checkbox }
     */
    createActivateSortingItem: function() {
        var me = this;

        me.activateSorting = Ext.create('Ext.form.field.Checkbox', {
            labelWidth: 155,
            inputValue: true,
            uncheckedValue: false,
            fieldLabel: '{s name="category/activate_sorting"}{/s}',
            listeners: {
                'change': Ext.bind(me.onActivateSorting, me)
            }
        });
        return me.activateSorting;
    },

    /**
     * @return { Ext.form.field.Checkbox }
     */
    createActivateFacetItem: function() {
        var me = this;

        me.activateFacets = Ext.create('Ext.form.field.Checkbox', {
            labelWidth: 155,
            inputValue: true,
            uncheckedValue: false,
            fieldLabel: '{s name="category/activate_facets"}{/s}',
            listeners: {
                'change': Ext.bind(me.onActivateFacet, me)
            }
        });
        return me.activateFacets;
    },

    /**
     * @return { Ext.form.field.Checkbox }
     */
    createHideSortingItem: function() {
        var me = this;

        me.hideSorting = Ext.create('Ext.form.field.Checkbox', {
            labelWidth: 155,
            name: 'hideSortings',
            inputValue: true,
            uncheckedValue: false,
            fieldLabel: '{s name="category/hide_sorting"}{/s}'
        });
        return me.hideSorting;
    },

    /**
     * @return { Shopware.form.field.CustomSortingGrid }
     */
    createSortingSelection: function() {
        var me = this, store;

        store = me.createEntitySearchStore("Shopware\\Models\\Search\\CustomSorting");
        store.pageSize = 200;

        me.sortingSelection = Ext.create('Shopware.form.field.CustomSortingGrid', {
            labelWidth: 155,
            disabled: true,
            ignoreDisabled: false,
            store: store,
            searchStore: me.createEntitySearchStore("Shopware\\Models\\Search\\CustomSorting"),
            fieldLabel: '{s name="category/sorting_selection"}{/s}',
            name: 'sortingIds'
        });
        return me.sortingSelection;
    },

    createFacetSearchStore: function(entity) {
        var me = this;

        return Ext.create('Ext.data.Store', {
            model: 'Shopware.model.Dynamic',
            proxy: {
                type: 'ajax',
                url: '{url controller="EntitySearch" action="search"}?model=' + entity,
                reader: Ext.create('Shopware.model.DynamicReader')
            },
            sorters: [{
                sorterFn: function(facet1, facet2){
                    if (!me.facets) {
                        return;
                    }

                    var getPos = function(facet){
                        var indices = me.facets;
                        return indices.indexOf(facet.internalId.toString());
                    };

                    var pos1 = getPos(facet1),
                        pos2 = getPos(facet2);

                    if (pos1 === pos2) {
                        return 0;
                    }
                    return pos1 < pos2 ? -1 : 1;
                }
            }],
        });
    },

    /**
     * @return { Shopware.form.field.CustomFacetGrid }
     */
    createFacetSelection: function() {
        var me = this, store, searchStore;

        store = me.createFacetSearchStore("Shopware\\Models\\Search\\CustomFacet");
        searchStore = me.createFacetSearchStore("Shopware\\Models\\Search\\CustomFacet");

        searchStore.remoteFilter = true;
        searchStore.sortOnLoad = true;
        searchStore.filter(me.createFacetStoreFilter());
        store.remoteFilter = true;
        store.filter(me.createFacetStoreFilter());
        store.pageSize = 200;

        me.facetSelection = Ext.create('Shopware.form.field.CustomFacetGrid', {
            labelWidth: 155,
            disabled: true,
            ignoreDisabled: false,
            store: store,
            searchStore: searchStore,
            fieldLabel: '{s name="category/facet_selection"}{/s}',
            name: 'facetIds'
        });
        return me.facetSelection;
    },

    /**
     * @param { function } copyFunction
     *
     * @return { Ext.button.Button }
     */
    createCopySettingsButton: function(copyFunction) {
        var me = this;

        me.copySettingsButton = Ext.create('Ext.button.Button', {
            cls: 'primary small',
            margin: '5 0 0 160',
            text: '{s name="category/copy_settings_button"}{/s}',
            handler: Ext.bind(copyFunction, me)
        });
        return me.copySettingsButton;
    },

    copySortingSettings: function() {
        var me = this;

        if (!me.category) {
            return;
        }

        me.fireEvent('saveCategory', me.category, function() {
            Ext.Ajax.request({
                url: '{url controller=CustomSorting action=copyCategorySettings}',
                method: 'POST',
                params: {
                    categoryId: me.category.get('id')
                },
                success: function(operation, opts) {
                    Shopware.Notification.createGrowlMessage('', '{s name="category/copy_success"}{/s}');
                }
            });
        });
    },

    copyFacetSettings: function() {
        var me = this;

        if (!me.category) {
            return;
        }

        me.fireEvent('saveCategory', me.category, function() {
            Ext.Ajax.request({
                url: '{url controller=CustomFacet action=copyCategorySettings}',
                method: 'POST',
                params: {
                    categoryId: me.category.get('id')
                },
                success: function(operation, opts) {
                    Shopware.Notification.createGrowlMessage('', '{s name="category/copy_success"}{/s}');
                }
            });
        });
    },

    /**
     * @return { Ext.util.Filter[] }
     */
    createFacetStoreFilter: function() {
        var filterCategoryFacet = new Ext.util.Filter({
            property: 'uniqueKey',
            expression: '!=',
            value: 'CategoryFacet'
        });

        var includeFacetsWithoutUniqueKey = new Ext.util.Filter({
            property: 'uniqueKey',
            operator: 'OR',
            expression: 'IS NULL'
        });

        return [filterCategoryFacet, includeFacetsWithoutUniqueKey]
    },

    /**
     * @param { Ext.form.field.Checkbox } checkbox
     * @param { boolean } active
     *
     * @return { boolean }
     */
    onActivateSorting: function(checkbox, active) {
        var me = this;

        if (active) {
            me.sortingSelection.enable();
            return true;
        }

        me.category.set('sortingIds', null);
        me.sortingSelection.disable();
        me.sortingSelection.store.load();
        return true;
    },

    /**
     * @param { Ext.form.field.Checkbox } checkbox
     * @param { boolean } active
     *
     * @return { boolean }
     */
    onActivateFacet: function(checkbox, active) {
        var me = this;

        if (active) {
            me.facetSelection.enable();
            return true;
        }

        me.category.set('facetIds', null);
        me.facetSelection.disable();
        me.facetSelection.store.load();
        return true;
    },

    /**
     * @param { Ext.data.Model } category
     *
     * @return { boolean }
     */
    loadCategory: function(category) {
        var me = this, hasSortings, hasFacets;

        me.loadRecord(category);
        me.category = category;
        me.enable();

        hasSortings = (category.get('sortingIds').length > 0);
        me.activateSorting.setValue(hasSortings);

        if (hasSortings) {
            me.sortingSelection.enable();
        } else {
            me.sortingSelection.disable();
            me.sortingSelection.store.load();
        }

        hasFacets = (category.get('facetIds').length > 0);
        me.activateFacets.setValue(hasFacets);

        if (hasFacets) {
            me.facets = category.get('facetIds').split('|').filter(
                function(i) {
                    if (i.length){
                        return i;
                    }
                });
            me.facetSelection.store.sort();
            me.facetSelection.enable();
        } else {
            me.facets = null;
            me.facetSelection.store.sort();
            me.facetSelection.disable();
            me.facetSelection.store.load();
        }

        return true;
    }
});
//{/block}
