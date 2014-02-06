
Ext.define('Shopware.apps.Template.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.template-list-window',
    height: 450,
    title : '{s name=window_title}Template listing{/s}',

    configure: function() {
        return {
            listingGrid: 'Shopware.apps.Template.view.list.Template',
            listingStore: 'Shopware.apps.Template.store.Template'
        };
    }
});