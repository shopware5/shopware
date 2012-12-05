//{extends file="[default]backend/newsletter_manager/view/main/window.js"}
//{block name="backend/newsletter_manager/view/main/window" append}
//{namespace name="backend/swag_newsletter/main"}
Ext.define('Shopware.apps.NewsletterManager.view.main.Window-SwagNewsletter', {

    /**
     * Defines an override applied to a class.
     * @string
     */
    override: 'Shopware.apps.NewsletterManager.view.main.Window',

    /**
     * List of classes that have to be loaded before instantiating this class.
     * @array
     */
    requires: [ 'Shopware.apps.NewsletterManager.view.main.Window' ],

    width: 900,
    height: 600,

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
     * Inserts analysis/statistics tab into the tab row
     * @return Array
     */
    getTabs: function(){
        var me = this,
            tabs = me.callOverridden(arguments);

        tabs.splice(1, 0, {
            xtype: 'newsletter-manager-tabs-analytics',
            store: me.mailingStore,
            orderStore: Ext.create('Shopware.apps.NewsletterManager.store.Order').load(),
            orderStatusStore: Ext.create('Shopware.apps.Base.store.OrderStatus').load(),
            paymentStatusStore: Ext.create('Shopware.apps.Base.store.PaymentStatus').load()
        });

        return tabs;

    }


});
//{/block}