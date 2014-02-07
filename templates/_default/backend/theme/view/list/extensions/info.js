
Ext.define('Shopware.apps.Theme.view.list.extensions.Info', {
    extend: 'Shopware.listing.InfoPanel',
    alias: 'widget.theme-listing-info-panel',
    cls: 'theme-info-panel',

    configure: function() {
        return {
            model: 'Shopware.apps.Theme.model.Theme',
            fields: {
                screen: '{literal}<div class="screen"><img src="{screen}" title="{name}" /></div>{/literal}',
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
