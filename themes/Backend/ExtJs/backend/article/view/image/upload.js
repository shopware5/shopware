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
 * @package    Article
 * @subpackage Detail
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Article detail page - Image
 * The upload component contains a media drop zone, an upload button for images and a media selection.
 * The user can select the article images over the media manager, can use the drag and drop system or can select
 * the images over the upload button. All events of the component handled in the media controller.
 */
//{namespace name=backend/article/view/main}
//{block name="backend/article/view/image/upload"}
Ext.define('Shopware.apps.Article.view.image.Upload', {
    /**
     * Define that the category drop zone is an extension of the Ext.panel.Panel
     * @string
     */
    extend:'Ext.panel.Panel',
    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.article-image-upload',
    /**
     * Set css class for this component
     * @string
     */
    cls: Ext.baseCSSPrefix + 'article-image-upload',

    /**
     * Layout for the component
     */
    layout: 'anchor',

    /**
     * Defaults for the panel items
     * @object
     */
    defaults: {
        anchor: '100%'
    },

    /**
     * Contains all snippets for the view component
     * @object
     */
    snippets:{
        title: '{s name=image/upload/title}Upload images{/s}',
        infoHeader: '{s name=image/upload/info_header}Please assign the requested images to the article <strong>[0]</strong>.{/s}',
        infoText: '{s name=image/upload/info_text}You have several opportunities to select new or existing images. You can, as usual, use the normal uploader, you can utilize the dropbox that appears below to upload your images or you can select the images directly from the media manager.{/s}',
        upload:  '{s name=image/upload/upload}Upload images{/s}',
        mediaSelection: '{s name=image/upload/select}Select images{/s}'
    },

    bodyPadding: 10,

    /**
	 * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
	 *
	 * @return void
	 */
    initComponent:function () {
        var me = this;
        me.title = me.snippets.title;
        me.infoText = me.createInfoText();
        me.uploadFields = me.createUploadFields();
        me.dropZone = me.createDropZone();
        me.items = [
            me.infoText,
            me.uploadFields,
            me.dropZone
        ];
        me.registerEvents();
        me.callParent(arguments);
    },

    /**
     * Registers additional component events.
     */
    registerEvents: function() {
    	this.addEvents(
    		/**
    		 * Event will be fired when the user want to upload images over the button.
    		 *
    		 * @event
    		 * @param [object]
    		 */
    		'mediaUpload'
    	);
    },

    /**
     * Creates the info text which is displayed on top of the drop zone.
     * @return Ext.container.Container
     */
    createInfoText: function() {
        var me = this, title = me.snippets.infoHeader;

        if (me.article instanceof Ext.data.Model) {
            title = Ext.String.format(title, me.article.get('name'));
        }
        return Ext.create('Ext.container.Container', {
            html: title + '<br><br>' + me.snippets.infoText,
            style: 'color: #999; font-style: italic;'
        });
    },

    /**
     * Creates the container with the both upload fields.
     * @return Ext.container.Container
     */
    createUploadFields: function() {
        var me = this;

        me.fileUploadField = Ext.create('Ext.form.field.File', {
            buttonOnly: false,
            labelWidth: 100,
            anchor: '100%',
            margin: '0 10 0 0',
            name: 'fileId',
            buttonText : me.snippets.upload,
            listeners: {
                scope: this,
                afterrender: function(btn) {
                    btn.fileInputEl.dom.multiple = true;
                },
                change: function(field) {
                    me.fireEvent('mediaUpload', field)
                }
            },
            buttonConfig : {
                iconCls: 'sprite-inbox-upload',
                cls: 'small secondary'
            }
        });

        if(Ext.isIE || Ext.isSafari) {
            var form = Ext.create('Ext.form.Panel', {
                unstyled: true,
                border: 0,
                bodyBorder: 0,
                style: 'background: transparent',
                bodyStyle: 'background: transparent',
                url: '{url controller="mediaManager" action="upload"}?albumID=-1',
                items: [ me.fileUploadField ]
            });
            me.fileUploadField  = form;
        }

        // Media selection field
        me.mediaSelection = Ext.create('Shopware.MediaManager.MediaSelection', {
            fieldLabel: me.snippets.mediaSelection,
            name: 'media-manager-selection',
            multiSelect: true,
            anchor: '100%',
            buttonText: me.snippets.mediaSelection,
            buttonConfig : {
                width:150
            },
            albumId: -1,
            allowBlank: false,
            validTypes: me.getAllowedExtensions(),
            validTypeErrorFunction: me.getExtensionErrorCallback()
        });

        return Ext.create('Ext.container.Container', {
            layout: 'column',
            margin: '20 0',
            defaults: {
                columnWidth: 0.5
            },
            items: [
                me.fileUploadField,
                me.mediaSelection
            ]
        });
    },

    /**
     * Method to set the allowed file extension for the media manager
     * @return Array of strings
     */
    getAllowedExtensions : function() {
        return [ 'gif', 'png', 'jpeg', 'jpg', 'swf' ]
    },
    /**
     * Returns the method which should be called if some select a file with a wrong extension.
     *
     * @return string
     */
    getExtensionErrorCallback :  function() {
        return 'onExtensionError';
    },

    /**
     * Creates the drop zone container
     * @return Ext.container.Container
     */
    createDropZone: function() {
        var me = this;

        return Ext.create('Shopware.apps.Article.view.image.DropZone', {
            anchor: '100%',
            dropZoneConfig: { hideOnLegacy: true, focusable: false }
        });
    }

});
//{/block}




