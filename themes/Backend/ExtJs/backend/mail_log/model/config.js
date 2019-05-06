// {namespace name="backend/mail_log/model"}
// {block name="backend/mail_log/model/config"}
Ext.define('Shopware.apps.MailLog.model.Config', {

    extend: 'Ext.data.Model',

    fields: [
        {
            name: 'mailLogActive',
            type: 'boolean',
        },
        {
            name: 'mailLogCleanupMaximumAgeInDays',
            type: 'int',
        },
        {
            name: 'mailLogActiveFilters',
            type: 'any'
        }
    ],

    proxy: {
        type: 'ajax',
        api: {
            read: '{url action="getConfig"}',
            update: '{url action="saveConfig"}',
            create: '{url action="saveConfig"}',
        },
        reader: {
            type: 'json',
            root: 'data',
        },
    },

});
// {/block}