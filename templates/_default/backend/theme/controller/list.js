
Ext.define('Shopware.apps.Theme.controller.List', {
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



    /**
     * Event listener of the toolbar "assign button".
     * Switches the shop template.
     */
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



    onPreviewTheme: function() {

    },

    /**
     * Event listener of the theme listing - selectionchange
     * event.
     *
     * Disables / Enables the toolbar buttons and refresh the info panel.
     *
     * @param view
     * @param records
     */
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

            if (record && record.getElements().getCount() > 0) {
                me.getListingWindow().configureButton.enable();
            }
        }

        me.getInfoPanel().updateInfoView(record);
    },

    /**
     * Returns the selected theme model of the theme listing
     *
     * @returns { Shopware.apps.Theme.model.Theme }
     */
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

    /**
     * Returns the selected shop model of the toolbar combo box.
     *
     * @returns { Shopware.apps.Base.model.Shop }
     */
    getSelectedShop: function() {
        var me = this;

        if (!(me.getShopCombo())) {
            return null;
        }

        return me.getShopCombo().getStore().getById(
            me.getShopCombo().getValue()
        );
    }


});