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

//{block name="backend/config/view/custom_search/sorting/detail"}

Ext.define('Shopware.apps.Config.view.custom_search.sorting.Detail', {
    extend: 'Shopware.model.Container',
    alias: 'widget.config-custom-sorting-detail',
    mixins: {
        factory: 'Shopware.attribute.SelectionFactory'
    },

    configure: function() {
        return {
            splitFields: false,
            fieldSets: [{
                title: null,
                border: false,
                fields: {
                    label: {
                        allowBlank: false,
                        fieldLabel: '{s name="sorting_label"}{/s}',
                        translatable: true,
                        helpText: '{s name="sorting_label_help"}{/s}'
                    },
                    active: {
                        fieldLabel: '{s name="active"}{/s}',
                        helpText: '{s name="active_help"}{/s}'
                    },
                    displayInCategories: {
                        fieldLabel: '{s name="display_in_categories"}{/s}',
                        helpText: '{s name="display_in_categories_help"}{/s}'
                    },
                    sortings: {
                        allowBlank: false,
                        xtype: 'custom-search-sorting-selection',
                        fieldLabel: '{s name="sortings"}{/s}',
                        helpText: '{s name="sortings_help"}{/s}',
                        height: 180
                    }
                }
            }]
        };
    }
});

//{/block}
