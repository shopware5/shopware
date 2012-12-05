//{extends file="[default]backend/newsletter_manager/model/settings.js"}
//{block name="backend/newsletter_manager/model/settings"}
Ext.define('Shopware.apps.NewsletterManager.model.Settings', {
    /**
     * Extends the standard Ext Model
     * @string
     */
    extend:'Ext.data.Model',

    requires:[
           'Shopware.apps.NewsletterManager.model.NewsletterElement'
       ],

    /**
     * The fields used for this model
     * @array
     */
    fields: [
        { name: 'id', type: 'int' },
        { name: 'subject', type: 'string' },
        { name: 'senderId', type: 'int', defaultValue: 1 },
        { name: 'customerGroup', type: 'string', defaultValue: '' },
        { name: 'languageId', type: 'int',  defaultValue: 1 },
        { name: 'recipients', type: 'int', defaultValue: 0 },
        { name: 'dispatch', type: 'int', defaultValue: 1 },

        { name: 'senderName', type: 'string' },
        { name: 'senderMail', type: 'string' },
        { name: 'subject', type: 'string' },
        { name: 'plaintext', type: 'boolean' }
    ],

    /**
     * Define the associations of the mailing model.
     * @array
     */
    associations:[
        { type:'hasMany', model:'Shopware.apps.NewsletterManager.model.NewsletterElement', name:'getElements', associationKey:'elements' }
    ]

});
//{/block}