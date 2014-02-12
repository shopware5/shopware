
Ext.define('Shopware.apps.Theme.view.detail.elements.CheckboxField', {
    extend: 'Ext.form.field.Checkbox',
    alias: 'widget.theme-checkbox-field',
    inputValue: true,
    uncheckedValue: false,

    initComponent: function() {
        var me = this;

        console.log("me check", me);
        return me.callParent(arguments);
    }
});
