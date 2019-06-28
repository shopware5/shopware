// {namespace name="backend/mail_log/model"}
// {block name="backend/mail_log/model/mail_log_contact"}
Ext.define('Shopware.apps.MailLog.model.MailLogContact', {

    extend: 'Shopware.data.Model',

    fields: [
        {
            name: 'id',
            type: 'int',
            useNull: true
        },
        {
            name: 'mailAddress',
            type: 'string'
        }
    ]

});
// {/block}