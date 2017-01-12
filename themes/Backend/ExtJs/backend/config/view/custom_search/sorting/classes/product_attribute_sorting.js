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

//{block name="backend/config/view/custom_search/sorting/classes/product_attribute_sorting"}

Ext.define('Shopware.apps.Config.view.custom_search.sorting.classes.ProductAttributeSorting', {
    extend: 'Shopware.apps.Config.view.custom_search.sorting.classes.SortingInterface',
    mixins: {
        factory: 'Shopware.attribute.SelectionFactory'
    },

    getLabel: function() {
        return '{s name="product_attribute_sorting"}{/s}';
    },

    supports: function(sortingClass) {
        return (sortingClass.indexOf('Shopware\\Bundle\\SearchBundle\\Sorting\\ProductAttributeSorting') >= 0);
    },

    load: function(sortingClass, parameters, callback) {
        if (!Ext.isFunction(callback)) {
            throw 'Requires provided callback function';
        }
        var record = {
            'class': 'Shopware\\Bundle\\SearchBundle\\Sorting\\ProductAttributeSorting|' + parameters.field,
            'label': parameters.backend_label,
            'parameters': parameters
        };

        callback(record);
    },

    create: function(callback) {
        var me = this;

        if (!Ext.isFunction(callback)) {
            throw 'Requires provided callback function';
        }

        Ext.create('Shopware.apps.Config.view.custom_search.sorting.includes.CreateWindow', {
            title: me.getLabel(),
            height: 180,
            width: 500,
            items: [
                me._createAttributeSelection(),
                { xtype: 'custom-search-direction-combo', labelWidth: 150 }
            ],
            callback: function(values) {
                me._createRecord(values, callback);
            }
        }).show();
    },

    _createAttributeSelection: function() {
        var me = this;

        var store = Ext.create('Ext.data.Store', {
            model: 'Shopware.model.Dynamic',
            proxy: {
                type: 'ajax',
                url: '{url controller="AttributeData" action="list"}',
                reader: Ext.create('Shopware.model.DynamicReader'),
                extraParams: {
                    table: 's_articles_attributes'
                }
            }
        });

        return Ext.create('Shopware.form.field.AttributeSingleSelection', {
            labelWidth: 150,
            name: 'field',
            allowBlank: false,
            fieldLabel: '{s name="product_attribute_sorting_field"}{/s}',
            store: store
        });
    },

    _createRecord: function(parameters, callback) {
        var me = this;

        me._requestLabel(parameters.field, function(label) {
            parameters.backend_label = label;

            callback({
                'class': 'Shopware\\Bundle\\SearchBundle\\Sorting\\ProductAttributeSorting|' + parameters.field,
                'label': label,
                'parameters': parameters
            });
        });
    },

    _requestLabel: function(field, callback) {
        Ext.Ajax.request({
            url: '{url controller=Attributes action=getColumn}',
            params: {
                table: 's_articles_attributes',
                columnName: field
            },
            success: function(operation, opts) {
                var response = Ext.decode(operation.responseText);
                var label = '{s name="product_attribute_sorting_short"}{/s}:';

                response = response.data;

                if (response.label) {
                    label += ' <b>' + response.label + '</b>';
                } else if (response.columnName) {
                    label += ' <b>' + response.columnName + '</b>';
                }

                if (response.helpText) {
                    label += ' <i>[' + response.helpText + ']</i>';
                }

                callback(label);
            }
        });
    },

    _getLabelOfObject: function(values) {
        if (values.label.length > 0) {
            return values.label + ' - '+ values.columnName;
        }
        return values.columnName;
    }
});

//{/block}
