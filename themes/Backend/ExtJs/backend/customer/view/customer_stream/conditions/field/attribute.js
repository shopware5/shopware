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

//{namespace name="backend/customer_stream/translation"}

Ext.define('Shopware.apps.Customer.view.customer_stream.conditions.field.Attribute', {

    extend: 'Ext.form.FieldContainer',
    layout: { type: 'vbox', align: 'stretch' },
    mixins: {
        formField: 'Ext.form.field.Base'
    },
    value: undefined,
    attributeField: null,

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    createItems: function () {
        var me = this;

        return [
            me.createOperatorField(),
            me.createValueField(),
            me.createBetweenContainer()
        ];
    },

    getValue: function() {
        var me = this;

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
        } else if (value.operator === 'IN') {
            value.value = value.value.split(",");
        }

        return value;
        // this.value;
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
        var result = {};
        return this.getValue();
        return result;
    },

    operatorStore: function () {
        return Ext.create('Ext.data.Store', {
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
                { name: '{s name=attribute_condition/starts_with}starts with{/s}', value: 'STARTS_WITH' },
                { name: '{s name=attribute_condition/ends_with}ends with{/s}', value: 'ENDS_WITH' },
                { name: '{s name=attribute_condition/like}like{/s}', value: 'CONTAINS' }
            ]
        });
    },

    createOperatorField: function () {
        var me = this;
        me.operatorSelection = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=attribute/operator}Operator:{/s}',
            store: me.operatorStore(),
            displayField: 'name',
            valueField: 'value',
            allowBlank: false,
            name: 'operator',
            listeners: {
                change: function (field, value) {
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

    createFromField: function() {
        var me = this;
        me.fromField = Ext.create('Ext.form.field.Number', {
            fieldLabel: '{s name=attribute/from_text}From{/s}',
            flex: 1,
            listeners: {
                change: function() {
                    me.toField.setMinValue(this.getValue() + 1);
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
                    me.fromField.setMaxValue(this.getValue() - 1);
                }
            }
        });
        return me.toField;
    },

    createValueField: function () {
        var me = this;
        me.valueField = Ext.create('Ext.form.field.Text', {
            fieldLabel: '{s name=attribute/value}Value:{/s}',
            allowBlank: false,
            name: 'value'
        });
        return me.valueField;
    },

    createBetweenContainer: function () {
        var me = this;
        me.betweenContainer = Ext.create('Ext.container.Container', {
            layout: { type: 'hbox', alaign: 'strech' },
            hidden: true,
            items: [me.createFromField(), me.createToField()]
        });
        return me.betweenContainer;
    }
});