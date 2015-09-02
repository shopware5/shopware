
//{namespace name=backend/plugin_manager/translation}
Ext.define('Shopware.apps.PluginManager.controller.Main', {
    extend:'Ext.app.Controller',
    mainWindow: null,

    refs: [
        { ref: 'navigation', selector: 'plugin-manager-listing-window plugin-category-navigation' },
        { ref: 'localListing', selector: 'plugin-manager-local-plugin-listing' },
        { ref: 'updatePage', selector: 'plugin-manager-update-page' }
    ],

    init: function() {
        var me = this;

        Ext.Ajax.request({
            url: '{url controller=PluginManager action=pingStore}',
            method: 'POST',
            success: function (operation, opts) {
                var response = Ext.decode(operation.responseText);

                Shopware.app.Application.sbpAvailable = response.success;

                if (me.subApplication.params && me.subApplication.params.hidden) {
                    return;
                }

                if (!Shopware.app.Application.sbpAvailable) {
                    Shopware.Notification.createGrowlMessage('', '{s name="sbp_not_available"}{/s}');
                }
                me.mainWindow = me.getView('list.Window').create();
                me.mainWindow.show();
            }
        });

        me.control({
            'plugin-manager-listing-window': {
                'plugin-manager-loaded': me.afterPluginManagerLoaded
            }
        });

        Shopware.app.Application.on({
            'load-update-listing': me.loadUpdateListing,
            scope: me
        });

        this.callParent(arguments);
    },

    loadUpdateListing: function(callback) {
        var me = this,
            navigation = me.getNavigation(),
            updatePage = me.getUpdatePage();

        updatePage.listing.resetListing();

        updatePage.updateStore.load({
            callback: function(records) {
                if (records) {
                    navigation.setUpdateCount(records.length);

                    Ext.create('Shopware.notification.ExpiredLicence').check();
                }

                if (Ext.isFunction(callback)) {
                    callback(records);
                }
            }
        });
    },

    afterPluginManagerLoaded: function() {
        var me = this,
            localListing = me.getLocalListing();

        if (!Shopware.app.Application.sbpAvailable) {
            var navController = me.subApplication.getController('Navigation');
            navController.displayLocalPluginPage();
        }

        Ext.Function.defer(function () {
            localListing.getStore().load({
                callback: function(records) {
                }
            });

            Shopware.app.Application.fireEvent('load-update-listing');

        }, 1000);
    }
});