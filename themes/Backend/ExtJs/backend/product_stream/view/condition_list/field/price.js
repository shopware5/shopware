
Ext.define('Shopware.apps.ProductStream.view.condition_list.field.Price', {

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
            me.createFromField(),
            me.createToField()
        ];
    },

    createFromField: function() {
        var me = this;

        me.fromField = Ext.create('Ext.form.field.Number', {
            fieldLabel: 'from',
            minValue: 0,
            labelWidth: 30,
            flex: 1,
            listeners: {
                change: function() {
                    me.toField.setMinValue(me.fromField.getValue());
                }
            }
        });
        return me.fromField;
    },

    createToField: function() {
        var me = this;

        me.toField = Ext.create('Ext.form.field.Number', {
            labelWidth: 30,
            fieldLabel: 'to',
            minValue: 0,
            padding: '0 0 0 10',
            flex: 1,
            listeners: {
                change: function() {
                    me.fromField.setMaxValue(me.toField.getValue());
                }
            }
        });
        return me.toField;
    },

    getValue: function() {
        return this.value;
    },

    setValue: function(value) {
        var me = this;

        me.value = value;

        if (!Ext.isObject(value)) {
            me.fromField.setValue(null);
            me.toField.setValue(null);
            return;
        }


        if (value.hasOwnProperty('minPrice')) {
            me.fromField.setValue(value.minPrice);
        }
        if (value.hasOwnProperty('maxPrice')) {
            me.toField.setValue(value.maxPrice);
        }
    },

    getSubmitData: function() {
        var value = {};

        value[this.name] = {
            minPrice: this.fromField.getValue(),
            maxPrice: this.toField.getValue()
        };
        return value;
    },

    validate: function() {
        var valid = (this.fromField.getValue() !== null || this.toField.getValue());

        if (!valid) {
            Shopware.Notification.createGrowlMessage('Validation', this.getErrorMessage());
        }

        return valid;
    },

    getErrorMessage: function() {
        return 'Price range requires at least one value';
    }
});
