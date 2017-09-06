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

//{block name="backend/config/view/custom_search/sorting/classes/search_ranking_sorting"}

Ext.define('Shopware.apps.Config.view.custom_search.sorting.classes.SearchRankingSorting', {
    extend: 'Shopware.apps.Config.view.custom_search.sorting.classes.SortingInterface',

    getLabel: function() {
        return '{s name="search_ranking_sorting"}{/s}';
    },

    supports: function(sortingClass) {
        return (sortingClass == 'Shopware\\Bundle\\SearchBundle\\Sorting\\SearchRankingSorting');
    },

    load: function(sortingClass, parameters, callback) {
        if (!Ext.isFunction(callback)) {
            throw 'Requires provided callback function';
        }
        callback(this._createRecord());
    },

    create: function(callback) {
        var me = this;

        if (!Ext.isFunction(callback)) {
            throw 'Requires provided callback function';
        }

        callback(me._createRecord());
    },

    _createRecord: function() {
        return {
            'class': 'Shopware\\Bundle\\SearchBundle\\Sorting\\SearchRankingSorting',
            'label': '{s name="search_ranking_sorting"}{/s}',
            'parameters': {
                'direction': 'DESC'
            }
        };
    }
});

//{/block}
