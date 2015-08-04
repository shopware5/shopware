
Ext.define('Shopware.apps.ProductStream.view.condition_list.field.Sales', {

    extend: 'Ext.form.FieldContainer',
    layout: { type: 'hbox', align: 'stretch' },
    mixins: [ 'Ext.form.field.Base' ],
    height: 30,
    value: undefined,

    initComponent: function() {
        var me = this;
        me.items = me.createItems();
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;
        return [
            me.createMinSales()
        ];
    },

    createMinSales: function() {
        var me = this;

        me.minSales = Ext.create('Ext.form.field.Number', {
            labelWidth: 150,
            fieldLabel: 'Minimum Sales',
            allowBlank: false,
            minValue: 1,
            value: null,
            padding: '0 0 0 10',
            flex: 1
        });

        return me.minSales;
    },

    getValue: function() {
        return this.value;
    },

    setValue: function(value) {
        var me = this;

        me.value = value;

        if (!Ext.isObject(value)) {
            me.minSales.setValue(1);
            return;
        }

        if (value.hasOwnProperty('minSales')) {
            me.minSales.setValue(value.minSales);
        }
    },

    getSubmitData: function() {
        var value = {};

        value[this.name] = {
            minSales: this.minSales.getValue()
        };
        return value;
    }
});
