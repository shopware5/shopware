//{extends file="[default]backend/newsletter_manager/model/mailing.js"}
//{block name="backend/newsletter_manager/model/mailing"}
Ext.define('Shopware.apps.NewsletterManager.model.Mailing', {
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
        { name: 'id', type: 'int', useNull: true },
        { name: 'date', type: 'date', dateFormat: 'Y-m-d' },
        { name: 'locked', type: 'date' },
//        { name: 'groups', type: 'string' },
        { name: 'subject', type: 'string' },
        { name: 'status', type: 'int' },
        { name: 'addresses', type: 'int' },
        { name: 'customerGroup', type: 'string' },
        { name: 'senderName', type: 'string' },
        { name: 'senderMail', type: 'string' },
        { name: 'recipients', type: 'int' },
        { name: 'publish', type: 'int' },
        { name: 'read', type: 'string', defaultValue: 0 },
        { name: 'clicked', type: 'int', defaultValue: 0 },
        { name: 'revenue', type: 'float', defaultValue: 0 },
        { name: 'orders', type: 'int', defaultValue: 0 },
        { name: 'plaintext', type: 'boolean' },
        { name: 'languageId', type: 'int' },

        {   name:'conversionRate', type:'float',
            convert: function (newValue, record) {
                var clicks = record.get('clicked'),
                    orders = record.get('orders');

                if(clicks && orders) {
                    var rate = orders/clicks * 100;
                    rate = rate.toFixed(2);
                    return parseFloat(rate);
                }

                return 0;
          }
        },

        {   name:'readRate', type:'float',
            convert: function (newValue, record) {
                var recipients = record.get('recipients'),
                    read = record.get('read');

                if(recipients && read) {
                    var rate = read/recipients * 100;
                    rate = rate.toFixed(2);
                    return parseFloat(rate);
                }

                return 0;
          }
        },
        {   name:'clickRate', type:'float',
            convert: function (newValue, record) {
                var recipients = record.get('recipients'),
                    clicked = record.get('clicked');

                if(recipients && clicked) {
                    var rate = clicked/recipients * 100;
                    rate = rate.toFixed(2);
                    return parseFloat(rate);
                }

                return 0;
          }
        },
        {   name:'buyRate', type:'float',
            convert: function (newValue, record) {
                var recipients = record.get('recipients'),
                    orders = record.get('orders');

                if(recipients && orders) {
                    var rate = orders/recipients * 100;
                    rate = rate.toFixed(2);
                    return parseFloat(rate);
                }

                return 0;
          }
        }


    ],

    /**
     * Configure the data communication
     * @object
     */
    proxy: {
        type: 'ajax',

        /**
         * Configure the url mapping
         * @object
         */
        api: {
            create: '{url action="createNewsletter" controller="SwagNewsletter"}',
            update: '{url action="updateNewsletter" controller="SwagNewsletter"}',
            destroy:'{url action="deleteNewsletter" controller="NewsletterManager"}'
        },

        /**
         * Configure the data reader
         * @object
         */
        reader: {
            type: 'json',
            root: 'data'
        }
    },

    /**
     * Define the associations of the mailing model.
     * @array
     */
    associations:[
        // Addresses which have already received this mail
//        { type:'hasMany', model:'Shopware.apps.NewsletterManager.model.Recipient', name:'getAddresses', associationKey:'addresses' },
        // Container elements
        { type:'hasMany', model:'Shopware.apps.NewsletterManager.model.NewsletterElement', name:'getElements', associationKey:'elements' },
        // Groups which this newsletter addresses
        { type:'hasMany', model:'Shopware.apps.NewsletterManager.model.RecipientGroup', name:'getGroups', associationKey:'groups' },
        // Orders which made after reading this mail
        { type:'hasMany', model:'Shopware.apps.Base.model.Order', name:'getOrders', associationKey:'orders' }
    ]

});
//{/block}