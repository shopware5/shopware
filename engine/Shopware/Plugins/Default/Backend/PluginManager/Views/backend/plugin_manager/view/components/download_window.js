
Ext.define('Shopware.apps.PluginManager.view.components.DownloadWindow', {
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

    icon: '{link file="themes/Backend/ExtJs/backend/_resources/resources/themes/images/shopware-ui/plugin_manager/default_icon.png"}',
    headline: '',
    description: '',
    progressText: '[0] of [1] KB ([2]%)',

    callback: function() { },

    download: {
        fileName: 'download',
        uri: null,
        size: null,
        sha1: null
    },

    initComponent: function() {
        var me = this;

        me.items = me.createItems();

        me.callParent(arguments);
    },

    startDownload: function(offset) {
        var me = this;

        me.updateProgressBar(offset);

        Ext.Ajax.request({
            url: '{url controller=PluginManager action=rangeDownload}',
            method: 'POST',
            params: {
                offset: offset,
                fileName: me.download.fileName,
                uri: me.download.uri,
                size: me.download.size,
                sha1: me.download.sha1
            },
            success: function(operation, opts) {
                var response = Ext.decode(operation.responseText);

                if (response.finish == true) {
                    me.updateProgressBar(me.download.size);

                    Ext.Function.defer(function () {
                        me.callback(response.destination);
                        me.destroy();
                    }, 300);
                } else {
                    offset = response.offset;
                    me.startDownload(offset);
                }
            }
        });
    },

    createItems: function() {
        var me = this, items = [];

        if (me.headline) {
            items.push(me.createHeadline());
        }

        if (me.description) {
            items.push(me.createDescription());
        }

        items.push(me.createProgressBar());

        var container = Ext.create('Ext.container.Container', {
            xtype: 'container',
            flex: 1,
            items: items,
            layout: { type: 'vbox', align: 'stretch' },
            padding: '0 20'
        });

        if (me.icon) {
            return [me.createIcon(), container];
        } else {
            return [container];
        }
    },

    createProgressBar: function() {
        var me = this;
        var size = me.download.size / 1024;

        me.progress = Ext.create('Ext.ProgressBar', {
            animate: true,
            text: Ext.String.format(
                me.progressText,
                0,
                Math.round(size),
                0
            ),
            value: 0,
            height: 20,
            margin: '15 0 0'
        });

        return me.progress;
    },

    updateProgressBar: function(done) {
        var me = this;

        var index = done / 1024;
        var size = me.download.size / 1024;
        var percent = index / size;

        me.progress.updateProgress(
            percent,
            Ext.String.format(
                me.progressText,
                Math.round(index),
                Math.round(size),
                Math.round(percent * 100)
            ),
            true
        );
    },

    createIcon: function() {
        var me = this;

        return Ext.create('Ext.Component', {
            width: 128,
            height: 128,
            html: '<img src="'+ me.icon +'" />'
        });
    },

    createHeadline: function() {
        var me = this;

        return Ext.create('Ext.Component', {
            cls: 'headline',
            html: me.headline
        });
    },

    createDescription: function() {
        var me = this;

        return Ext.create('Ext.Component', {
            flex: 1,
            html: me.description
        });
    }
});