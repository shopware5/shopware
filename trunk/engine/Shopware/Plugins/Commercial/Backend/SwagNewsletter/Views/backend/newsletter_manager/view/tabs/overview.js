//{extends file="[default]backend/newsletter_manager/view/tabs/overview.js"}
//{block name="backend/newsletter_manager/view/tabs/overview" append}
//{namespace name="backend/swag_newsletter/main"}
Ext.define('Shopware.apps.NewsletterManager.view.tabs.Overview-SwagNewsletter', {

    /**
     * Defines an override applied to a class.
     * @string
     */
    override: 'Shopware.apps.NewsletterManager.view.tabs.Overview',

    /**
     * List of classes that have to be loaded before instantiating this class.
     * @array
     */
    requires: [ 'Shopware.apps.NewsletterManager.view.tabs.Overview' ],

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

    getToolbar: function() {
        var me = this,
            container = me.callOverridden(arguments);

//        var button = container.items.get(0);
//        button.setText("asd");

        return container;

    }
});
//{/block}