//{extends file="[default]backend/newsletter_manager/controller/overview.js"}
//{block name="backend/newsletter_manager/controller/overview" append}
//{namespace name="backend/swag_newsletter/main"}
Ext.define('Shopware.apps.NewsletterManager.controller.Overview-SwagNewsletter', {

    /**
     * Defines an override applied to a class.
     * @string
     */
    override: 'Shopware.apps.NewsletterManager.controller.Overview',

    /**
     * List of classes that have to be loaded before instantiating this class.
     * @array
     */
    requires: [ 'Shopware.apps.NewsletterManager.controller.Overview' ],

    /**
     * Initializes the class override to provide additional functionality
     * like a new full page preview.
     *
     * @public
     * @return void
     */
    init: function() {
        var me = this;

        var controller = me.subApplication.getController('Designer');
        me.subApplication.getController('Analytics');

        me.callOverridden(arguments);
    },

    /**
     * Called when the edit button in the action column was clicked
     * Will open the newsletter-editor window and load the existing newsletter
     */
    onEditNewsletter: function(record) {
        var me = this,
            newsletterWindow,
            settings = Ext.create('Shopware.apps.NewsletterManager.model.Settings');
        newsletterWindow = me.getView('newsletter.Window').create({
            senderStore: me.subApplication.senderStore,                     // available senders
            recipientGroupStore: me.subApplication.recipientGroupStore,     // available newsletter groups + available customer groups
            newsletterGroupStore: me.subApplication.newsletterGroupStore,   // available newsletter groups
            customerGroupStore: me.subApplication.customerGroupStore,        // available customer groups
            shopStore: me.subApplication.shopStore,
            libraryStore: me.subApplication.libraryStore,
            dispatchStore:  me.getStore('MailDispatch'),
            title: Ext.String.format("{s name=newsletterWindowEditTitle}Editing newsletter '{literal}{0}{/literal}{/s}'", record.get('subject')),
            record: record
        });

        //As the existing database table holds some strings where IDs would be needed, we have a additional
        //settings model, which translates between the Newsletter-Model ("Mailing") and the structure needed
        //to set the form up properly.
        settings.set('subject', record.get('subject'));
        settings.set('customerGroup', record.get('customerGroup'));
        settings.set('languageId', record.get('languageId'));
        if (record.get('plaintext') == true) {
            settings.set('dispatch', 2);
        } else {
            settings.set('dispatch', 1);
        }


        var editor = me.getNewsletterEditor(), form = me.getNewsletterSettings(),
            senderMail = record.get('senderMail'),
            groups, elements, content = "", senderRecord;

        elements = record.getElements();
//        if(containers instanceof Ext.data.Store && containers.first() instanceof Ext.data.Model) {
//            text = containers.first().getText();
//            if(text instanceof Ext.data.Store && text.first() instanceof Ext.data.Model) {
//                    content = text.first().get('content');
//            }
//
//        }
        settings.set('elements', elements);


        // sender is saved as plain text. need to get the id from senderStore
        senderRecord = me.subApplication.senderStore.findRecord('email', senderMail);
        if (!senderRecord instanceof Ext.data.Model) {
            settings.set('senderId', null);
        } else {
            settings.set('senderId', senderRecord.get('id'));
        }

        form.loadRecord(settings);


        // TinyMCE will be loaded last - it hast some getDoc() us undefined issues
        editor.loadRecord(settings);

    },

    /**
     * Called when the edit button in the action column was clicked
     * Will open the newsletter-editor window and load the existing newsletter
     */
    onCreateNewNewsletter: function (record) {
        var me = this,
            newsletterWindow,
            settings = Ext.create('Shopware.apps.NewsletterManager.model.Settings');

        newsletterWindow = me.getView('newsletter.Window').create({
            senderStore: me.subApplication.senderStore,                     // available senders
            recipientGroupStore: me.subApplication.recipientGroupStore,     // available newsletter groups + available customer groups
            newsletterGroupStore: me.subApplication.newsletterGroupStore,   // available newsletter groups
            customerGroupStore: me.subApplication.customerGroupStore,        // available customer groups
            shopStore: me.subApplication.shopStore,
            libraryStore: me.subApplication.libraryStore,
            dispatchStore:  me.getStore('MailDispatch')
        });

        var senderStore = me.subApplication.senderStore, r;
        if(senderStore instanceof Ext.data.Store) {
            r = me.subApplication.senderStore.first();
            if( r ) {
                settings.set('senderId', r.get('id'));
            }

        }

        var editor = me.getNewsletterEditor(), form = me.getNewsletterSettings();
        settings.set('customerGroup', me.subApplication.customerGroupStore.first().get('key'));
        form.loadRecord(settings);
    }

});
//{/block}