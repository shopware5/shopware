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
 * @package    Blog
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware Controller - Blog backend module
 *
 * Media controller of the blog module. Handles all action around the media part of sub-application
 */
//{namespace name=backend/blog/view/blog}
//{block name="backend/blog/controller/media"}
Ext.define('Shopware.apps.Blog.controller.Media', {
    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend:'Ext.app.Controller',

    /**
     * All references to get the elements by the applicable selector
     */
    refs:[
        { ref:'mediaList', selector:'blog-blog-detail-sidebar-options dataview[name=image-listing]' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the sub-application
     *
     * @return void
     */
    init:function () {
        var me = this;
        me.control({
            'blog-blog-detail-sidebar-options mediaselectionfield': {
                selectMedia: me.onMediaAdded
            },
            'blog-blog-detail-sidebar-options': {
                mediaSelect: me.onSelectMedia,
                mediaDeselect: me.onDeselectMedia,
                markPreviewImage: me.onMarkPreviewImage,
                removeImage: me.onRemoveImage
            }
        });
    },

    /**
     * Fires after the user selects one or media in the media manager
     * and presses the "apply selection"-button in the media manager.
     *
     * @event selectMedia
     * @param { object } dropZone - Shopware.MediaManager.DropZone
     * @param { array } images - Array of the selected Ext.data.Model's
     * @param { object } selModel - Associated Ext.selection.Model
     */
    onMediaAdded: function(dropZone, images, selModel) {
        var me = this,
                mediaList = me.getMediaList(),
                store = mediaList.getStore();

        if (images.length === 0) {
            return true;
        }

        Ext.each(images, function(item) {
            var media = Ext.create('Shopware.apps.Blog.model.Media', item.data);
            media.set('mediaId', item.get('id'));

            if (store.getCount() === 0) {
                media.set('preview', 1);
            }
            media.set('id', 0);
            store.add(media);
        });
    },

    /**
     * Event will be fired when the user select an image in the listing.
     *
     * @param { Ext.selection.DataViewModel } dataViewModel The selection data view model of the Ext.view.View
     * @param { Shopware.apps.Article.model.Media } media The selected media
     */
    onSelectMedia: function(dataViewModel, media, previewButton, removeButton) {
        this.disableImageButtons(dataViewModel, previewButton, removeButton);
    },

    /**
     * Event will be fired when the user de select an article image in the listing.
     *
     * @param [Ext.selection.DataViewModel] The selection data view model of the Ext.view.View
     * @param [Shopware.apps.Article.model.Media] The selected media
     */
    onDeselectMedia: function(dataViewModel, media, previewButton, removeButton) {
        this.disableImageButtons(dataViewModel, previewButton, removeButton);
    },

    /**
     * MNarks an image as an blog preview image
     *
     * @return bool
     */
    onMarkPreviewImage: function() {
        var me = this,
            mediaList = me.getMediaList(),
            store = mediaList.getStore(),
            selected = mediaList.getSelectionModel().selected.first();

        if (!(selected instanceof Ext.data.Model)) {
            return false;
        }

        store.each(function(item) {
            item.set('preview', false);
        });

        selected.set('preview', true);
    },

    /**
     * Removes the selected image.
     *
     * @return bool
     */
    onRemoveImage: function() {
        var me = this,
            mediaList = me.getMediaList(),
            store = mediaList.getStore(),
            changeMain,
            selected = mediaList.getSelectionModel().selected.first();

        if (!(selected instanceof Ext.data.Model)) {
            return false;
        }
        changeMain = (selected.get('preview')===true);

        store.remove(selected);
        if (!changeMain) {
            return true;
        }

        var next = store.getAt(0);
        if (next instanceof Ext.data.Model) {
            next.set('preview', true);
        }
    },

    /**
     * Helper function to disable/enable the toolbar buttons of the image list.
     * @param dataViewModel
     * @param previewButton
     * @param removeButton
     */
    disableImageButtons: function(dataViewModel, previewButton, removeButton) {
        var me = this,
            disabled = (dataViewModel.selected.length === 0);

        removeButton.setDisabled(disabled);
        previewButton.setDisabled(disabled);
        if (!disabled) {
            var selected = dataViewModel.selected.first();
            previewButton.setDisabled(selected.get('preview')===1);
        }
    }

});
//{/block}
