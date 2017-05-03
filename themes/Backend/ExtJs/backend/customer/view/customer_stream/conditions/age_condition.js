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
 * @package    Customer
 * @subpackage CustomerStream
 * @version    $Id$
 * @author shopware AG
 */

// {namespace name=backend/customer/view/main}
// {block name="backend/customer/view/customer_stream/conditions/age_condition"}
Ext.define('Shopware.apps.Customer.view.customer_stream.conditions.AgeCondition', {

    getLabel: function() {
        return '{s name="age"}{/s}';
    },

    supports: function(conditionClass) {
        return (conditionClass == 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\AgeCondition');
    },

    create: function(callback) {
        callback(this._create());
    },

    load: function(conditionClass, items, callback) {
        callback(this._create(items.field));
    },

    _create: function() {
        var me = this;
        var operatorField = Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.field.OperatorField', {
            allowedOperators: ['=','<','<=','BETWEEN','>','>=']
        });


        var valueField = Ext.create('Shopware.apps.Customer.view.customer_stream.conditions.field.AttributeValue', {
            name: 'value',
            operatorField: operatorField,
            createValueField: me.createValueField
        });

        return {
            title: '{s name="age"}{/s}',
            conditionClass: 'Shopware\\Bundle\\CustomerSearchBundle\\Condition\\AgeCondition',
            items: [
                operatorField,
                valueField
            ]
        };
    },

    createOperatorField: function () {
        var me = this;
        me.operatorSelection = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: '{s name=operator}{/s}',
            store: me.createOperatorStore(),
            displayField: 'name',
            valueField: 'value',
            allowBlank: false,
            name: 'operator'
        });
        return me.operatorSelection;
    },

    createOperatorStore: function () {
        return Ext.create('Ext.data.Store', {
            fields: [ 'name', 'value' ],
            data: [
                { name: '{s name=equals}{/s}', value: '=' },
                { name: '{s name=less_than}{/s}', value: '<' },
                { name: '{s name=less_than_equals}{/s}', value: '<=' },
                { name: '{s name=between}{/s}', value: 'BETWEEN' },
                { name: '{s name=greater_than}{/s}', value: '>' },
                { name: '{s name=greater_than_equals}{/s}', value: '>=' }
            ]
        });
    },

    createValueField: function () {
        var me = this;
        me.valueField =  Ext.create('Ext.form.field.Number', {
            fieldLabel: '{s name=attribute/value}{/s}',
            allowBlank: false,
            name: 'value'
        });
        return me.valueField;
    }
});
// {/block}
