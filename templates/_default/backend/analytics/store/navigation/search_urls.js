/**
 * todo@all: Documentation
 */
Ext.define('Shopware.apps.Analytics.store.navigation.SearchUrls', {
    extend: 'Ext.data.Store',
    alias: 'widget.analytics-store-navigation-search-urls',
    remoteSort: true,
    fields: [
        'count',
        'referrer'
    ],
    proxy: {
        type: 'ajax',
        url: '{url controller=analytics action=getSearchUrls}',
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
