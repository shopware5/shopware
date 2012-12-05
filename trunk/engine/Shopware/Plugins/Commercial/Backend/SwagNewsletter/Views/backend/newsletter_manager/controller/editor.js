//{extends file="[default]backend/newsletter_manager/controller/editor.js"}
//{block name="backend/newsletter_manager/controller/editor" append}
//{namespace name="backend/swag_newsletter/main"}
Ext.define('Shopware.apps.NewsletterManager.controller.Editor-SwagNewsletter', {

    /**
     * Defines an override applied to a class.
     * @string
     */
    override: 'Shopware.apps.NewsletterManager.controller.Editor',

    /**
     * List of classes that have to be loaded before instantiating this class.
     * @array
     */
    requires: [ 'Shopware.apps.NewsletterManager.controller.Editor' ],

    refs: [
        { ref:'newsletterDesigner', selector:'newsletter-designer' },
        { ref:'newsletterEditor', selector:'newsletter-manager-newsletter-editor' },
        { ref: 'previewButton', selector: 'newsletter-manager-newsletter-editor button[name=preview]' },
        { ref: 'sendMailButton', selector: 'newsletter-manager-newsletter-editor button[name=sendMail]' },
        { ref: 'mailAddressField', selector: 'newsletter-manager-newsletter-editor textfield[name=mailAddress]' },
        { ref:'newsletterSettings', selector:'newsletter-manager-newsletter-settings' },
        { ref:'newsletterWindow', selector:'newsletter-manager-newsletter-window' }
    ],

    /**
     * Initializes the class override to provide additional functionality
     * like a new full page preview.
     *
     * @public
     * @return void
     */
    init: function() {
        var me = this;

        me.callOverridden(arguments);

        me.control({
            'newsletter-designer': {
                'formChanged': me.onFormChanged
            }
        });
    },

    /**
     * Override the onFormChanged method in order to also check for new newsletter elements
     * @param form|null
     */
    onFormChanged: function(form) {
        var me = this,
            form = me.getNewsletterSettings().getForm(),
            previewButton = me.getPreviewButton(),
            sendMailButton = me.getSendMailButton(),
            mailAddressField = me.getMailAddressField(),
            window = me.getNewsletterWindow(),
            designer = me.getNewsletterDesigner(),
            entry = designer.dataviewStore.getAt(0), elements = entry.get('elements');


        // Disable the saveButton by default
        window.toolbar.saveButton.disable();
        // Allow the preview button to be enabled by default:
        // it just needs to be disabled, when elements in the designer are marked as "new"
        previewButton.enable();

        if(mailAddressField.isValid() && mailAddressField.getValue() != "") {
            sendMailButton.enable();
        }

        if(form.isValid()){
            window.toolbar.saveButton.enable();

            if(mailAddressField.isValid() && mailAddressField.getValue() != "") {
                sendMailButton.enable();
            }else{
                sendMailButton.disable();
            }
            previewButton.enable();
        }

        // Check if a element is marked as new
        Ext.each(elements, function(item) {
            if(item.get('isNew') === true) {
                window.toolbar.saveButton.disable();
                previewButton.disable();
                sendMailButton.disable();
            }
        });

        designer.dataView.refresh();
    },



    /**
     * Override for the createNewsletterModel function
     * @param record
     */
    createNewsletterModel: function(record) {
        var me = this,
            newsletter, container, ctText, config,
            textStore, containerStore,
            settings = me.getSettings();

//        Ext.each(settings.get('content')

        var elementStore = Ext.create('Ext.data.Store', { model: 'Shopware.apps.NewsletterManager.model.NewsletterElement' });
        Ext.each(settings.get('elements'), function(element) {
            elementStore.add(element);
        });

        config =  {
            subject: settings.get('subject'),
            customerGroup: settings.get('customerGroup'),
            recipients: settings.get('recipients'),
            plaintext: settings.get('plaintext'),
            senderName: settings.get('senderName'),
            senderMail: settings.get('senderMail'),
            groups: settings.get('groups'),
            languageId: settings.get('languageId'),

            date :  null,
            locked: null

        };

        // Create model for the newsletter
        if(record == null) {
            newsletter = Ext.create('Shopware.apps.NewsletterManager.model.Mailing', config);
        }else{
            record.set(config);
            newsletter = record;
        }

        if(settings.get('id') !== null){
            newsletter.set('id', settings.get('id'));
        }

        newsletter.getElementsStore = elementStore;
        newsletter.getGroupsStore = settings.get('groups');

        return newsletter;
    },

    /**
     * Replaces the getSettings methode of the basic newsletter module
     *
     */
    getSettings: function() {
        var me = this,
            record,
            count, totalCount = 0,
            designer = me.getNewsletterDesigner(),
            settingsForm = me.getNewsletterSettings(),
            newsletterGroups = settingsForm.newsletterGroups, customerGroups = settingsForm.customerGroups,
            groups = Ext.create('Shopware.apps.NewsletterManager.store.RecipientGroup'),
            data = designer.dataviewStore.first(),
            elements = data.get('elements');

        // Iterate the checkboxes and populate the RecipientGroupStore with checked customer groups
        Ext.each(customerGroups, function(checkbox) {
            var record = checkbox.record,
                count = checkbox.count,
                value = checkbox.getValue();

            //todo@dn: set count to the number of users in the given group
            if(value === true) {
                totalCount += count;

                record = Ext.create('Shopware.apps.NewsletterManager.model.RecipientGroup', {
                    internalId: record.get('id'),
                    number: count,
                    name: record.get('name'),
                    groupkey: record.get('key'),
                    isCustomerGroup: true
                });
                groups.add(record);
            }
        });

        // Iterate the checkboxes and populate the RecipientGroupStore with checked newsletter groups
        Ext.each(newsletterGroups, function(checkbox) {
            var record = checkbox.record,
                    count = checkbox.count,
                value = checkbox.getValue();

            //todo@dn: set count to the number of users in the given group
            if(value === true) {
                totalCount += count;

                record = Ext.create('Shopware.apps.NewsletterManager.model.RecipientGroup', {
                    internalId: record.get('id'),
                    number: count,
                    name: record.get('name'),
                    groupkey: false,
                    isCustomerGroup: false
                });
                groups.add(record);
            }

        });

        var settings = Ext.create('Shopware.apps.NewsletterManager.model.Settings'),
            values = settingsForm.getValues();

        // Copy the values from the form into the settings model
        settings.set(values);

        if(settingsForm.record.get('id') !== null){
            settings.set('id', settingsForm.record.get('id'));
        }
        settings.set('elements', elements);
        settings.set('groups', groups);
        settings.set('recipients', totalCount);
        if(values['dispatch'] == 1){
            settings.set('plaintext', false);
        }else{
            settings.set('plaintext', true);
        }
        var senderRecord = me.subApplication.senderStore.getById(values['senderId']);
        settings.set('senderName', senderRecord.get('name'));
        settings.set('senderMail', senderRecord.get('email'));

        return settings;
    }

});
//{/block}