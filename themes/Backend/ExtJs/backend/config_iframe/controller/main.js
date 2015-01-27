Ext.define('Shopware.apps.ConfigIframe.controller.Main', {
    extend: 'Ext.app.Controller',

    mainWindow: null,

    init: function() {
        var me = this;
        me.mainWindow = me.getView('main.Window').create().show();
    }
})