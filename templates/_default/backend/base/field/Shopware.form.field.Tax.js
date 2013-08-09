
Ext.define('Shopware.form.field.Tax', {
    extend: 'Ext.form.field.ComboBox',

    initComponent: function() {
        var me = this;
        me.fieldLabel = 'Steuersatz';

        me.callParent(arguments);
    }

});