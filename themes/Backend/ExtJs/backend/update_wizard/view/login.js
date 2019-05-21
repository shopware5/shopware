
//{namespace name=backend/update_wizard/translation}
Ext.define('Shopware.apps.UpdateWizard.view.Login', {
    extend: 'Ext.panel.Panel',
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    alias: 'widget.update-wizard-login',
    border: false,
    background: '#fff',

    padding: 20,

    initComponent: function() {
        var me = this;

        me.items = [
            {
                xtype: 'container',
                flex: 1,
                layout: { type: 'vbox', align: 'stretch' },
                items: [
                    me.createHeadline(),
                    me.createForm(),
                    me.createFooter()
                ]
            },
            me.createToolbar()
        ];

        me.callParent(arguments);
    },

    createHeadline: function() {
        var me = this;

        return Ext.create('Ext.Component', {
            html: '{s name="login_header_notice"}{/s}',
            cls: 'headline text'
        });
    },

    createFooter: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            html: '{s name="login_footer_notice"}{/s}',
            cls: 'update-wizard-login-footer text'
        });
    },

    createForm: function() {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            border: false,
            padding: '20 0',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            cls: 'form-panel',
            items: [
                me.createShopwareIdField(),
                me.createPasswordField()
            ]
        });

        return me.formPanel;
    },

    createToolbar: function() {
        var me = this;

        var cancelButton = Ext.create('PluginManager.container.Container', {
            html: '{s name="login_skip"}{/s}',
            cls: 'plugin-manager-action-button',
            handler: function() {
                me.fireEvent('close-update-wizard');
            }
        });

        var applyButton = Ext.create('PluginManager.container.Container', {
            html: '{s name="login_accept"}{/s}',
            cls: 'plugin-manager-action-button primary',
            minWidth: 255,
            handler: function() {
                me.applyLogin();
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            cls: 'update-wizard-toolbar',
            padding: '20 0',
            background: '#fff',
            height: 120,
            items: [ cancelButton ,'->', applyButton]
        });
    },

    applyLogin: function() {
        var me = this;

        if (!me.formPanel.getForm().isValid()) {
            return;
        }

        Shopware.app.Application.fireEvent(
            'store-login',
            me.shopwareIdField.getValue(),
            me.passwordField.getValue(),
            function(response) {
                me.fireEvent('update-wizard-display-plugin-page');
            },
            function(response) {
                var message = response.message;

                Shopware.Notification.createStickyGrowlMessage({
                    title: 'Error',
                    text: message,
                    width: 350
                });
            }
        );
    },

    createShopwareIdField: function() {
        var me = this;

        me.shopwareIdField = Ext.create('Ext.form.field.Text', {
            name: 'shopwareId',
            allowBlank: false,
            cls: 'shopware-id',
            fieldLabel: '{s name="shopware_id"}{/s}',
            emptyText: '{s name="shopware_id"}{/s}',
            margin: '10 0',
            flex: 1,
            listeners: {
                specialkey: function(field, e){
                    if (e.getKey() == e.ENTER) {
                        me.applyLogin();
                    }
                }
            }
        });

        return me.shopwareIdField;
    },

    createPasswordField: function() {
        var me = this;

        me.passwordField = Ext.create('Ext.form.field.Text', {
            name: 'password',
            allowBlank: false,
            flex: 1,
            cls: 'password',
            fieldLabel: '{s name="password"}{/s}',
            emptyText: '{s name="password"}{/s}',
            inputType: 'password',
            listeners: {
                specialkey: function(field, e){
                    if (e.getKey() == e.ENTER) {
                        me.applyLogin();
                    }
                }
            }
        });

        return me.passwordField;
    }
});
