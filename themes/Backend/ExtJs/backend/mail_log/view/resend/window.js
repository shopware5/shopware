// {namespace name="backend/mail_log/view/resend"}
// {block name="backend/mail_log/view/resend/window"}
Ext.define('Shopware.apps.MailLog.view.resend.Window', {

    extend: 'Shopware.apps.MailLog.view.detail.Window',
    alias: 'widget.mail_log-resend-window',
    title: '{s name="window_title"}{/s}',
    autoShow: true,

    record: null,

    registerEvents: function () {
        this.addEvents('resendMail');
    },

    createToolbarItems: function () {
        var me = this,
            items = me.callParent(arguments);

        this.resendButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            name: 'resend-button',
            text: '{s name="window_button_resend"}{/s}',
            handler: function () {
                me.fireEvent('resendMail', me.record, me);
            },
        });

        items.push(this.resendButton);

        return items;
    },

    createAssociationComponent: function(type, model, store, association, baseRecord) {
        return Ext.create('Shopware.apps.MailLog.view.resend.MailLog', {
            record: model,
            store: store,
            flex: 1,
            subApp: this.subApp,
            association: association,
            configure: function() {
                var config = { };

                if (association) {
                    config.associationKey = association.associationKey;
                }

                if (baseRecord && baseRecord.getConfig('controller')) {
                    config.controller = baseRecord.getConfig('controller');
                }

                return config;
            }
        });
    }

});
// {/block}
