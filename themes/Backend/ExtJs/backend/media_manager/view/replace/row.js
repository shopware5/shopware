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
//{block name="backend/media_manager/view/replace/row"}
Ext.define('Shopware.apps.MediaManager.view.replace.Row', {
    extend: 'Ext.container.Container',
    layout: 'hbox',
    margin: '0 0 10 0',
    style: {
        width: '100%',
        borderBottom: '1px solid #c4c4c4'
    },

    noPictureImageSrc: '{url module=frontend}' + '/themes/Backend/ExtJs/backend/_resources/images/index/no-picture.jpg',

    /**
     * init's all required components
     */
    initComponent: function() {
        var me = this;

        me.items = me.createRow();
        me.registerEvents();

        me.callParent(arguments);
    },

    /**
     * registers events
     */
    registerEvents: function() {
        var me = this;

        me.fileUpload.on('upload-changed', me.onFileSelected, me);
        me.fileUpload.on('upload-uploadReady', me.onUploadReady, me);
        me.fileUpload.on('upload-noValue', me.onUploadReady, me);
        me.fileUpload.on('upload-maximumReached', me.onMaximumReached, me);
        me.fileUpload.on('upload-error', me.onUploadError, me);
    },

    onFileSelected: function(dropZone, component, event, files) {
        var me = this,
            file = files[0];

        me.showPreview(dropZone, file);
    },

    /**
     * shows the preview of the given file in the previewContainer
     *
     * @param { Shopware.apps.MediaManager.view.replace.Upload } upload
     * @param { File } file
     */
    showPreview: function(upload, file) {
        var me = this,
            reader = new FileReader(),
            previewContainer;

        upload.setLoading(true);

        reader.onload = function(e) {
            var data,
                type = me.getType(file);

            if (!file.type.match(/^image/)) {
                data = { thumbnail: me.noPictureImageSrc, name: file.name, isRaw: true, type: type };
            } else {
                data = { thumbnail: e.target.result, name: file.name, isRaw: true, type: type };
            }

            previewContainer = me.getMediaTemplate(data);
            me.previewContainer.removeAll();
            me.previewContainer.add(previewContainer);

            /**
             * scroll to top of this container because after add the new preview container the view
             * scrolls to top of the more closely container.
             */
            me.grid.scrollBy([0, me.getEl().dom.offsetTop], false);

            upload.setLoading(false);
        };

        reader.readAsDataURL(file);
    },

    /**
     * Gets the type of the file and return the them for later use in template
     *
     * @param { File } file
     * @return { string }
     */
    getType: function(file) {
        var type = file.type.split('/')[0].toUpperCase();

        switch (type) {
            case 'AUDIO':
                type = 'MUSIC';
                break;
            case 'APPLICATION':
            case 'TEXT':
            case '':
                type = 'UNKNOWN';
                break;
        }

        return type;
    },

    /**
     * upload ready event handler
     *
     * @param { Shopware.apps.MediaManager.view.replace.Upload } upload
     */
    onUploadReady: function(upload) {
        var me = this;

        me.fireEvent('upload-uploadReady', me, upload);
    },

    /**
     * on maximum files reached show a growlMessage
     */
    onMaximumReached: function() {
        var me = this;

        Shopware.Notification.createGrowlMessage(
            '{s name="mediaManager/replaceWindow/window/errorTitle"}{/s}',
            '{s name="mediaManager/replaceWindiw/dropZone/maximumFiles"}{/s}'
        );

        me.onUploadError();
    },

    /**
     * on upload error fire the error event
     */
    onUploadError: function() {
        var me = this;

        me.fireEvent('upload-error', me);
    },

    /**
     * returns the current value object if exists else the boolean "false"
     *
     * @return { object|boolean }
     */
    getValue: function() {
        var me = this;

        if (me.hasOwnProperty('replaceData')) {
            return me.replaceData;
        }

        return false;
    },

    /**
     * creates all components for replace a media
     *
     * @return { Array }
     */
    createRow: function() {
        var me = this;

        return [
            me.getMediaTemplate(me.record.raw),
            me.getReplaceIcon(),
            me.getPreviewContainer(),
            me.createMediaDropZone(me.record.get('id'))
        ]
    },

    /**
     * Creates and return the media template
     *
     * @param { object } data
     * @return { Ext.container.Container }
     */
    getMediaTemplate: function(data) {
        var me = this;

        if (data && !data.isRaw) {
            data.thumbnail += '?' + new Date().getTime();
        }

        return Ext.create('Ext.container.Container', {
            data: data,
            width: 100,
            tpl: me.createMediaViewTemplate()
        });
    },

    /**
     * creates and return the replace icon
     *
     * @return { Ext.container.Container }
     */
    getReplaceIcon: function() {
        var template = new Ext.XTemplate(
            '<div>',
            '   <div class="sprite-arrow-180" style="width: 16px; height: 16px;">&nbsp;</div>',
            '   <div class="sprite-arrow" style="width: 16px; height: 16px;">&nbsp;</div>',
            '</div>'
        );

        return Ext.create('Ext.container.Container', {
            tpl: template,
            data: {},
            margin: '30 10 10 10',
            width: 16,
            height: 32
        });
    },

    /**
     * creates and reutrn the empty media template
     *
     * @return { Ext.container.Container }
     */
    getEmptyMediaTemplate: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            tpl: me.createMediaViewTemplate(),
            data: { thumbnail: '', name: '', isRaw: true, type: 'IMAGE' }
        });
    },

    /**
     * creates a Container for a late handling like clear all and add new content
     *
     * @return { Ext.container.Container }
     */
    getPreviewContainer: function() {
        var me = this;

        me.previewContainer = Ext.create('Ext.container.Container', {
            width: 100,
            items: [
                me.getEmptyMediaTemplate()
            ]
        });

        return me.previewContainer;
    },

    /**
     * creates and return the media upload component with drag and drop
     * requires the media id to replace the media
     *
     * @param { string|number } mediaId
     * @return { Ext.container.Container }
     */
    createMediaDropZone: function(mediaId) {
        var me = this;

        me.fileUpload = Ext.create('Shopware.apps.MediaManager.view.replace.Upload', {
            mediaId: mediaId
        });

        return Ext.create('Ext.container.Container', {
            width: 345,
            items: [
                me.fileUpload
            ]
        });
    },

    /**
     * creates the template for the media view panel
     *
     * @return { Ext.XTemplate }
     */
    createMediaViewTemplate: function() {
        return new Ext.XTemplate(
            '{literal}',
            '<tpl for=".">',
            '   <div class="thumb-wrap" id="{name}">',
            '      <tpl if="type == &quot;IMAGE&quot;">',
            '         <div class="thumb">',
            '             <div class="inner-thumb" style="overflow: hidden"><img src="{thumbnail}" title="{name}" /></div>',
            '         </div>',
            '      </tpl>',
            '      <tpl if="type != &quot;IMAGE&quot;">',
            '         <div class="thumb icon">',
            '             <div class="icon-{[values.type.toLowerCase()]}">&nbsp;</div>',
            '         </div>',
            '      </tpl>',
            '   </div>',
            '   <div class="x-clear"></div>',
            '</tpl>',
            '{/literal}'
        );
    },

    /**
     * starts the upload of the selected files
     */
    startUpload: function() {
        var me = this;

        me.fileUpload.startUpload();
    }
});
//{/block}