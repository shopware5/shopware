// {namespace name="backend/mail_log/model"}
// {block name="backend/mail_log/model/mail_log"}
Ext.define('Shopware.apps.MailLog.model.MailLog', {

    extend: 'Shopware.data.Model',

    configure: function () {
        return {
            controller: 'MailLog',
            detail: 'Shopware.apps.MailLog.view.detail.MailLog'
        };
    },

    fields: [
        {
            name: 'id',
            type: 'int',
            useNull: true
        },
        {
            name: 'subject',
            type: 'string',
        },
        {
            name: 'sender',
            type: 'string',
        },
        {
            name: 'recipients',
            type: 'Shopware.apps.MailLog.model.MailLogContact'
        },
        {
            name: 'sentAt',
            type: 'datetime'
        },
        {
            name: 'contentHtml',
            type: 'string'
        },
        {
            name: 'contentText',
            type: 'string'
        },
        {
            name: 'order',
            type: 'Shopware.apps.Order.model.Order'
        },
    ],

    validations: [
        {
            field: 'recipients',
            type: 'length',
            min: 1,
        },
    ]

});
// {/block}