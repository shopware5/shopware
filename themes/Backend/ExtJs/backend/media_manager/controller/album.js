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
 * Shopware UI - Media Manager Album Controller
 *
 * this file handles the album administration.
 */
//{namespace name=backend/media_manager/view/main}
//{block name="backend/media_manager/controller/album"}
Ext.define('Shopware.apps.MediaManager.controller.Album', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

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
        { ref: 'albumTree', selector: 'mediamanager-album-tree' },
        { ref: 'mediaView', selector: 'mediamanager-media-view' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function () {
        var me = this;

        me.control({
            // Open add window
            'mediamanager-album-tree button[action=mediamanager-album-tree-add]': {
                click: me.onOpenAddWindow
            },
            //remove selected album
            'mediamanager-album-tree button[action=mediamanager-album-tree-delete]': {
                click: me.onDeleteAlbumButton
            },
            'mediamanager-album-tree textfield[action=mediamanager-album-tree-search]': {
                change: me.onSearchAlbum
            },
            // Save new album
            'mediamanager-album-add button[action=mediamanager-album-add-add]': {
                click: me.onAddAlbum
            },
            'mediamanager-album-tree': {
                addAlbum: me.onOpenAddWindow,
                addSubAlbum: me.onAddSubAlbum,
                deleteAlbum: me.onDeleteAlbum,
                reload: me.onReloadAlbums,
                editSettings: me.onOpenSettingsWindow,
                itemmove: me.onMoveAlbum,
                emptyTrash: me.onEmptyTrash
            },
            'mediamanager-media-view html5fileupload': {
                uploadReady: me.onReload
            },
            // Save album settings
            'mediamanager-album-setting button[action=mediamanager-album-setting-save]': {
                click: me.onSaveSettings
            },
            'mediamanager-album-setting': {
                generateThumbnails: me.onGenerateThumbnails
            }
        });

        me.callParent(arguments);
    },

    /**
     * Event listener method which fired when the user uploads files
     * and the upload completed. Refreshs the tree and select the last
     * selected node.
     */
    onReload: function () {
        var me = this,
            tree = this.getAlbumTree(),
            store = this.getStore('Album'),
            selModel = tree.getSelectionModel(),
            selected = tree.selModel.selected.items[0];

        var rootNode = tree.getRootNode();
        rootNode.removeAll(false);

        tree.setLoading(true);
        store.load({
            callback: function () {

                if (selected) {
                    var lastSelected = store.getNodeById(selected.data.id);
                    me.expandParent(lastSelected);
                    selModel.select(lastSelected);
                }
                tree.setLoading(false);

            }
        });
    },

    expandParent: function (node) {
        var me = this;
        if (!node) {
            return;
        }
        node.expand();
        if (!node.parentNode) {
            return;
        }
        me.expandParent(node.parentNode);
    },

    /**
     * Event listener method which fires when the user
     * clicks on the "remove album"-button below the tree.
     *
     * @return void
     */
    onDeleteAlbumButton: function () {
        var me = this,
            tree = me.getAlbumTree(),
            store = tree.store,
            selected = tree.selModel.selected.items[0];

        if (!selected) {
            return;
        }

        //delete only leaf albums
        if (selected.data.leaf) {
            selected.set('albumID', selected.get('id'));
            tree.setLoading(true);
            selected.destroy({
                callback: function () {
                    var rootNode = tree.getRootNode();
                    rootNode.removeAll(false);

                    store.load();
                    tree.setLoading(false);
                }
            })

        }
    },

    /**
     * Filters the tree with the passed search value, which inserted in the
     * search field on the left hand of the module.
     *
     * @param field
     * @param value
     */
    onSearchAlbum: function (field, value) {
        var me = this,
            tree = me.getAlbumTree(),
            searchString = Ext.String.trim(value),
            store = tree.store,
            view = tree.viewConfig.plugins.cmp,
            plugin = view.initialConfig.plugins.cmp.plugins[0];

        //lock drag and drop if the tree is filtered.
        if (searchString.length > 0) {
            plugin.dropZone.lock();
            plugin.dragZone.lock();
        } else {
            plugin.dropZone.unlock();
            plugin.dragZone.unlock();
        }

        //search value changed?
        if (store.getProxy().extraParams.filter === 'undefined' && searchString.length == 0) {
            return;
        }
        if (store.getProxy().extraParams.filter === searchString) {
            return;
        }

        tree.setLoading(true);
        store.getProxy().extraParams = { albumFilter: searchString };

        var rootNode = tree.getRootNode();
        rootNode.removeAll(false);

        //don't use store.clearFilter(), clearFilter() send an ajax request to reload the store.
        store.load({
            callback: function () {
                tree.setLoading(false);
            }
        });
    },

    /**
     * Event listener method which fires when the user
     * clicks the "add album"-button in the toolbar of the
     * album tree.
     *
     * Opens the "add album"-window.
     *
     * @event click
     * @return void
     */
    onOpenAddWindow: function () {
        var me = this,
            tree = me.getAlbumTree(),
            selModel = tree.getSelectionModel(),
            selection = selModel.getSelection(),
            parentId;

        if (selection && selection.length === 1) {
            selection = selection[0];

            parentId = selection.get('id');
            me.getView('album.Add').create({ parentId: parentId });
        } else {
            me.getView('album.Add').create();
        }

    },

    /**
     * Event listener method which fires when the user
     * clicks in the item context menu on the "edit settings" button.
     *
     * Opens the "album settings"-window
     *
     * @event editSettings
     * @param [object] scope - Scope of the fired event
     * @param [object] view - the Ext.tree.Panel
     * @param [object] record - clicked Ext.data.Model
     * @return void
     */
    onOpenSettingsWindow: function (scope, view, record) {
        this.getView('album.Setting').create({ settings: record });
    },

    /**
     * Event listener method which fires when the user
     * clicks the "save"-button in the "add"-window.
     *
     * The method adds a new album to the store
     *
     * @event click
     * @param [object] btn - pressed Ext.button.Button
     */
    onAddAlbum: function (btn) {
        var win = btn.up('window'),
            form = win.down('form'),
            values = form.getForm().getValues(),
            me = this,
            model = me.getModel('Album').create(values),
            store = me.getAlbumTree().store,
            tree = me.getAlbumTree();

        tree.setLoading(true);

        model.save({
            callback: function () {
                var rootNode = tree.getRootNode();
                rootNode.removeAll(false);

                if (win.closeAction == 'destroy') {
                    win.destroy();
                } else {
                    win.close()
                }
                store.load();
                tree.setLoading(false);
            }
        })
    },

    /**
     * Event listener method which fires when the user
     * clicks the "add subalbum"-button in the item
     * context menu.
     *
     * Opens the "add album"-window with the parent id
     *
     * @event addSubAlbum
     * @param [object] scope - Scope of the fired event
     * @param [object] view - Ext.tree.Panel which has fired the event
     * @param [object] record - Associated Ext.data.Model
     * @return void
     */
    onAddSubAlbum: function (scope, view, record) {
        var parentId = record.get('id');
        this.getView('album.Add').create({ parentId: parentId });
    },

    /**
     * Event listener method which fires when the user
     * clicks the "delete album"-button in the item
     * context menu.
     *
     * Deletes the associated album.
     *
     * @event deleteAlbum
     * @param scope
     * @param view
     * @param record
     */
    onDeleteAlbum: function (scope, view, record) {
        var me = this,
            tree = me.getAlbumTree(),
            store = tree.store;

        record.set('albumID', record.get('id'));
        tree.setLoading(true);
        record.destroy({
            callback: function () {
                var rootNode = tree.getRootNode();
                rootNode.removeAll(false);

                store.load();
                tree.setLoading(false);
            }
        })
    },

    /**
     * Event listener method which fires when the user
     * clicks the "delete album"-button in the item
     * context menu.
     *
     * Deletes the associated album.
     *
     * @event deleteAlbum
     * @param scope
     * @param view
     * @param record
     */
    onEmptyTrash: function () {
        var me = this;

        Ext.Msg.confirm('{s name=recycle/title}Empty recycle bin?{/s}', '{s name=recycle/message}All items in the recycle bin will be deleted and can not be recovered. Dou you want to continue?{/s}', function (btn) {
            if (btn !== 'yes') {
                return false;
            }

            Ext.Ajax.request({
                url: '{url controller="MediaManager" action="emptyTrash"}',
                success: function(response) {
                    var response = Ext.JSON.decode(response.responseText);
                    if(response.success) {
                        me.onReload();
                        me.getController('Media').onReload();
                    }
                }
            });
        });
    },

    /**
     * Event listener method which fires when the user
     * clicks on the "reload albums"-button in the container
     * context menu.
     *
     * Reloads the associated Ext.data.Tree.Store
     *
     * @event reload
     * @return void
     */
    onReloadAlbums: function () {
        var me = this,
            tree = me.getAlbumTree(),
            store = tree.store;

        var rootNode = tree.getRootNode();
        rootNode.removeAll(false);

        tree.setLoading(true);
        store.load({
            callback: function () {
                tree.setLoading(false);
            }
        });
    },

    /**
     * Event listener method which fires when the user
     * moves an album to a different place.
     *
     * Moves an album to a different position.
     *
     * @event itemmove
     * @param [object] node - actual Ext.data.Model
     * @param [object] oldParent - old Ext.data.Model
     * @param [object] newParent - updated Ext.data.Model
     * @return void
     */
    onMoveAlbum: function (node, oldParent, newParent) {
        node.data.position = node.data.index + 1;
        if (newParent.data.id !== 'root') {
            node.data.parentId = newParent.data.id;
        } else {
            node.data.parentId = 0;
        }
        node.save();
    },

    /**
     * Event listener method which will be fired when the user
     * clicks the "save settings"-button.
     *
     * Changes the album settings
     *
     * @event click
     * @param [object] btn - pressed Ext.button.Button
     * @return void
     */
    onSaveSettings: function (btn) {
        var me = this,
            win = btn.up('window'),
            model = win.settings,
            thumbItems = win.thumbnailStore.data.items,
            thumbChanged = me.handleAlbumModelData(model, win);

        me.setAlbumSizes(model, thumbItems);

        model.save({
            callback: function () {
                if (thumbChanged) {
                    Ext.Msg.confirm('{s name=settings/newSizesTitle}Generate new thumbnails?{/s}', '{s name=settings/generateNewSizes}New thumbnail sizes have been defined. Would you like to generate them now?{/s}', function (btn) {
                        if (btn !== 'yes') {
                            return false;
                        }

                        win.fireEvent('createThumbnailWindow', model);
                    });
                }

                win.close();
                me.getAlbumTree().fireEvent('reload');
            }
        });
    },

    /**
     * Reads out the settings window form and sets its
     * values to the album model.
     *
     * Returns boolean status whether thumbnail sizes
     * were changed or not.
     *
     * @param model
     * @param win
     * @param setSizes
     * @returns boolean
     */
    handleAlbumModelData: function (model, win) {
        var me = this,
            form = win.down('form'),
            values = form.getValues(),
            thumbItems = win.thumbnailStore.data.items,
            thumbChanged = me.checkThumbnailsChanged(thumbItems, model.get('thumbnailSize'));

        model.set(values);

        return thumbChanged;
    },

    setAlbumSizes: function(model, thumbItems){
        var sizes = [];

        Ext.each(thumbItems, function (item) {
            sizes.push(item.data);
        });

        model.set('thumbnailSize', sizes);
    },

    /**
     * This method first saves the album and generates the
     * thumbnails afterwards with the defined sizes.
     *
     * @param window
     */
    onGenerateThumbnails: function (window) {
        var me = this,
            model = window.settings,
            thumbItems = window.thumbnailStore.data.items,
            thumbChanged = me.handleAlbumModelData(model, window);

        if (thumbChanged) {
            Ext.Msg.confirm('{s name=settings/saveNowTitle}Save album?{/s}', '{s name=settings/saveBeforeGeneration}The album must first be saved. Save album now?{/s}', function (btn) {
                if (btn !== 'yes') {
                    return false;
                }

                me.setAlbumSizes(model, thumbItems);

                // save the album and generate the thumbnails on the callback
                model.save({
                    callback: function () {
                        window.fireEvent('createThumbnailWindow', model);
                    }
                });
            });
        } else {
            window.fireEvent('createThumbnailWindow', model);
        }
    },

    /**
     * Determines if the thumbnails sizes have changed
     *
     * @param thumbnailItems
     * @param oldSizes
     * @returns bool
     */
    checkThumbnailsChanged: function (thumbnailItems, oldSizes) {
        var thumbnails = [];

        // check if new thumbnails were created or changed
        Ext.each(thumbnailItems, function (item) {
            thumbnails.push(item.data);
        });

        return JSON.stringify(oldSizes) != JSON.stringify(thumbnails);
    }
});
//{/block}
