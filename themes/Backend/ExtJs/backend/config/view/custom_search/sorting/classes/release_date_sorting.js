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
 */

//{namespace name="backend/custom_search/translation"}

//{block name="backend/config/view/custom_search/sorting/classes/release_date_sorting"}

Ext.define('Shopware.apps.Config.view.custom_search.sorting.classes.ReleaseDateSorting', {
    extend: 'Shopware.apps.Config.view.custom_search.sorting.classes.SortingInterface',

    getLabel: function() {
        return '{s name="release_date_sorting"}{/s}';
    },

    supports: function(sortingClass) {
        return (sortingClass == 'Shopware\\Bundle\\SearchBundle\\Sorting\\ReleaseDateSorting');
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
                    return '{s name="release_date_sorting_asc"}{/s}';
                },
                getDescendingLabel: function() {
                    return '{s name="release_date_sorting_desc"}{/s}';
                }
            }],
            callback: function(values) {
                callback(me._createRecord(values));
            }
        }).show();
    },

    _createRecord: function(parameters) {
        var label = '{s name="release_date_sorting_asc"}{/s}';

        if (parameters.direction == 'DESC') {
            label = '{s name="release_date_sorting_desc"}{/s}';
        }

        return {
            'class': 'Shopware\\Bundle\\SearchBundle\\Sorting\\ReleaseDateSorting',
            'label': label,
            'parameters': parameters
        };
    }
});

//{/block}
