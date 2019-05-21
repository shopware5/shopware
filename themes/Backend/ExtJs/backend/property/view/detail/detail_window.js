
//{namespace name=backend/property/view/main}

Ext.define('Shopware.apps.Property.view.detail.DetailWindow', {
    extend: 'Enlight.app.Window',
    height: 450,
    width: 700,
    layout: 'fit',
    translationType: '',
    fields: [],
    attributeTable: '',
    successNotification: '',

    initComponent: function() {
        var me = this;
        me.items = me.createItems();
        me.dockedItems = [me.createToolbar()];
        me.callParent(arguments);
        me.formPanel.loadRecord(me.record);
    },

    createItems: function() {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            plugins: [{
                ptype: 'translation',
                translationType: me.translationType
            }],
            autoScroll: true,
            cls: 'shopware-form',
            layout: 'anchor',
            bodyPadding: 20,
            defaults: { anchor: '100%', labelWidth: 155 },
            items: me.createFields()
        });

        me.attributeForm = Ext.create('Shopware.attribute.Form', {
            table: me.attributeTable,
            allowTranslation: false,
            translationForm: me.formPanel,
            margin: '20 0 0'
        });
        me.attributeForm.loadAttribute(me.record.get('id'));
        me.formPanel.add(me.attributeForm);

        return [me.formPanel];
    },

    createFields: function() {
        return this.fields;
    },

    createToolbar: function() {
        var me = this;
        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            items: ['->', {
                xtype: 'button',
                text: '{s name="cancel_button"}{/s}',
                cls: 'secondary',
                handler: function() {
                    me.destroy();
                }
            }, {
                xtype: 'button',
                text: '{s name="save_button"}{/s}',
                cls: 'primary',
                handler: function() {
                    me.saveRecord();
                }
            }]
        });
    },

    saveRecord: function() {
        var me = this;

        if (!me.formPanel.getForm().isValid()) {
            return;
        }

        me.formPanel.getForm().updateRecord(me.record);
        me.record.save({
            callback: function() {
                if (me.successNotification) {
                    Shopware.Notification.createGrowlMessage('', me.successNotification);
                }

                me.attributeForm.saveAttribute(me.record.get('id'));
                me.destroy();
                me.fireEvent('record-saved', me.record);
            }
        });
    }
});
