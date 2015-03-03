
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.view.account.Login', {
    extend: 'Ext.window.Window',
    modal: true,

    cls: 'plugin-manager-login-window',

    header: false,

    layout: {
        type: 'vbox',
        align: 'stretch'
    },

    bodyPadding: 40,
    minHeight: 440,
    minWidth: 500,

    initComponent: function() {
        var me = this;

        me.items = [
            me.createHeadline(),
            me.createForm(),
            me.createForgotLink(),
            me.createRegisterLink()
        ];

        me.dockedItems = [ me.createToolbar() ];

        me.callParent(arguments);
    },

    createHeadline: function() {
        var me = this;

        return Ext.create('Ext.Component', {
            html: '{s name="welcome"}{/s}',
            cls: 'headline'
        });
    },

    createForgotLink: function() {
        var me = this;

        return Ext.create('Ext.Component', {
            html: '<a href="https://account.shopware.com/#/forgotPassword" target="_blank">{s name="forgot_password"}{/s}</a>',
            cls: 'forgot'
        });
    },

    createRegisterLink: function() {
        var me = this;

        return Ext.create('Ext.Component', {
            html: '<a href="https://account.shopware.com/#/" target="_blank">{s name="register_now"}{/s}</a>',
            cls: 'forgot'
        });
    },

    createForm: function() {
        var me = this;

        me.formPanel = Ext.create('Ext.form.Panel', {
            border: false,
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
            html: '{s name="cancel"}{/s}',
            cls: 'plugin-manager-action-button',
            handler: function() {
                me.destroy();
            }
        });

        var applyButton = Ext.create('PluginManager.container.Container', {
            html: '{s name="login"}{/s}',
            cls: 'plugin-manager-action-button primary',
            handler: function() {
                me.applyLogin();
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            cls: 'toolbar',
            padding: '0 40 30',
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
                me.destroy();
                me.callback();
            }
        );
    },

    createShopwareIdField: function() {
        var me = this;

        me.shopwareIdField = Ext.create('Ext.form.field.Text', {
            name: 'shopwareId',
            allowBlank: false,
            cls: 'shopware-id',
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