
//{literal}
Ext.define('Shopware.filter.Field', {
    extend: 'Ext.form.FieldContainer',

    padding: 10,

    layout: {
        type: 'hbox',
        align: 'stretch'
    },
    style: 'background: #fff',

    initComponent: function() {
        var me = this;

        me.checkbox = Ext.create('Ext.form.field.Checkbox', {
            width: 28,
            margin: '2 0 0 0'
        });

        me.checkbox.on('change', function(checkbox, value) {
            var field = me.items.items[1];
            if (value) {
                field.enable();
            } else {
                field.disable()
            }
        });

        me.field.flex = 1;
        me.field.labelWidth = 100;
        me.field.disabled = true;
        me.field.margin = 0;

        me.items = [
            me.checkbox,
            me.field
        ];

        me.callParent(arguments);
    }
});
//{/literal}