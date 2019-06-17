// {namespace name="backend/mail_log/main"}
// {block name="backend/mail_log/controller/main"}
Ext.define('Shopware.apps.MailLog.controller.Main', {

    extend: 'Enlight.app.Controller',

    init: function() {
        this.mainWindow = this.getView('list.Window').create({}).show();

        this.control({
            'mail_log-listing-grid': {
                /*{if {acl_is_allowed resource=order privilege=read}}*/
                openOrder: this.onOpenOrder,
                /*{/if}*/
                /*{if {acl_is_allowed privilege=resend}}*/
                resendMailDialog: this.onResendMailDialog,
                /*{/if}*/
            },
            'mail_log-resend-window': {
                /*{if {acl_is_allowed privilege=resend}}*/
                resendMail: this.onResendMail,
                /*{/if}*/
            }
        });
    },

    onOpenOrder: function(record) {
        if (!record.get('order')) {
            return;
        }

        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Order',
            params: {
                orderId: record.get('order').id
            }
        });
    },

    onResendMailDialog: function(record) {
        var me = this;

        me.getView('resend.Window').create({
            record: record.copy(),
        });
    },

    onResendMail: function (record, window) {
        var me = this;

        if (!record.isValid()) {
            Shopware.Notification.createGrowlMessage(
                '{s name=resend_mail_invalid_title}{/s}',
                '{s name=resend_mail_invalid_text}{/s}'
            );

            return;
        }

        window.resendButton.disable();

        Ext.Ajax.request({
            url: '{url controller=MailLog action=resendMail}',
            method: 'POST',
            jsonData: {
                id: record.get('id'),
                recipients: record.get('recipients')
            },
            success: function (response, opts) {
                Shopware.Notification.createGrowlMessage(
                    '{s name=resend_mail_success_title}{/s}',
                    '{s name=resend_mail_success_text}{/s}'
                );

                window.destroy();
                me.mainWindow.gridPanel.getStore().load();
            },
            failure: function (response, opts) {
                window.resendButton.enable();

                Shopware.Notification.createGrowlMessage(
                    '{s name=resend_mail_error_title}{/s}',
                    '{s name=resend_mail_error_text}{/s}'
                );
            }
        });
    }

});
// {/block}
