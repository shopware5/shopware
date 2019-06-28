// {namespace name="backend/mail_log/store"}
// {block name="backend/mail_log/store/mail_log_contact"}
Ext.define('Shopware.apps.MailLog.store.MailLogContact', {

    extend: 'Shopware.store.Listing',
    model: 'Shopware.apps.MailLog.model.MailLogContact',

    configure: function () {
        return {
            controller: 'MailLogContact'
        };
    }

});
// {/block}