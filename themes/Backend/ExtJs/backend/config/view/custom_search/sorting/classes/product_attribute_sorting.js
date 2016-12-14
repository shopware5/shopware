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

//{block name="backend/config/view/custom_search/sorting/classes/product_attribute_sorting"}

Ext.define('Shopware.apps.Config.view.custom_search.sorting.classes.ProductAttributeSorting', {
    extend: 'Shopware.apps.Config.view.custom_search.sorting.classes.AbstractSorting',
    mixins: {
        factory: 'Shopware.attribute.SelectionFactory'
    },

    getLabel: function() {
        return '{s name="product_attribute_sorting"}{/s}';
    },

    load: function(sortingClass, parameters) {
        if (sortingClass.indexOf('Shopware\\Bundle\\SearchBundle\\Sorting\\ProductAttributeSorting') < 0) {
            return null;
        }
        return this._createRecord(parameters);
    },

    create: function(callback) {
        var me = this;

        if (!Ext.isFunction(callback)) {
            throw 'Requires provided callback function';
        }

        Ext.create('Shopware.apps.Config.view.custom_search.sorting.includes.CreateWindow', {
            title: me.getLabel(),
            height: 180,
            width: 390,
            items: [
                me._createAttributeSelection(),
                { xtype: 'custom-search-direction-combo', labelWidth: 150 }
            ],
            callback: function(values) {
                callback(me._createRecord(values));
            }
        }).show();
    },

    _createAttributeSelection: function() {
        var me = this;

        return {
            xtype: 'combobox',
            name: 'field',
            labelWidth: 150,
            fieldLabel: '{s name="product_attribute_sorting_field"}{/s}',
            pageSize: 20,
            store: me.createEntitySearchStore("Shopware\\Models\\Attribute\\Configuration"),
            valueField: 'columnName',
            allowBlank: false,
            tpl: Ext.create('Ext.XTemplate',
                '<tpl for="."><div class="x-boundlist-item">{literal}{[this.getRecordLabel(values)]}{/literal}</div></tpl>',
                {
                    getRecordLabel: function(values) {
                        return me._getLabelOfObject(values);
                    }
                }
            ),
            displayTpl: Ext.create('Ext.XTemplate',
                '<tpl for=".">{literal}{[this.getRecordLabel(values)]}{/literal}</tpl>',
                {
                    getRecordLabel: function(values) {
                        return me._getLabelOfObject(values);
                    }
                }
            )
        };
    },

    _createRecord: function(parameters) {
        return {
            'class': 'Shopware\\Bundle\\SearchBundle\\Sorting\\ProductAttributeSorting|' + parameters.field,
            'label': '{s name="product_attribute_sorting_short"}{/s} - ' + parameters.field + ' [' + parameters.direction + ']',
            'parameters': parameters
        };
    },

    _getLabelOfObject: function(values) {
        if (values.label.length > 0) {
            return values.label + ' - '+ values.columnName;
        }
        return values.columnName;
    }
});

//{/block}
