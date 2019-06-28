// {namespace name="backend/mail_log/view/detail"}
// {block name="backend/mail_log/view/detail/mail_log_contact"}
Ext.define('Shopware.apps.MailLog.view.detail.MailLogContact', {

    extend: 'Shopware.grid.Association',
    alias: 'widget.mail_log-view-detail-mail-log-contact',

    configure: function () {
        return {
            controller: 'MailLogContact',
            columns: {
                mailAddress: {}
            }
        };
    },

});
// {/block}