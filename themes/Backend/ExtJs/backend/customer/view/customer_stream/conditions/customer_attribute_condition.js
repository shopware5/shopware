/* global Ext */
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

// {namespace name="backend/customer_stream/translation"}

Ext.define('Shopware.apps.Customer.view.customer_stream.conditions.CustomerAttributeCondition', {

    mixins: {
        factory: 'Shopware.attribute.SelectionFactory'
    },

    getLabel: function() {
        return '{s name="customer_attribute_condition"}{/s}';
    },

    supports: function(conditionClass) {
        return (conditionClass === 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\CustomerAttributeCondition');
    },

    create: function(callback) {
        var me = this;
        Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.field.AttributeWindow', {
            applyCallback: function (attribute) {
                callback(me._create(attribute));
            }
        }).show();
    },

    load: function(conditionClass, items, callback) {
        callback(this._create(items.field));
    },

    _create: function(attribute) {
        var operatorField = this.createOperatorField();

        var valueField = Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.field.AttributeValue', {
            name: 'value',
            operatorField: operatorField
        });

        return {
            title: this.getLabel() + ' [' + attribute + ']',
            conditionClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\CustomerAttributeCondition',
            items: [
                { xtype: 'hidden', name: 'field', value: attribute },
                operatorField,
                valueField
            ]
        };
    },

    createOperatorField: function () {
        var me = this;
        me.operatorSelection = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=attribute/operator}Operator:{/s}',
            store: me.operatorStore(),
            displayField: 'name',
            valueField: 'value',
            allowBlank: false,
            name: 'operator'
        });
        return me.operatorSelection;
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
    }
});
