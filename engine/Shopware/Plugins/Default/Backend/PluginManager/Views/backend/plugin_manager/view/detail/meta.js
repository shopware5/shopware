
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.view.detail.Meta', {
    extend: 'Ext.container.Container',

    cls: 'store-plugin-detail-meta-data',
    defaults: {
        xtype: 'component',
        cls: 'item',
        height: 40
    },

    mixins: {
        events: 'Shopware.apps.PluginManager.view.PluginHelper'
    },

    initComponent: function() {
        var me = this, items = [], commentCount = 0;

        if (me.plugin['getCommentsStore']) {
            commentCount = me.plugin['getCommentsStore'].getCount();
        }

        items.push({
            html: '<div class="label">{s name="version"}Version{/s}:</div>' +
            '<div class="value">'+ me.plugin.get('version') +'</div>'
        });

        items.push({
            html: '<div class="label">{s name="rating_short"}Rating{/s}:</div>' +
                '<div class="value">'+
                    '<div class="store-plugin-rating star' + me.plugin.get('rating') + '">('+commentCount+')</div>' +
                '</div>'
        });

        if (me.plugin['getLicenceStore']) {

            try {
                var licence = me.plugin['getLicenceStore'].first();

                var price = licence['getPriceStore'].first();

                var type = me.getTextForPriceType(price.get('type'));

                var expiration = licence.get('expirationDate');

                var result = type;

                if (expiration) {
                    var date = me.formatDate(expiration.date);
                    result += '<span class="date"> ({s name="till"}until{/s}: '+ Ext.util.Format.date(date) + ')</span>';
                }

                items.push({
                    html: '<div class="label">{s name="licence"}License{/s}:</div><div class="value">'+result+'</div>'
                });
            } catch (e) {

            }
        }

        me.items = items;

        me.callParent(arguments);
    }
});