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
 */

//{namespace name=backend/media_manager/view/replace}
//{block name="backend/media_manager/view/replace/window"}
Ext.define('Shopware.apps.MediaManager.view.replace.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.mediamanager-replace-window',
    title: '{s name="mediaManager/replaceWindow/window/title"}{/s}',
    cancelUrl: '{url action=cancel controller=MediaManager}',
    replaceUrl: '{url action=replace controller=MediaManager}',
    updateUrl: '{url action=updateTemporaryMedia controller=MediaManager}',
    height: 'auto',
    maximizable: false,
    minimizable: false,
    resizable: false,
    modal: true,
    width: 615,
    maxHeight: 500,
    baseHeight: 210,
    rowHeight: 136,

    bodyStyle: {
        background: '#F0F2F4'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.height = me.getHeight();
        me.dockedItems = me.createDockedItems();
        me.registerEvents();

        me.callParent(arguments);
    },

    /**
     * Creates all items
     *
     * @return { Array }
     */
    createItems: function() {
        var me = this;

        return [
            me.createInfoPanel(),
            me.createReplaceGrid()
        ]
    },

    /**
     * Registers the required events
     */
    registerEvents: function() {
        var me = this;

        me.replaceGrid.on('uploadReady', me.startUpload, me);
        me.replaceGrid.on('upload-error', me.onError, me);
    },

    /**
     * Calculates the height of the window
     *
     * @return { number }
     */
    getHeight: function() {
        var me = this;

        return me.baseHeight + (me.rowHeight * me.selectedMedias.length);
    },

    /**
     * Creates the replace grid with all selected medias
     *
     * @return { Shopware.apps.MediaManager.view.replace.Grid }
     */
    createReplaceGrid: function() {
        var me = this;

        me.replaceGrid = Ext.create('Shopware.apps.MediaManager.view.replace.Grid', {
            selectedMedias: me.selectedMedias
        });

        return me.replaceGrid;
    },

    /**
     * Creates the docked items
     *
     * @return { Array }
     */
    createDockedItems: function() {
        var me = this;

        return [
            me.createBottomToolbar()
        ];
    },

    /**
     * Creates the info field set
     *
     * @return { Ext.form.FieldSet }
     */
    createInfoPanel: function() {
        var info = Ext.create('Ext.container.Container', {
            html: '{s name="mediaManager/replaceWindow/window/infoText"}{/s}'
        });

        return Ext.create('Ext.form.FieldSet', {
            margin: '10 10 20 10',
            title: '{s name="mediaManager/replaceWindow/window/infoHeader"}{/s}',
            items: [
                info
            ]
        })
    },

    /**
     * Creates the toolbar with cancel and save button
     *
     * @return { Ext.toolbar.Toolbar }
     */
    createBottomToolbar: function() {
        var me = this;

        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            ui: 'shopware-ui',
            cls: 'shopware-toolbar',
            items: [
                '->',
                me.createCancelButton(),
                me.createSaveButton()
            ]
        });
    },

    /**
     * Creates the save button
     *
     * @return { Ext.button.Button }
     */
    createSaveButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            cls: 'primary',
            text: '{s name="mediaManager/replaceWindow/window/save"}{/s}',
            handler: Ext.bind(me.onClickSaveButton, me)
        });
    },

    /**
     * creates the cancel button
     *
     * @return { Ext.button.Button }
     */
    createCancelButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            cls: 'secondary',
            text: '{s name="mediaManager/replaceWindow/window/cancel"}{/s}',
            handler: Ext.bind(me.close, me)
        });
    },

    /**
     * the save button handler
     *
     * @param { Ext.button.Button } button
     */
    onClickSaveButton: function(button) {
        var me = this;

        me.rowIndex = 0;

        me.startUpload();
    },

    /**
     * starts the upload of the selected files and shows a growlMessage if the upload is ready
     */
    startUpload: function() {
        var me = this,
            mediaManager = me.mediaManager,
            selectedRecord = mediaManager.dataView.getSelectionModel().getSelection()[0],
            length = me.replaceGrid.rows.length,
            rows = me.replaceGrid.rows,
            row;

        if (mediaManager.selectedLayout === 'table') {
            selectedRecord = mediaManager.down('mediamanager-media-grid').getSelectionModel().getSelection()[0];
        }

        me.setLoading(true);

        if (me.rowIndex >= length) {
            Shopware.Notification.createGrowlMessage(
                '',
                '{s name="mediaManager/replaceWindow/window/saved"}{/s}'
            );
            mediaManager.mediaStore.load({
                callback: function() {
                    if (!selectedRecord) {
                        return;
                    }

                    var record = mediaManager.mediaStore.getById(selectedRecord.get('id'));
                    if (record) {
                        mediaManager.infoView.update(record.getData());
                    }
                }
            });


            me.close();
            return;
        }

        row = rows[me.rowIndex];
        me.rowIndex++;
        row.startUpload();
    },

    /**
     * Event handler was called if a error occurred
     */
    onError: function() {
        var me = this;

        me.setLoading(false);
    }
});
//{/block}
