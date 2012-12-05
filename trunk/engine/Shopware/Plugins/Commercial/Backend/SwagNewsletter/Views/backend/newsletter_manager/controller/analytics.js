//{block name="backend/newsletter_manager/controller/analytics"}
//{namespace name="backend/swag_newsletter/main"}
Ext.define('Shopware.apps.NewsletterManager.controller.Analytics', {

    extend: 'Ext.app.Controller',

    refs:[
        { ref:'orderTab', selector:'newsletter-manager-tabs-orders' }
    ],

    /**
     * A template method that is called when your application boots. It is called before the Application's
     * launch function is executed so gives a hook point to run any code before your Viewport is created.
     */
    init: function() {
        var me = this;

        me.control({
            'newsletter-manager-tabs-orders': {
                'searchOrders': me.onSearchOrders,
                'showCustomer': me.onShowCustomer,
                'showOrder': me.onShowOrder
            }
        });



        me.callParent(arguments);
    },

    /**
     * Called when the user clicked the 'openOrder' action button
     *
     * @param record
     * @return
     */
    onShowOrder: function(record) {
        Shopware.app.Application.addSubApplication({
            name: 'Shopware.apps.Order',
            params: {
                orderId:record.get('id')
            }
        });
    },

    /**
     * Called when the cuser clicked the 'showCustomer' action button
     * @param record
     */
    onShowCustomer: function(record) {
        var me = this,
            customerId = record.get('customerId');

        if(customerId) {
            Shopware.app.Application.addSubApplication({
                name: 'Shopware.apps.Customer',
                action: 'detail',
                params: {
                    customerId: customerId
                }
            });
        }
    },

    /**
     * Called when the user types into the order search field
     * @param field
     * @param store
     */
    onSearchOrders: function(field, store) {
        if(!field) {
            return;
        }

        var me = this,
            orderTab = me.getOrderTab(),
            store = orderTab.store,
            searchString = Ext.String.trim(field.getValue());

            //scroll the store to first page
            store.currentPage = 1;

            //If the search-value is empty, reset the filter
            if ( searchString.length === 0 ) {
                store.clearFilter();
            } else {
                //This won't reload the store
                store.filters.clear();
                //Loads the store with a special filter
                store.filter('filter', searchString);
            }
    }


});
//{/block}