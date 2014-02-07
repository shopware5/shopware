
Ext.define('Shopware.apps.Theme.controller.Main', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'listing', selector: 'theme-listing' },
        { ref: 'listingView', selector: 'theme-listing dataview' },
        { ref: 'listingWindow', selector: 'theme-list-window' },
        { ref: 'shopCombo', selector: 'theme-list-window combobox[name=shop]' },
        { ref: 'infoPanel', selector: 'theme-listing-info-panel' }
    ],

    init: function() {
        var me = this;

        me.control({
            'theme-listing dataview': {
                selectionchange: me.onSelectTheme
            },
            'theme-list-window': {
                'assign-theme': me.onAssignTheme,
                'preview-theme': me.onPreviewTheme
            }

        });

        me.mainWindow = me.getView('list.Window').create({ }).show();
    },

    onAssignTheme: function() {
        var me = this, shop, theme;

        shop = me.getSelectedShop();
        theme = me.getSelectedTheme();

        Ext.Ajax.request({
            url: '{url controller="theme" action="assign"}',
            method: 'POST',
            params: {
                shopId: shop.get('id'),
                themeId: theme.get('id')
            },
            success: function(response, opts) {
                me.getListingView().getStore().load();
            }
        });
    },

    getSelectedTheme: function() {
        var me = this;

        if (!(me.getListingView())) {
            return null;
        }

        var selModel = me.getListingView().getSelectionModel();

        if (selModel.getSelection().length > 0) {
            return selModel.getSelection().shift();
        } else {
            return null;
        }
    },

    getSelectedShop: function() {
        var me = this;

        if (!(me.getShopCombo())) {
            return null;
        }

        return me.getShopCombo().getStore().getById(
            me.getShopCombo().getValue()
        );
    },



    onPreviewTheme: function() {

    },

    onSelectTheme: function(view, records) {
        var me = this;
        var record = { };

        me.getListingWindow().previewButton.disable();
        me.getListingWindow().assignButton.disable();
        me.getListingWindow().configureButton.disable();

        if (records.length > 0) {
            record = records.shift();
            me.getListingWindow().previewButton.enable();
            me.getListingWindow().assignButton.enable();
            me.getListingWindow().configureButton.enable();
        }

        me.getInfoPanel().updateInfoView(record);
    }
});