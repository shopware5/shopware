
Ext.define('Shopware.apps.PluginManager.view.loading.Mask', {
    extend: 'Ext.window.Window',

    modal: true,
    cls: 'plugin-manager-loading-mask',

    layout: {
        type: 'hbox',
        align: 'stretch'
    },
    bodyPadding: 20,
    header: false,
    width: 550,

    initComponent: function() {
        var me = this;

        me.items = [
            me.createIcon(),
            {
                xtype: 'container',
                flex: 1,
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                padding: '0 20',
                items: [
                    me.createHeadline(),
                    me.createDescription(),
                    me.createLoadingIndicator()
                ]
            }
        ];

        me.callParent(arguments);
    },

    createIcon: function() {
        var me = this, path = '';

        if (!me.plugin.get('iconPath')) {
            path = '{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/plugin_manager/default_icon.png"}';
        } else {
            path = me.plugin.get('iconPath');
        }

        return Ext.create('Ext.Component', {
            width: 128,
            height: 128,
            html: '<img src="'+ path +'" />'
        });
    },

    createHeadline: function() {
        var me = this;

        return Ext.create('Ext.Component', {
            cls: 'headline',
            html: me.plugin.get('label')
        });
    },

    createDescription: function() {
        var me = this;

        return Ext.create('Ext.Component', {
            flex: 1,
            html: me.description
        });
    },

    createLoadingIndicator: function() {
        var me = this;

        me.loadingIndicator = Ext.create('Ext.Component', {
            width: 60,
            height: 60,
            cls: 'plugin-manager-loading-indicator-wrapper',
            html: '<div class="plugin-manager-loading-indicator"></div>'
        });

        return me.loadingIndicator;
    }
});