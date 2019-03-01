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

///{namespace name=backend/custom_search/translation}

//{block name="backend/config/view/custom_search/sorting/sorting/selection"}

Ext.define('Shopware.apps.Config.view.custom_search.sorting.SortingSelection', {
    extend: 'Shopware.form.field.Grid',
    alias: 'widget.custom-search-sorting-selection',
    mixins: {
        factory: 'Shopware.attribute.SelectionFactory'
    },

    initComponent: function() {
        var me = this;

        me.sortingHandlers = me.sort(
            me.initSortings()
        );
        me.searchStore = me.createSearchStore();

        me.callParent(arguments);
    },

    sort: function(sortings) {
        return sortings.sort(function(a, b) {
            return a.getLabel().localeCompare(b.getLabel());
        });
    },

    initSortings: function() {
        return [
            Ext.create('Shopware.apps.Config.view.custom_search.sorting.classes.PriceSorting'),
            Ext.create('Shopware.apps.Config.view.custom_search.sorting.classes.ProductNameSorting'),
            Ext.create('Shopware.apps.Config.view.custom_search.sorting.classes.ProductNumberSorting'),
            Ext.create('Shopware.apps.Config.view.custom_search.sorting.classes.PopularitySorting'),
            Ext.create('Shopware.apps.Config.view.custom_search.sorting.classes.ReleaseDateSorting'),
            Ext.create('Shopware.apps.Config.view.custom_search.sorting.classes.SearchRankingSorting'),
            Ext.create('Shopware.apps.Config.view.custom_search.sorting.classes.ProductAttributeSorting'),
            Ext.create('Shopware.apps.Config.view.custom_search.sorting.classes.ProductStockSorting'),
            Ext.create('Shopware.apps.Config.view.custom_search.sorting.classes.ManualSorting')
        ];
    },

    initializeStore: function() {
        return this.store = Ext.create('Ext.data.Store', {
            fields: ['label', 'class', 'parameters']
        });
    },

    createSearchStore: function() {
        var me = this,
            data = [];

        Ext.each(me.sortingHandlers, function(item) {
            data.push({
                class: item,
                label: item.getLabel()
            });
        });

        return Ext.create('Ext.data.Store', {
            fields: ['label', 'class', 'parameters'],
            data: data,
            pageSize: null
        });
    },

    onSelect: function(combo, records) {
        var me = this;

        combo.clearValue();

        Ext.each(records, function(comboRecord) {
            var handler = comboRecord.get('class');

            handler.create(function(record) {
                if (me.recordExists(record.class)) {
                    Shopware.Notification.createGrowlMessage('', '{s name="singleton_error"}{/s}');
                    return;
                }
                me.store.add(record);
            });
        });
    },

    onBeforeSelect: function(combo, records) {
        return true;
    },

    recordExists: function(sortingClass) {
        var found = false;

        this.store.each(function(item, index) {
            if (item.get('class') == sortingClass) {
                found = item;
            }
        });
        return found;
    },

    setValue: function(value) {
        var me = this;

        me.store.removeAll();
        if (!value) {
            return;
        }

        try {
            var sorting = Ext.JSON.decode(value);
        } catch (e) {
            throw 'Sorting selection can not be decoded';
        }

        me.loadSortings(sorting);
    },

    loadSortings: function(sortings, callback) {
        var me = this,
            parameters,
            handler;

        for (var sortingClass in sortings) {
            parameters = sortings[sortingClass];
            handler = me.getHandler(sortingClass, parameters);

            if (handler) {
                handler.load(sortingClass, parameters, function(sorting) {
                    me.store.add(sorting);
                });
            } else {
                me.store.add({
                    label: sortingClass,
                    parameters: parameters
                });
            }
        }
    },

    getHandler: function(sortingClass, parameters) {
        var me = this,
            supportedHandler = null;

        Ext.each(me.sortingHandlers, function(handler) {
            if (handler.supports(sortingClass, parameters)) {
                supportedHandler = handler;
                return false;
            }
        });

        return supportedHandler;
    },

    getValue: function() {
        var me = this,
            recordData = { };

        me.store.each(function(item) {
            recordData[item.get('class')] = item.get('parameters');
        });

        if (recordData.length <= 0) {
            return null;
        }
        return Ext.JSON.encode(recordData);
    }
});

//{/block}
