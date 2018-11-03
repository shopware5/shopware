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

//{block name="backend/config/view/custom_search/sorting/classes/price_sorting"}

Ext.define('Shopware.apps.Config.view.custom_search.sorting.classes.PriceSorting', {
    extend: 'Shopware.apps.Config.view.custom_search.sorting.classes.SortingInterface',

    getLabel: function() {
        return '{s name="price_sorting"}{/s}';
    },

    supports: function(sortingClass) {
        return (sortingClass == 'Shopware\\Bundle\\SearchBundle\\Sorting\\PriceSorting');
    },

    load: function(sortingClass, parameters, callback) {
        if (!Ext.isFunction(callback)) {
            throw 'Requires provided callback function';
        }
        callback(this._createRecord(parameters));
    },

    create: function(callback) {
        var me = this;

        if (!Ext.isFunction(callback)) {
            throw 'Requires provided callback function';
        }

        Ext.create('Shopware.apps.Config.view.custom_search.sorting.includes.CreateWindow', {
            title: me.getLabel(),
            items: [{
                xtype: 'custom-search-direction-combo',
                getAscendingLabel: function() {
                    return '{s name="price_sorting_asc"}{/s}';
                },
                getDescendingLabel: function() {
                    return '{s name="price_sorting_desc"}{/s}';
                }
            }],
            callback: function(values) {
                callback(me._createRecord(values));
            }
        }).show();
    },

    _createRecord: function(parameters) {
        var label = '{s name="price_sorting_asc"}{/s}';

        if (parameters.direction == 'DESC') {
            label = '{s name="price_sorting_desc"}{/s}';
        }

        return {
            'class': 'Shopware\\Bundle\\SearchBundle\\Sorting\\PriceSorting',
            'label': label,
            'parameters': parameters
        };
    }
});

//{/block}
