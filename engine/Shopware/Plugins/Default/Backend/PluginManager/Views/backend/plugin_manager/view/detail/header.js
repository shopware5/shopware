
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.view.detail.Header', {
    extend: 'Ext.container.Container',
    cls: 'store-plugin-detail-headline-container-content',
    layout: {
        type: 'hbox',
        align: 'stretch'
    },

    initComponent: function() {
        var me = this, certified = null, producer = null;

        if (!me.plugin) {
            return;
        }

        if (me.plugin.get('certified')) {
            certified = Ext.create('Ext.Component', {
                cls: 'headline-certified',
                html: '<span class="icon">&nbsp;</span><span class="text">{s name="certified"}Certified{/s}</span>'
            });
        }

        if (me.plugin['getProducerStore'] instanceof Ext.data.Store) {
            var record = me.plugin['getProducerStore'].first();

            producer = Ext.create('Ext.Component', {
                cls: 'headline-author',
                margin: '5 0 0',
                html: '<span class="prefix">{s name="from_producer"}Developed by{/s}:</span> ' + record.get('name')
            });
        }

        var image = null;

        if (me.plugin.get('iconPath')) {
            image = {
                xtype: 'component',
                cls: 'headline-image',
                width: 128,
                height: 128,
                html: '<img src="'+ me.plugin.get('iconPath') +'" />'
            };
        }

        me.items = [image, {
            xtype: 'container',
            cls: 'headline-right-container',
            flex: 1,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [
                {
                    xtype: 'component',
                    html: '<div>'+ me.plugin.get('label') + '</div>',
                    cls: 'headline-name',
                    width: 750
                },
                certified,
                producer
            ]
        }];

        me.callParent(arguments);
    }
});