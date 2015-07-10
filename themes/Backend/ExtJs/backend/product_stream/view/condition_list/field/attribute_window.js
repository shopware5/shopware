
Ext.define('Shopware.apps.ProductStream.view.condition_list.field.AttributeWindow', {
    extend: 'Ext.window.Window',
    modal: true,
    bodyPadding: 10,
    width: 400,

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    applyCallback: function(field) { },

    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.dockedItems = [me.createToolbar()];
        me.callParent(arguments);
    },

    createItems: function() {
        var me = this;

        var store = Ext.create('Shopware.apps.ProductStream.store.Attribute');

        me.attributeCombo = Ext.create('Ext.form.field.ComboBox', {
            name: 'attribute',
            fieldLabel: 'Select product attribute',
            pageSize: 20,
            store: store,
            valueField: 'column',
            allowBlank: false,
            displayField: 'column'
        });

        me.notice = Ext.create('Ext.container.Container', {
            html: 'Bitte wählen Sie zunächst ein Produkt Attribute aus',
            height: 40
        });

        return [me.notice, me.attributeCombo];
    },

    createToolbar: function() {
        var me = this;
        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: ['->', {
                xtype: 'button',
                text: 'Apply',
                cls: 'primary',
                handler: function() {
                    if (me.attributeCombo.getValue()) {
                        me.applyCallback(me.attributeCombo.getValue());
                        me.destroy();
                    }
                }
            }]
        });
    }
});
