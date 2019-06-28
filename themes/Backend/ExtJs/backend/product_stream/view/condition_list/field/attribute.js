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
 *
 * @category   Shopware
 * @package    ProductStream
 * @subpackage Window
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/product_stream/main}
//{block name="backend/product_stream/view/condition_list/field/attribute"}
Ext.define('Shopware.apps.ProductStream.view.condition_list.field.Attribute', {

    extend: 'Ext.form.FieldContainer',
    layout: { type: 'vbox', align: 'stretch' },
    mixins: [ 'Ext.form.field.Base' ],
    height: 70,
    value: undefined,
    attributeField: null,

    initComponent: function() {
        var me = this;
        me.items = me.createItems();
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;

        return [
            me.createOperatorSelection(),
            me.createValueField(),
            me.createBetweenContainer()
        ];
    },

    createBetweenContainer: function() {
        var me = this;

        me.betweenContainer = Ext.create('Ext.container.Container', {
            layout: { type: 'hbox', align: 'stretch' },
            hidden: true,
            items: [ me.createFromField(), me.createToField() ]
        });
        return me.betweenContainer;
    },

    createFromField: function() {
        var me = this;

        me.fromField = Ext.create('Ext.form.field.Number', {
            fieldLabel: '{s name=attribute/from_text}From{/s}',
            flex: 1,
            listeners: {
                change: function() {
                    me.toField.setMinValue(me.fromField.getValue() -1);
                }
            }
        });
        return me.fromField;
    },

    createToField: function() {
        var me = this;

        me.toField = Ext.create('Ext.form.field.Number', {
            labelWidth: 50,
            fieldLabel: '{s name=attribute/to_text}to{/s}',
            padding: '0 0 0 10',
            flex: 1,
            listeners: {
                change: function() {
                    me.fromField.setMaxValue(me.toField.getValue() + 1);
                }
            }
        });
        return me.toField;
    },

    createOperatorSelection: function () {
        var me = this;

        var store = Ext.create('Ext.data.Store', {
            fields: [ 'name', 'value' ],
            data: [
                { name: '{s name=attribute_condition/equals}equals{/s}', value: '=' },
                { name: '{s name=attribute_condition/not_equals}not equals{/s}', value: '!=' },
                { name: '{s name=attribute_condition/less_than}less than{/s}', value: '<' },
                { name: '{s name=attribute_condition/less_than_equals}less than equals{/s}', value: '<=' },
                { name: '{s name=attribute_condition/between}between{/s}', value: 'BETWEEN' },
                { name: '{s name=attribute_condition/greater_than}greater than{/s}', value: '>' },
                { name: '{s name=attribute_condition/greater_than_equals}greater than equals{/s}', value: '>=' },
                { name: '{s name=attribute_condition/in}in{/s}', value: 'IN' },
                { name: '{s name=attribute_condition/not_in}not in{/s}', value: 'NOT IN' },
                { name: '{s name=attribute_condition/starts_with}starts with{/s}', value: 'STARTS_WITH' },
                { name: '{s name=attribute_condition/ends_with}ends with{/s}', value: 'ENDS_WITH' },
                { name: '{s name=attribute_condition/like}like{/s}', value: 'CONTAINS' }
            ]
        });

        me.operatorSelection = Ext.create('Ext.form.field.ComboBox', {
            store: store,
            fieldLabel: '{s name=operator}Operator{/s}',
            displayField: 'name',
            valueField: 'value',
            allowBlank: false,
            listeners: {
                change: function(field, value) {
                    if (value === 'BETWEEN') {
                        me.betweenContainer.show();
                        me.valueField.hide();
                    } else {
                        me.betweenContainer.hide();
                        me.valueField.show();
                    }
                }
            }
        });

        return me.operatorSelection;
    },

    createValueField: function () {
        var me = this;

        me.valueField = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=value}Value{/s}',
        });

        return me.valueField;
    },

    getValue: function() {
        return this.value;
    },

    setValue: function(value) {
        var me = this;

        me.value = value;
        if (Ext.isObject(value)) {
            me.attributeField = value.field;

            me.operatorSelection.setValue(value.operator);

            if (value.operator === 'BETWEEN') {
                me.fromField.setValue(value.value.min);
                me.toField.setValue(value.value.max);
            } else {
                me.valueField.setValue(value.value);
            }
        }
    },

    getSubmitData: function() {
        var value = {
            field: this.attributeField,
            operator: this.operatorSelection.getValue(),
            value: this.valueField.getValue()
        };

        if (value.operator === 'BETWEEN') {
            value.value = {
                min: this.fromField.getValue(),
                max: this.toField.getValue()
            }
        } else if (value.operator === 'IN' || value.operator === 'NOT IN') {
            value.value = value.value.split(",");
        }

        var result = {};
        result[this.name] = value;
        return result;
    }
});
//{/block}
