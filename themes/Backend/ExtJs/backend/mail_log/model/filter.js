// {namespace name="backend/mail_log/model"}
//{block name="backend/performance/model/filter"}
Ext.define('Shopware.apps.MailLog.model.Filter', {

    extend: 'Ext.data.Model',

    fields: [
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