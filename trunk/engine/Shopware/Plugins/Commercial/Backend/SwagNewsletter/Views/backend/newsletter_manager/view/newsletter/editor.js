//{extends file="[default]backend/newsletter_manager/view/newsletter/editor.js"}
//{block name="backend/newsletter_manager/view/newsletter/editor" append}
//{namespace name="backend/swag_newsletter/main"}
Ext.define('Shopware.apps.NewsletterManager.view.newsletter.Editor-SwagNewsletter', {

    /**
     * Defines an override applied to a class.
     * @string
     */
    override: 'Shopware.apps.NewsletterManager.view.newsletter.Editor',

    /**
     * List of classes that have to be loaded before instantiating this class.
     * @array
     */
    requires: [ 'Shopware.apps.NewsletterManager.view.newsletter.Editor' ],

    alias: 'widget.newsletter-old-editor',

    /**
     * Initializes the class override to provide additional functionality
     * like a new full page preview.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.callOverridden(arguments);
    },

    /**
     * Creates the actual newsletter component
     * @return
     */
    createPanel: function() {
        var me = this,
            designer = Ext.create('Shopware.apps.NewsletterManager.view.newsletter.Designer', {
                libraryStore: me.libraryStore,
                newsletterRecord: me.record
            });

        return designer;

    }


});
//{/block}