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
 */

/**
 * Shopware UI - Media Manager Thumbnail Controller
 *
 * The thumbnail controller handles the thumbnail main window,
 * its elements and the batch calls for the thumbnail generation.
 *
 * @category    Shopware
 * @package     MediaManager
 * @copyright   Copyright (c) shopware AG (http://www.shopware.de)
 */
//{namespace name=backend/media_manager/view/main}
//{block name="backend/media_manager/controller/thumbnail"}
Ext.define('Shopware.apps.MediaManager.controller.Thumbnail', {

    extend: 'Ext.app.Controller',

    snippets: {
        errorTitle: '{s name=error/title}Error{/s}',
        errorMessage: '{s name=thumbnail/batch/error_message}An error has occurred while generating the item thumbnails:{/s}',
        finished: '{s name=thumbnail/batch/finished}Finished{/s}'
    },

    refs: [
        { ref: 'albumTree', selector: 'mediamanager-album-tree' },
        { ref: 'mediaView', selector: 'mediamanager-media-view' }
    ],

    /**
     * This method creates listener for events fired from the
     * album tree, media view and the thumbnail main window
     */
    init: function () {
        var me = this;

        me.control({
            'mediamanager-thumbnail-main': {
                startProcess: me.onStartProcess,
                cancelProcess: me.onCancelProcess,
                closeWindow: me.onCloseWindow
            },
            'mediamanager-album-setting': {
                createThumbnailWindow: me.onCreateThumbnailWindow
            }
        });

        me.callParent(arguments);
    },

    /**
     * Reloads the thumbnail view of the media manager
     */
    onCloseWindow: function () {
        var me = this;

        me.getMediaView().mediaStore.reload();
    },

    /**
     * Enables and disables the generate thumbnail button
     * according if the selected album has defined thumbnail sizes
     *
     * @param selModel
     */
    onSelectAlbum: function (selModel) {
        var me = this,
            thumbnailButton = me.getMediaView().createThumbsBtn,
            record = selModel.getLastSelected();

        if (!record || record && !record.get('id')) {
            thumbnailButton.disable();
            return;
        }

        if (record.get('thumbnailSize').length === 0 || record.get('mediaCount') === 0) {
            thumbnailButton.disable();
        } else if (thumbnailButton.isDisabled()) {
            thumbnailButton.enable();
        }
    },

    /**
     * Creates the main window and saves the album data for later use
     */
    onCreateThumbnailWindow: function (record) {
        var me = this;

        if (!record || record && !record.get('id')) {
            return;
        }

        me.album = record.data;

        me.window = me.getView('thumbnail.Main').create({ }).show();
    },

    /**
     * Triggers if the start generation button was pressed
     * in the thumbnail generation window.
     *
     * @param win
     * @param btn
     */
    onStartProcess: function (win, btn) {
        var me = this;

        me.batchConfig = me.getBatchConfig(win);

        me.cancelOperation = false;

        me.runRequest(0, win);

        btn.hide();
        win.cancelButton.show();
        win.closeButton.disable();
    },

    /**
     * Sets cancelOperation to true which will be checked in the
     * next batch call and will stop.
     *
     * @param btn
     */
    onCancelProcess: function (btn) {
        var me = this;

        btn.disable();

        me.cancelOperation = true;
    },

    /**
     * Returns the needed configuration for the next batch call
     *
     * @param win
     * @returns Object
     */
    getBatchConfig: function (win) {
        var me = this;

        return {
            batchSize: win.batchSizeCombo.getValue(),
            snippet: win.snippets.batch.process,
            totalCount: me.album.mediaCount,
            progress: win.thumbnailProgress,
            requestUrl: '{url controller="MediaManager" action="createThumbnails"}',
            params: {
                albumId: me.album.id
            }
        }
    },

    /**
     * This function sends a request to generate new thumbnails
     *
     * @param offset
     * @param win
     */
    runRequest: function (offset, win) {
        var me = this,
                config = me.batchConfig,
                params = config.params;

        me.errors = me.errors || [];

        // if cancel button was pressed
        if (me.cancelOperation) {
            win.closeButton.enable();
            return;
        }

        if (config.progress) {
            // sets a new progress status
            config.progress.updateProgress(
                    (offset + config.batchSize) / config.totalCount,
                    Ext.String.format(
                            config.snippet,
                            (offset + config.batchSize) > config.totalCount ? config.totalCount : (offset + config.batchSize),
                            config.totalCount
                    ),
                    true
            );
        }

        params.offset = offset;
        params.limit = config.batchSize;

        // Sends a request to create new thumbnails according to the batch informations
        Ext.Ajax.request({
            url: config.requestUrl,
            method: 'POST',
            params: params,
            timeout: 4000000,
            success: function (response) {
                var operation = Ext.decode(response.responseText);

                if (operation.success !== true) {
                    me.errors.push(operation.message);
                }

                if (operation.fails && operation.fails.length > 0) {
                    Shopware.Notification.createGrowlMessage(
                        "",
                        operation.fails.join("\n<br>")
                    );
                }

                var newOffset = (offset + config.batchSize);

                if (newOffset > config.totalCount) {
                    config.batchSize = config.totalCount - offset;
                    newOffset = (offset + config.batchSize);
                }

                if (newOffset === config.totalCount) {
                    me.batchConfig.progress.updateText(me.snippets.finished);
                    me.onProcessFinish(win);
                    return;
                }

                me.runRequest(newOffset, win);
            },
            failure: function (response) {
                Shopware.Msg.createStickyGrowlMessage({
                    title: '{s name=thumbnail/batch/timeOutTitle}An error occured{/s}',
                    text: "{s name=thumbnail/batch/timeOut}The server could not handle the request. Please choose a smaller batch size.{/s}"
                });

                me.onProcessFinish(win);
            }
        });
    },

    /**
     * Will be called when every thumbnails were generated
     *
     * @param win
     */
    onProcessFinish: function (win) {
        var me = this;

        if (!Ext.isEmpty(me.errors)) {
            var message = me.errors.join("\n");

            Shopware.Msg.createStickyGrowlMessage({
                title: me.snippets.errorTitle,
                text: me.snippets.errorMessage + '\n' + message
            });

            me.errors = [];
        }

        win.cancelButton.hide();
        win.closeButton.enable();
    }
});
//{/block}
