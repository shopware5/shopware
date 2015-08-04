
Ext.define('Shopware.apps.ProductStream.view.condition_list.field.VoteAverage', {

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
            me.createAverage()
        ];
    },

    createAverage: function() {
        var me = this;

        me.average = Ext.create('Ext.form.field.Number', {
            labelWidth: 150,
            fieldLabel: 'Minimum Average',
            allowBlank: false,
            minValue: 1,
            value: 3,
            decimalPrecision: 1,
            maxValue: 5,
            padding: '0 0 0 10',
            flex: 1
        });
        return me.average;
    },

    getValue: function() {
        return this.value;
    },

    setValue: function(value) {
        var me = this;

        me.value = value;

        if (!Ext.isObject(value)) {
            me.average.setValue(1);
            return;
        }

        if (value.hasOwnProperty('average')) {
            me.average.setValue(value.average);
        }
    },

    getSubmitData: function() {
        var value = {};

        value[this.name] = {
            average: this.average.getValue()
        };
        return value;
    }
});
