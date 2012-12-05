//{extends file="[default]backend/newsletter_manager/controller/main.js"}
//{block name="backend/newsletter_manager/controller/main" append}
//{namespace name="backend/swag_newsletter/main"}
Ext.define('Shopware.apps.NewsletterManager.controller.Main-SwagNewsletter', {

    /**
     * Defines an override applied to a class.
     * @string
     */
    override: 'Shopware.apps.NewsletterManager.controller.Main',

    /**
     * List of classes that have to be loaded before instantiating this class.
     * @array
     */
    requires: [ 'Shopware.apps.NewsletterManager.controller.Main' ],

    /**
     * Initializes the class override to provide additional functionality
     * like a new full page preview.
     *
     * @public
     * @return void
     */
    init: function() {
        var me = this;

        me.subApplication.libraryStore = me.getStore('Shopware.apps.NewsletterManager.store.Library').load();
        me.callOverridden(arguments);
    }

});
//{/block}