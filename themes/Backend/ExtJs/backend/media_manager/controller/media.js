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

//{namespace name=backend/media_manager/view/main}

/**
 * Shopware UI - Media Manager Media Controller
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */

//{block name="backend/media_manager/controller/media"}
Ext.define('Shopware.apps.MediaManager.controller.Media', {

    /**
     * Extend from the standard ExtJS 4 controller
     *
     * @string
     */
    extend: 'Ext.app.Controller',
    snippets: {
        confirmMsgBox: {
            deleteTitle: '{s name=confirmMsgBox/deleteTitle}Delete media files{/s}',
            deleteText: '{s name=confirmMsgBox/deleteText}Are you sure you want to delete all selected media files?{/s}'
        }
    },

    /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * Example: { ref : 'grid', selector : 'grid' } transforms to this.getGrid();
     *          { ref : 'addBtn', selector : 'button[action=add]' } transforms to this.getAddBtn()
     *
     * @object
     */
    refs: [
        { ref: 'mediaView', selector: 'mediamanager-media-view' },
        { ref: 'albumTree', selector: 'mediamanager-album-tree' },
        { ref: 'mediaGrid', selector: 'mediamanager-media-grid' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init: function() {
        var me = this;

        me.control({
            'mediamanager-album-tree': {
                itemclick: me.onChangeMediaAlbum,
                startBatchMoveMedia: me.moveMedias

        /* {if {acl_is_allowed privilege=upload}} */
                ,reload: me.onTreeLoad
        /* {/if} */
            },
            'mediamanager-media-view': {
                editLabel: me.onEditLabel,
                changePreviewSize: me.onChangePreviewSize
            },
            'mediamanager-media-view button[action=mediamanager-media-view-layout]': {
                change: me.onChangeLayout
            },
            'mediamanager-media-view button[action=mediamanager-media-view-delete]': {
                click: me.onDeleteMedia
            },
            'mediamanager-media-view textfield[action=mediamanager-media-view-search]': {
                change: me.onSearchMedia
            },
            'mediamanager-media-view html5fileupload': {
                uploadReady: me.onReload
            },
        /* {if {acl_is_allowed privilege=upload}} */
            'mediamanager-media-view filefield': {
                change: me.onMediaUpload
            },
        /* {/if} */
            'mediamanager-selection-window textfield[action=mediamanager-selection-window-searchfield]': {
                change: me.onSearchMedia
            },
            'mediamanager-media-grid': {
                'showDetail': me.onShowDetails,
                'edit': me.onGridEditLabel
            }
        });

        me.callParent(arguments);
    },

    moveMedias: function(view, medias) {
        var me = this;

        me.getView('batchMove.BatchMove').create({
            sourceView: view, mediasToMove: medias, mediaGrid: me.getMediaGrid(), mediaView: me.getMediaView()
        }).show();
    },

    /**
     * Event listener method which fired when the user uploads a file.
     * Reloads the store to refresh the data view.
     */
    onReload: function() {
        var me = this, validTypes = me.subApplication.validTypes,
            store = me.getStore('Media');

        if(validTypes) {
            var proxy = store.getProxy();
            proxy.extraParams.validTypes = me.setValidTypes();
        }

        store.load();
    },

    /**
     * Helper method which sets the valid types for the media selection.
     *
     * Please note that this code will be used multiple times.
     *
     * @public
     * @return string
     */
    setValidTypes: function() {
        var me = this,
            types = me.subApplication.validTypes,
            filters = '';

        Ext.each(types, function(typ) {
            filters += typ + '|';
        });
        filters = filters.substr(0, filters.length-1);

        return filters;
    },

    /**
     * Event listener method which will be fired when the user
     * want to upload files over the upload button.
     * The files will be iterated and uploaded via the media manager backend controller.
     *
     * @param field
     */
    onMediaUpload: function(field) {
        var me = this,
            fileField = field.getEl().down('input[type=file]').dom,
            mediaView = me.getMediaView();

        field.reset();

        // Fire afterrender event after reset
        // to trigger custom dom manipulation.
        field.fireEvent('afterrender', field);

        mediaView.mediaDropZone.iterateFiles(fileField.files);
    },

    /**
     * Event listener method which will be fired when the tree
     * on the left hand of the module loads, to reset
     * the request url of the html 5 upload component.
     *
     * @param { Shopware.apps.MediaManager.model.Album } treeNode
     */
    onTreeLoad: function(treeNode) {
        var me = this,
            mediaView = me.getMediaView();

        var url = mediaView.mediaDropZone.requestURL;
        if (url.indexOf('?albumID=') !== -1) {
            url = url.substr(0, url.indexOf('?albumID='));
        }
        mediaView.mediaDropZone.requestURL = url;

        if(treeNode.hasOwnProperty('get')) {
            mediaView.mediaStore.getProxy().extraParams.albumID = treeNode.get('id');

            if (url.indexOf('?albumID=') !== -1) {
                url = url.substr(0, url.indexOf('?albumID='));
            }
            url += '?albumID=' + treeNode.get('id');
            mediaView.mediaDropZone.requestURL = url;

            mediaView.mediaStore.load();
        }
    },

    /**
     * Event listener method which will be fired when the user
     * insert a value in the search field on the right hand of the module,
     * to search media by their name.
     *
     * @param { object } field - Ext.form.field.Text
     * @param { string } value - inserted search value
     */
    onSearchMedia: function(field, value) {
        var me = this,
            mediaView = me.getMediaView(),
            store = mediaView.dataView.store,
            searchString = Ext.String.trim(value),
            childNodes = me.getAlbumTree().getStore().tree.root.childNodes;

        // Don't use store.clearFilter(), clearFilter() send an ajax request to reload the store.
        store.filters.clear();
        // Only one album available, so the search will only work in this album
        if(childNodes.length === 1 && !store.getProxy().extraParams.albumID){
            store.getProxy().extraParams.albumID = childNodes[0].getId();
        }
        store.currentPage = 1;
        store.filter('name', searchString);
    },

    /**
     * Event listener method which will be fired when the user
     * clicks on an album in the tree on the left hand of the
     * module.
     *
     * Loads the media for the associated album and displays
     * them into an dataview.
     *
     * @event itemclick
     * @param { object } view - Ext.tree.Panel
     * @param { object } record - associated Ext.data.Model of the clicked item
     * @return void
     */
    onChangeMediaAlbum: function(view, record) {
        var me = this,
            mediaView = me.getMediaView(),
            store = mediaView.dataView.store,
            proxy = store.getProxy();

        //  Add the album id as parameter to the request url of the upload field.
        /* {if {acl_is_allowed privilege=upload}} */
        var url = mediaView.mediaDropZone.requestURL;
        if (url.indexOf('?albumID=') !== -1) {
            url = url.substr(0, url.indexOf('?albumID='));
        }
        url = url + '?albumID=' + record.get('id');
        mediaView.mediaDropZone.requestURL = url;
        /* {/if} */

        // Set the delete button disabled if we change the album
        if(mediaView.deleteBtn && !mediaView.deleteBtn.isDisabled()) {
            mediaView.deleteBtn.setDisabled(true);
        }
        proxy.extraParams = { albumID: record.get('id') };

        var validTypes = me.subApplication.validTypes;
        if(validTypes) {
            proxy.extraParams.validTypes = me.setValidTypes();
        }
        store.filters.clear();
        store.currentPage = 1;
        store.load();

         // Re initial the plugin to fix the drag selector zone
        var dragSelector = mediaView.dataView.plugins[0];
        dragSelector.reInit();
    },

    /**
     * Event listener method which fires when the user
     * clicks the "delete media(s)" button in the top toolbar
     *
     * Shows a confirmation message box
     *
     * @event click
     * @return void
     */
    onDeleteMedia: function() {
        var me = this;

        Ext.MessageBox.confirm(
            me.snippets.confirmMsgBox.deleteTitle,
            me.snippets.confirmMsgBox.deleteText,
            function (button) {
                if (button === 'yes'){
                    me.deleteMedia();
                }
            },
        this);
    },

    /**
     * Deletes the currently selected medias.
     * Will be executed if the user confirms to delete the selected medias
     *
     * @return void
     */
    deleteMedia: function() {
        var me = this,
            tree =  me.getAlbumTree(),
            treeStore = tree.getStore(),
            rootNode = tree.getRootNode(),
            store = me.getStore('Media'),
            mediaView = me.getMediaView(),
            cardContainer = mediaView.cardContainer,
            selModel, selected, view;

        mediaView.setLoading(true);

        if (mediaView.selectedLayout === 'grid') {
            view = mediaView.dataView;
        } else {
            view = cardContainer.getLayout().getActiveItem();
        }

        selModel = view.getSelectionModel();
        selected = selModel.getSelection();

        mediaView.attributeButton.hide();

        store.remove(selected);
        store.getProxy().batchActions = false;
        store.sync({
            callback : function() {
                mediaView.setLoading(false);
                store.load({
                    callback: function() {
                        tree.fireEvent('refresh', tree);
                        mediaView.deleteBtn.setDisabled(true);
                    }
                });
            }
        });
    },

    /**
     * Event listener method which fires when the user
     * edits the label of a media.
     *
     * Edits the name of the media.
     *
     * @event editLabel
     * @param { object } scope - Scope of the fired event Ext.ux.DataView.LabelEditor
     * @param { object } editor - Editor field based on Ext.ux.DataView.LabelEditor
     * @param { object } value
     */
    onEditLabel: function(scope, editor, value) {
        var record = editor.activeRecord,
            store = this.getStore('Media'),
            proxy = store.getProxy();

        if (value.length > 0) {
            record.set('name', value);
        }

        record.set('albumID', proxy.extraParams.albumID);
        record.save({
            callback: function() {
                store.load();
            }
        });
    },

    /**
     * Event listener method which will be triggered when the user
     * selects an entry in the list view.
     *
     * The method unlocks the `delete` button (if available) and updates
     * the `info` view on the right hand of the module (if available).
     *
     * @param { Ext.grid.Panel } grid - The list view panel
     * @param { Array } selection - The selected entries in the list view
     * @returns { void|Boolean } Falsy, if no entry is selected. Otherwise `void`
     */
    onShowDetails: function(grid, selection) {
        var me = this, view = me.getMediaView(),
            record;

        if(view.deleteBtn) {
            view.deleteBtn.setDisabled(!selection.length);
            view.replaceButton.setDisabled(!selection.length);
        }

        if(!selection.length) {
            return false;
        }
        record = selection[0];

        if(view.infoView) {
            view.infoView.update(record.data);
        }
    },

    /**
     * Event listener method which will be fired when the user clicks
     * on the `change layout` button.
     *
     * The method sets the correct active item.
     *
     * @param { ?Ext.button.Button } button - The clicked button
     * @param { Object } item - The configuration of the active layout
     * @returns { void }
     */
    onChangeLayout: function(button, item) {
        var view = this.getMediaView();

        view.selectedLayout = item.layout;
        view.cardContainer.getLayout().setActiveItem((item.layout === 'grid') ? 0 : 1);

        view.fireEvent('media-view-layout-changed', view, item.layout);
    },

    /**
     * Event listener method which will be fired when the user edits the name of an
     * entry in the list view using the row editor.
     *
     * The method is just a wrapper for the `onEditLabel`-method.
     *
     * @param { Ext.grid.pluginRowEditing } editor - The used editor
     * @param { Object } eOpts - Additional event options
     */
    onGridEditLabel: function(editor, eOpts) {
        var me = this;
        editor.activeRecord = eOpts.record;
        me.onEditLabel(me, editor, eOpts.newValues.name);
    },

    /**
     * Event listener method which will be fired when the user changes
     * the selected preview size.
     *
     * The method reloads the store to trigger the re-rendering of the list view
     * and resizes the `preview` column.
     *
     * @param { Ext.form.field.ComboBox } field - The field which has fired the event
     * @param { String|Number } newValue - New field value
     * @param { String|Number } value - Last value of the field
     * @returns { void|Boolean } Falsy, if the old value is empty or the user hasn't changed
     *          the selected item. Otherwise `void`
     */
    onChangePreviewSize: function(field, newValue, value) {
        var me = this,
            grid = me.getMediaGrid(),
            view = me.getMediaView(),
            iconSize;

        // Cast the passed value to a number
        iconSize = ~~(1 * newValue);

        if (!view || iconSize === 0) {
            return false;
        }

        // Change the thumbnail size for the table view, sadly we need to recreate the view.
        view.thumbnailSize = iconSize;
        view.mediaViewContainer.removeAll();

        /* {if {acl_is_allowed privilege=upload}} */
        view.mediaViewContainer.add(view.createDropZone());
        /* {/if} */

        view.mediaViewContainer.add(view.createMediaView());

        // 1) Set the icon preview size on the grid
        // 2) Refresh the view
        // 3) Resize the first column to fit the new icon size
        grid.selectedPreviewSize = iconSize;
        grid.getView().refresh();
        grid.columns[1].setWidth((iconSize < 50) ? 50 : iconSize + 10);

        // Prevents the first event to re-render the list view
        if (!value || newValue === value) {
            return false;
        }

        view.fireEvent('media-view-preview-size-changed', view, iconSize, view.selectedLayout);
    }
});
//{/block}
