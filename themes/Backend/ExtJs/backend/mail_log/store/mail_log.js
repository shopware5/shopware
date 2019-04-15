// {namespace name="backend/mail_log/store"}
// {block name="backend/mail_log/store/mail_log"}
Ext.define('Shopware.apps.MailLog.store.MailLog', {

    extend: 'Shopware.store.Listing',
    model: 'Shopware.apps.MailLog.model.MailLog',
    sorters: [
        {
            property: 'sentAt',
            direction: 'DESC'
        },
    ],

    configure: function () {
        return {
            controller: 'MailLog',
        };
    }

});
// {/block}