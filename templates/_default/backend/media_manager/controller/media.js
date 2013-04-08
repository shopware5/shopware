/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

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
        { ref: 'mediaView', selector: 'mediamanager-media-view' },
        { ref: 'albumTree', selector: 'mediamanager-album-tree' }
	],

	/**
	 * Creates the necessary event listener for this
	 * specific controller and opens a new Ext.window.Window
	 * to display the subapplication
     *
     * @return void
	 */
	init: function() {
        var me = this;

        me.control({
            'mediamanager-album-tree': {
                itemclick: me.onChangeMediaAlbum

        /* {if {acl_is_allowed privilege=upload}} */
				,reload: me.onTreeLoad
        /* {/if} */
            },
            'mediamanager-media-view': {
                editLabel: me.onEditLabel
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
			}
        });

        me.callParent(arguments);
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
     * Helper method which sets the valid types
     * for the media selection.
     *
     * Please note that this code will be used multiple times.
     *
     * @public
     * @return void
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
        var fileField = field.getEl().down('input[type=file]').dom;
        var me = this,
            mediaView = me.getMediaView();

        mediaView.mediaDropZone.iterateFiles(fileField.files);
    },

    /**
     * Event listener method which will be fired when the tree
     * on the left hand of the module loads, to reset
     * the request url of the html 5 upload component.
     */
    onTreeLoad: function(store, records, success, operation, eOpts) {
        var me = this,
            mediaView = me.getMediaView();

        var url = mediaView.mediaDropZone.requestURL;
        if (url.indexOf('?albumID=') !== -1) {
            url = url.substr(0, url.indexOf('?albumID='));
        }
        mediaView.mediaDropZone.requestURL = url;
    },

    /**
     * Event listener method which will be fired when the user
     * insert a value in the search field on the right hand of the module,
     * to search media by their name.
     *
     * @param [object] field - Ext.form.field.Text
     * @param [string] value - inserted search value
     */
    onSearchMedia: function(field, value) {
        var me = this,
            mediaView = me.getMediaView(),
            store = mediaView.dataView.store,
            searchString = Ext.String.trim(value),
			childNodes = me.getAlbumTree().getStore().tree.root.childNodes;

        //don't use store.clearFilter(), clearFilter() send an ajax request to reload the store.
        store.filters.clear();
		//Only one album available, so the search will only work in this album
		if(childNodes.length == 1 && !store.getProxy().extraParams.albumID){
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
     * @param [object] view - Ext.tree.Panel
     * @param [object] record - associated Ext.data.Model of the clicked item
     * @return void
     */
    onChangeMediaAlbum: function(view, record) {
        var me = this,
            mediaView = me.getMediaView(),
            store = mediaView.dataView.store,
            proxy = store.getProxy();

        //add the album id as parameter to the request url of the upload field.
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

        /**
         * Re initial the plugin to fix the drag selector zone
         */
        var dragSelector = mediaView.dataView.plugins[0];
        dragSelector.reInit();
    },

    /**
     * Event listener method which fires when the user
     * clicks the "delete media(s)" button in the top toolbar
     *
     * Deletes the currently selected medias.
     *
     * @event click
     * @return void
     */
    onDeleteMedia: function() {
        var me = this,
            view = me.getMediaView().dataView,
            selModel = view.getSelectionModel(),
            selected = selModel.getSelection(),
            store = me.getStore('Media'),
            tree =  me.getAlbumTree(),
            treeStore = tree.getStore(),
            rootNode = tree.getRootNode();

        store.remove(selected);
        store.getProxy().batchActions = false;
        store.sync({
            callback : function() {
                store.load({
                    callback: function() {
                        rootNode.removeAll(false);
                        treeStore.load();
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
     * @param [object] scope - Scope of the fired event Ext.ux.DataView.LabelEditor
     * @param [object] editor - Editor field based on Ext.ux.DataView.LabelEditor
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
    }
});
//{/block}
