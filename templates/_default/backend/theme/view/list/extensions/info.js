
Ext.define('Shopware.apps.Theme.view.list.extensions.Info', {
    extend: 'Shopware.listing.InfoPanel',
    alias: 'widget.theme-listing-info-panel',

    configure: function() {
        return {
            model: 'Shopware.apps.Theme.model.Theme',
            fields: {
                screen: '{literal}{screen}{/literal}',
                name: null,
                author: null,
                esi: null,
                style: null,
                emotion: null
            }
        };
    },

    checkRequirements: function() { },
    addEventListeners: function() { }

});
