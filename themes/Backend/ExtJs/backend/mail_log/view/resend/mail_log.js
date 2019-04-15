// {namespace name="backend/mail_log/view/resend"}
// {block name="backend/mail_log/view/resend/mail_log"}
Ext.define('Shopware.apps.MailLog.view.resend.MailLog', {

    extend: 'Shopware.apps.MailLog.view.detail.MailLog',

    configure: function () {
        var me = this,
            config = me.callParent(arguments);

        config.fieldSets = me.createFieldSets({
            editableFields: true,
        });

        return config;
    },
});
// {/block}