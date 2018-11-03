/**
 * Shopware 5
 * Copyright (c) shopware AG
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
 *
 * @category   Shopware
 * @package    MediaManager
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Media Manager Main Controller
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */

//{block name="backend/media_manager/controller/main"}
Ext.define('Shopware.apps.MediaManager.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {
        var me = this,
            albumStore = me.subApplication.getStore('Album'),
            mediaStore = me.subApplication.getStore('Media'),
            minimizable = me.subApplication.minimizable,
            forceToFront = me.subApplication.forceToFront || false;

        if (me.subApplication.params && me.subApplication.params.albumId !== null) {
            if (Ext.isArray(me.subApplication.params.albumId)) {
                albumStore.getProxy().extraParams.albumId = me.subApplication.params.albumId.join(',');
            } else {
                albumStore.getProxy().extraParams.albumId = me.subApplication.params.albumId;
            }
        } else {
            albumStore.getProxy().extraParams.albumId = null;
        }

        me.control({
            'mediamanager-main-window mediamanager-media-view ': {
                'media-view-layout-changed': me.onChangeLayout,
                'media-view-media-quantity-changed': me.onChangeMediaQuantity,
                'media-view-preview-size-changed': me.onChangePreviewSize
            },
            'mediamanager-selection-window mediamanager-media-view ': {
                'media-view-layout-changed': me.onChangeLayout,
                'media-view-media-quantity-changed': me.onChangeMediaQuantity,
                'media-view-preview-size-changed': me.onChangePreviewSize
            }
        });

        /**
         * Initialize the record with default values provided by the view and the mediaGrid.
         */
        this.settingRecord = Ext.create('Shopware.apps.MediaManager.model.Setting', {
            displayType: 'grid',
            itemsPerPage: 20,
            tableThumbnailSize: 16,
            gridThumbnailSize: 72
        });

        // Loading user config from backend
        Ext.Ajax.request({
            url: '{url controller=UserConfig action=get}',
            jsonData: {
                name: 'mediamanager-settings'
            },
            callback: function (request, success, response) {
                var loadedSettings = Ext.JSON.decode(response.responseText);

                if (!Ext.isEmpty(loadedSettings)) {
                    me.settingRecord.set(loadedSettings)
                }

                if(me.subApplication.layout && me.subApplication.layout === 'small') {
                    me.mainWindow = me.getView('main.Selection').create({
                        albumStore: albumStore,
                        mediaStore: mediaStore,
                        selectionHandler: me.subApplication.mediaSelectionCallback,
                        eventScope: me.subApplication.eventScope,
                        selectionMode: me.subApplication.selectionMode,
                        validTypes: me.subApplication.validTypes,
                        forceToFront: forceToFront,
                        minimizable: minimizable
                    });
                } else {
                    me.mainWindow = me.getView('main.Window').create({
                        albumStore: albumStore,
                        mediaStore: mediaStore,
                        validTypes: me.validTypes
                    });
                }

                me.restoreSettings();
                me.mainWindow.show();
            }
        });

        me.callParent(arguments);
    },

    /**
     * Restores the loaded settings into the view.
     */
    restoreSettings: function() {
        var me = this,
            thumbnailSize = me.settingRecord.get(this.settingRecord.get('displayType') + 'ThumbnailSize'),
            view = this.mainWindow.down('mediamanager-media-view'),
            grid = this.mainWindow.down('mediamanager-media-grid');

        // Create subviews
        view.mediaViewContainer.add(view.createMediaView());

        // Sets the number of items displayed
        view.mediaStore.pageSize = this.settingRecord.get('itemsPerPage');
        view.pageSize.store.each(function(item) {
            if(parseInt(item.raw.value) === parseInt(me.settingRecord.get('itemsPerPage'))) {
                view.pageSize.reset();
                view.pageSize.setValue(item.raw.name);

                return false;
            }
        });

        // Activates the correct button for the layout
        view.displayTypeBtn.menu.items.each(function(item) {
            if (item.layout === me.settingRecord.get('displayType')) {
                view.displayTypeBtn.setActiveItem(item);

                return false;
            }
        });

        // Selects the correct layout
        view.selectedLayout = this.settingRecord.get('displayType');
        view.cardContainer.getLayout().setActiveItem((this.settingRecord.get('displayType') === 'grid') ? 0 : 1);

        grid.columns[1].setWidth(thumbnailSize + 10);
        grid.selectedPreviewSize = thumbnailSize;

        // Loads the list of icon sizes for the layout given
        if (view.hasOwnProperty(this.settingRecord.get('displayType') + 'ImageSizes')) {
            view.imageSize.reset();
            view.imageSize.getStore().loadData(view[this.settingRecord.get('displayType') + 'ImageSizes']);
        }

        // Sets the configured icon size
        view.imageSize.setValue(grid.selectedPreviewSize);
    },

    /**
     * Stores the user config settings regarding the visual representation of media in the backend.
     */
    saveSettings: function() {
        Ext.Ajax.request({
            url: '{url controller=UserConfig action=save}',
            method: 'POST',
            jsonData: {
                name: 'mediamanager-settings',
                config: Ext.JSON.encode(this.settingRecord.data)
            }
        });
    },

    /**
     * Changes the list of available icon sizes when the layout is changed.
     *
     * @param { Object } view
     * @param { string } layout
     */
    onChangeLayout: function(view, layout) {
        // This check prevents storing during creation of the window
        if (this.settingRecord.get('displayType') !== layout) {

            // Load the list of available icon sizes
            if (view.hasOwnProperty(layout + 'ImageSizes')) {
                view.imageSize.getStore().loadData(view[layout + 'ImageSizes']);
                view.imageSize.setValue(this.settingRecord.get(layout + 'ThumbnailSize'));
            }

            // Store new layout in model
            this.settingRecord.set('displayType', layout);

            this.saveSettings();
        }
    },

    /**
     * Stores the given new icon size in the settings.
     *
     * @param { Object } view
     * @param { int } newIconSize
     * @param { String } layout
     */
    onChangePreviewSize: function(view, newIconSize, layout) {
        // This check prevents storing during creation of the window
        if (layout === this.settingRecord.get('displayType')) {
            this.settingRecord.set(this.settingRecord.get('displayType') + 'ThumbnailSize', newIconSize);

            this.saveSettings();
        }
    },

    /**
     * Stores the given new number of media per page.
     *
     * @param { Object } view
     * @param { int } itemsPerPage
     */
    onChangeMediaQuantity: function(view, itemsPerPage) {
        // This check prevents storing during creation of the window
        if (this.settingRecord.set('itemsPerPage') !== itemsPerPage) {
            this.settingRecord.set('itemsPerPage', itemsPerPage);

            this.saveSettings();
        }
    }
});
//{/block}
