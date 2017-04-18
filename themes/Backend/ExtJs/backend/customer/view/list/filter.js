Ext.define('Shopware.apps.Customer.view.list.Filter', {
    extend: 'Shopware.listing.FilterPanel',
    alias: 'widget.customer-filter-panel',
    title: 'Filterung',

    configure: function() {
        return {
            controller: 'Customer',
            model: 'Shopware.apps.Customer.model.List'
        };
    }
});
