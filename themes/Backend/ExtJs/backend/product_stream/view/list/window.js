
Ext.define('Shopware.apps.ProductStream.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.product-stream-list-window',
    height: 450,
    title : '{s name=window_title}Product Stream listing{/s}',

    configure: function() {
        return {
            listingGrid: 'Shopware.apps.ProductStream.view.list.List',
            listingStore: 'Shopware.apps.ProductStream.store.Stream'
        };
    }
});