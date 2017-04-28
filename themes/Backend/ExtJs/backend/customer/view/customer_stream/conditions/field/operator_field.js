
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