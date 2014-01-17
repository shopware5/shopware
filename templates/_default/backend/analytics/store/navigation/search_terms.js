/**
 * todo@all: Documentation
 */
Ext.define('Shopware.apps.Analytics.store.navigation.SearchTerms', {
    extend: 'Ext.data.Store',
    alias: 'widget.analytics-store-navigation-search-terms',
    remoteSort: true,
    fields: [
        'count',
        'searchterm'
    ],
    proxy: {
        type: 'ajax',
        url: '{url controller=analytics action=getReferrerSearchTerms}',
        reader: {
            type: 'json',
            root: 'data',
            totalProperty: 'total'
        }
    }
});
