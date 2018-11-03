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

//{block name="backend/config/view/custom_search/sorting/classes/product_stock_sorting"}

Ext.define('Shopware.apps.Config.view.custom_search.sorting.classes.ProductStockSorting', {
    extend: 'Shopware.apps.Config.view.custom_search.sorting.classes.SortingInterface',

    getLabel: function () {
        return '{s name="product_stock_sorting"}{/s}';
    },

    supports: function (sortingClass) {
        return sortingClass == 'Shopware\\Bundle\\SearchBundle\\Sorting\\ProductStockSorting';
    },

    load: function (sortingClass, parameters, callback) {
        var me = this,
            callback = callback || Ext.emptyFn;

        callback(me.createRecord(parameters));
    },

    create: function (callback) {
        var me = this,
            callback = callback || Ext.emptyFn,
            comp = Ext.create('Shopware.apps.Config.view.custom_search.sorting.includes.CreateWindow', {
                title: me.getLabel(),
                items: [{
                    xtype: 'custom-search-direction-combo',
                    getAscendingLabel: function () {
                        return '{s name="product_stock_asc"}{/s}';
                    },
                    getDescendingLabel: function () {
                        return '{s name="product_stock_desc"}{/s}';
                    }
                }],
                callback: function (values, s) {
                    return callback(me.createRecord(values));
                }
            }).show();

        return comp;
    },

    createRecord: function (parameters) {
        return {
            'class': 'Shopware\\Bundle\\SearchBundle\\Sorting\\ProductStockSorting',
            'label': parameters.direction.toLowerCase() === 'desc' ? '{s name="product_stock_desc"}{/s}' : '{s name="product_stock_asc"}{/s}',
            'parameters': parameters
        };
    }
});

//{/block}
