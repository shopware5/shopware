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
// {block name="backend/customer/view/customer_stream/conditions/field/operator_field"}
Ext.define('Shopware.apps.Customer.view.customer_stream.conditions.field.OperatorField', {
    extend: 'Ext.form.field.ComboBox',
    alias: 'widget.condition-operator-selection',

    fieldLabel: '{s name=operator}{/s}',
    displayField: 'name',
    valueField: 'value',
    allowBlank: false,
    name: 'operator',

    allowedOperators: ['=','!=','<','<=','BETWEEN','>','>=','IN','STARTS_WITH','ENDS_WITH','CONTAINS'],

    initComponent: function() {
        var me = this;

        if (!me.store) {
            me.store = me.createStore();
        }

        me.callParent(arguments);
    },

    createStore: function() {
        var me = this;

        return Ext.create('Ext.data.Store', {
            fields: [ 'name', 'value' ],
            data: me.getOperators()
        });
    },

    getOperators: function() {
        var me = this;

        var operators = [
            { name: '{s name=equals}{/s}', value: '=' },
            { name: '{s name=not_equals}{/s}', value: '!=' },
            { name: '{s name=less_than}{/s}', value: '<' },
            { name: '{s name=less_than_equals}{/s}', value: '<=' },
            { name: '{s name=between}{/s}', value: 'BETWEEN' },
            { name: '{s name=greater_than}{/s}', value: '>' },
            { name: '{s name=greater_than_equals}{/s}', value: '>=' },
            { name: '{s name=in}{/s}', value: 'IN' },
            { name: '{s name=starts_with}{/s}', value: 'STARTS_WITH' },
            { name: '{s name=ends_with}{/s}', value: 'ENDS_WITH' },
            { name: '{s name=like}{/s}', value: 'CONTAINS' },
        ];

        var filtered = [];
        Ext.each(operators, function(operator) {
            if (me.allowedOperators.indexOf(operator.value) >= 0) {
                filtered.push(operator);
            }
        });

        return filtered;
    }
});

// {/block}
