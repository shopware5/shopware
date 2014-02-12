
Ext.define('Shopware.apps.Theme.view.list.extensions.Info', {
    extend: 'Shopware.listing.InfoPanel',
    alias: 'widget.theme-listing-info-panel',
    cls: 'theme-info-panel',
    width: 240,

    configure: function() {
        return {
            model: 'Shopware.apps.Theme.model.Theme',
            fields: {
                screen: '{literal}<div class="screen"><img src="{screen}" title="{name}" /></div>{/literal}',
                name: '<div class="info-item"> <p class="label">Name:</p> <p class="value">{literal}{name}{/literal}</p> </div>',
                author: '<div class="info-item"> <p class="label">Author:</p> <p class="value">{literal}{author}{/literal}</p> </div>',
                license: '<div class="info-item"> <p class="label">License:</p> <p class="value">{literal}{license}{/literal}</p> </div>',
                description: '<div class="info-item"> <p class="label">Description:</p> <p class="value">{literal}{description}{/literal}</p> </div>'
            }
        };
    },

    checkRequirements: function() { },
    addEventListeners: function() { }

});
