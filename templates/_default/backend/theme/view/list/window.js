
Ext.define('Shopware.apps.Theme.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.theme-list-window',
    height: 450,
    title : '{s name=window_title}Theme manager{/s}',
    minWidth: 600,

    configure: function() {
        return {
            listingGrid: 'Shopware.apps.Theme.view.list.Theme',
            listingStore: 'Shopware.apps.Theme.store.Theme',

            extensions: [
                { xtype: 'theme-listing-info-panel' }
            ]
        };
    }
});