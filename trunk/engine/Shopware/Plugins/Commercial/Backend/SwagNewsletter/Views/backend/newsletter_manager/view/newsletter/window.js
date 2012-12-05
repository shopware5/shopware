//{extends file="[default]backend/newsletter_manager/view/newsletter/window.js"}
//{block name="backend/newsletter_manager/view/newsletter/window" append}
//{namespace name="backend/swag_newsletter/main"}
Ext.define('Shopware.apps.NewsletterManager.view.newsletter.Window-SwagNewsletter', {

    /**
     * Defines an override applied to a class.
     * @string
     */
    override: 'Shopware.apps.NewsletterManager.view.newsletter.Window',

    /**
     * List of classes that have to be loaded before instantiating this class.
     * @array
     */
    requires: [ 'Shopware.apps.NewsletterManager.view.newsletter.Window' ],

    /**
     * Initializes the class override to provide additional functionality
     * like a new full page preview.
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.hudPlugin = me.setupHudPlugin();

        me.plugins = [ me.hudPlugin ];

        me.callOverridden(arguments);
    },


    /**
     * Filter the library store for shopware components
     * @return Array
     */
    getShopwareComponents: function() {
        var me = this, components = [];

        me.libraryStore.clearFilter();
        me.libraryStore.filter({
            filterFn: function(item) {
                return item.get("pluginId") === null;
            }
        });
        return me.libraryStore.data.items;
    },

    /**
     * Filter the library store for 3rd party components
     * @return Array
     */
    getPluginComponents: function() {
        var me = this, components = [];

        me.libraryStore.clearFilter();
        me.libraryStore.filter({
            filterFn: function(item) {
                return item.get("pluginId") > 0;
            }
        });
        return me.libraryStore.data.items;
    },

    /**
     * Creates the store for the HUD elements
     * @return Ext.data.Store
     */
    getHudStore: function() {
        var me = this,
            store;

        // Create the data store
        return store = Ext.create('Ext.data.Store', {
            fields: [
                'headline', 'children'
            ],
            data: [{
                headline: '{s name=window/default_elements}Default elements{/s}',
                children: me.getShopwareComponents()
            }, {
                headline: '{s name=window/third_party_elements}Third party elements{/s}',
                children: me.getPluginComponents()
            }]
        });
    },



    /**
     * Creates, configures and returns the hub plugin
     * @return Shopware.window.plugin.Hud
     */
    setupHudPlugin: function() {
        var me = this,
            hud;
        
        hud = Ext.create('Shopware.window.plugin.Hud', {
            hudStore: me.getHudStore(),
            originalStore: me.libraryStore,
            hudOffset: 0,
            hudHeight: 550,
            itemSelector: '.x-library-element',
            tpl: me.createElementLibraryTemplate()
        });

        return hud;
    },

    /**
     * Creates the template für the hud element library
     * @return Ext.XTemplate
     */
    createElementLibraryTemplate: function() {
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="x-library-outer-panel">',
                    '<h2 class="x-library-section-title">',
                        '<div class="x-library-section-inner-title">{headline}:</div>',
                        '<div class="toggle"></div>',
                    '</h2>',
                    '<div class="x-library-inner-panel">',
                        '<ul>',
                            '<tpl for="children">',
                                '<li class="x-library-element" data-componentId="{data.id}">',
                                    '{data.name}',
                                '</li>',
                            '</tpl>',
                        '</ul>',
                    '</div>',
                '</div>',
            '</tpl>{/literal}'
        );
    },

    /**
    * Creates the main tab
    * internal titles needed in the main controller to tell apart the different tabs
    * @return Array
    */
    getTabs: function(){
        var me = this,
            tabs = me.callOverridden(arguments);
        me.libraryStore.clearFilter();

        tabs[0].libraryStore = me.libraryStore;
        return tabs;

//        return [{
//            xtype:'newsletter-manager-newsletter-editor',
//            record: me.record,
//            libraryStore: me.libraryStore
//        },
//        {
//            xtype:'newsletter-manager-newsletter-settings',
//            senderStore: me.senderStore,
//            recipientGroupStore: me.recipientGroupStore,
//            newsletterGroupStore: me.newsletterGroupStore,
//            customerGroupStore: me.customerGroupStore,
//            shopStore: me.shopStore,
//            dispatchStore: me.dispatchStore,
//            record: me.record
//        }];
    }
});
//{/block}