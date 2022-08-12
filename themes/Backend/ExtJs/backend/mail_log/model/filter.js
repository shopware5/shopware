// {namespace name="backend/mail_log/model"}
//{block name="backend/mail_log/model/filter"}
Ext.define('Shopware.apps.MailLog.model.Filter', {

    extend: 'Ext.data.Model',

    fields: [
        //{block name="backend/mail_log/model/filter/fields"}
        { name: 'label', type: 'string' },
        { name: 'name', type: 'string' },
    ],

    proxy: {
        type: 'ajax',
        api: {
            read: '{url action="getFilters"}',
        },
        reader: {
            type: 'json',
            root: 'data',
        },
    },

});
//{/block}
