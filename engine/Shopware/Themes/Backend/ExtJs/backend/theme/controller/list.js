/**
 * Shopware 4
 * Copyright © shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/theme/main}

//{block name="backend/theme/controller/list"}

Ext.define('Shopware.apps.Theme.controller.List', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'listing', selector: 'theme-listing' },
        { ref: 'listingView', selector: 'theme-listing dataview' },
        { ref: 'listingWindow', selector: 'theme-list-window' },
        { ref: 'shopCombo', selector: 'theme-list-window combobox[name=shop]' },
        { ref: 'infoPanel', selector: 'theme-listing-info-panel' }
    ],

    init: function () {
        var me = this;

        me.control({
            'theme-listing html5fileupload': {
                fileUploaded: me.onThemeUploaded
            },
            'theme-listing dataview': {
                selectionchange: me.onSelectTheme
            },
            'theme-list-window': {
                'search-theme': me.onSearchTheme,
                'refresh-list': me.onRefreshList,
                'create-theme': me.onCreateTheme
            },
            'theme-listing-info-panel' : {
                'assign-theme': me.onAssignTheme,
                'preview-theme': me.onPreviewTheme
            }
        });

        me.mainWindow = me.getView('list.Window').create({ }).show();
    },

    onRefreshList: function() {
        this.getListingView().getStore().load();
    },

    onCreateTheme: function() {
        var me = this;

        me.getView('create.Window').create({
            record: Ext.create('Shopware.apps.Theme.model.Theme')
        }).show();
    },

    /**
     * Event listener of the upload drop zone.
     * Called after the zip archive uploaded.
     */
    onThemeUploaded: function() {
        this.getListingView().getStore().load();
    },

    /**
     * Event listener of the listing window search field.
     * Filters the store with the passed value.
     *
     * @param window
     * @param field
     * @param value
     */
    onSearchTheme: function (window, field, value) {
        var me = this,
            listing = me.getListingView(),
            store = listing.getStore();

        value = Ext.String.trim(value);
        store.filters.clear();
        store.currentPage = 1;

        if (value.length > 0) {
            store.filter({ property: 'search', value: value });
        } else {
            store.load();
        }
    },

    /**
     * Event listener of the toolbar "assign button".
     * Switches the shop template.
     */
    onAssignTheme: function () {
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
            success: function (response, opts) {
                var message = Ext.String.format(
                    '{s name="assign_message"}Theme [0] assigned to shop [1]{/s}',
                    theme.get('name'),
                    shop.get('name')
                );

                Shopware.Notification.createGrowlMessage(
                    '{s name="application"}Theme manager 2.0{/s}',
                    message,
                    'Theme manager'
                );

                me.getListingView().getStore().load();
            }
        });
    },


    /**
     * Event listener function of the "preview theme" listing toolbar button.
     */
    onPreviewTheme: function () {
        var me = this, shop, theme,
            url = '{url controller="theme" action="preview"}';

        shop = me.getSelectedShop();
        theme = me.getSelectedTheme();

        //preview window already opened?
        if (me.previewWindow) {
            me.previewWindow.close();
            me.previewWindow = null;

            me.getInfoPanel().previewButton.setText('{s name=preview}Preview theme{/s}');
            me.removePreviewFlag()
        } else {
            url += '?themeId=' + theme.get('id') + '&shopId=' + shop.get('id');

            me.getInfoPanel().previewButton.setText('{s name=stop_preview}Stop preview{/s}');
            theme.set('preview', true);
            me.previewWindow = window.open(url);
        }

        me.enableToolbarButtons();
    },

    /**
     * Helper function which removes the preview flag of each listing record.
     */
    removePreviewFlag: function() {
        var me = this,
            store = me.getListingView().getStore();

        store.each(function(item) {
            item.set('preview', false);
        });
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
    onSelectTheme: function (view, records) {
        var me = this;
        var record = { };

        if (records.length > 0) {
            record = records.shift();
        }

        me.enableToolbarButtons();

        me.getInfoPanel().updateInfoView(record);
    },

    /**
     * Helper function which enables/disables the listing
     * window toolbar buttons for the current state.
     */
    enableToolbarButtons: function() {
        var me = this;

        var record = me.getSelectedTheme();

        me.getInfoPanel().previewButton.disable();
        me.getInfoPanel().assignButton.disable();
        me.getInfoPanel().configureButton.disable();

        if (record instanceof Ext.data.Model) {
            me.getInfoPanel().previewButton.enable();
            me.getInfoPanel().assignButton.enable();

            if (record.get('hasConfig')) {
                me.getInfoPanel().configureButton.enable();
            }
        }

        if (me.previewWindow) {
            me.getInfoPanel().previewButton.enable();
        }
    },

    /**
     * Returns the selected theme model of the theme listing
     *
     * @returns { Shopware.apps.Theme.model.Theme }
     */
    getSelectedTheme: function () {
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
    getSelectedShop: function () {
        var me = this;

        if (!(me.getShopCombo())) {
            return null;
        }

        return me.getShopCombo().getStore().getById(
            me.getShopCombo().getValue()
        );
    }


});

//{/block}
