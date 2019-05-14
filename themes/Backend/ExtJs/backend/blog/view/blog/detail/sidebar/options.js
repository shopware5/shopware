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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */
//{namespace name=backend/blog/view/blog}
/**
 * Shopware UI - Blog detail sidebar options window.
 *
 * Displays all Detail Blog Information
 */
//{block name="backend/blog/view/blog/detail/sidebar/options"}
Ext.define('Shopware.apps.Blog.view.blog.detail.sidebar.Options', {
    extend:'Ext.panel.Panel',
    alias:'widget.blog-blog-detail-sidebar-options',
    border: 0,
    bodyPadding: 10,
    autoScroll: true,
    collapsed: false,
    title: '{s name=detail/sidebar/options/title}Additional options{/s}',
    layout: {
        type: 'border'
    },

    /**
     * Initialize the Shopware.apps.Blog.view.blog.detail.sidebar.options and defines the necessary
     * default configuration
     */
    initComponent:function () {
        var me = this;

        me.registerEvents();
        me.propertiesPanel = Ext.create('Ext.panel.Panel', {
            title:'{s name=detail/sidebar/options/panel/properties}Blog article properties{/s}',
            margin: '0 0 10 0',
            layout: {
                type: 'anchor'
            },
            bodyPadding: 10,
            region:'north',
            closable: false,
            scrollable: true,
            split: true,
            autoScroll: true,
            collapsible: false,
            defaults:{
                labelWidth: 100,
                anchor: '100%',
                labelStyle: 'font-weight: 700;',
                xtype: 'textfield'
            },
            items: me.createOptionsForm()
        });

        me.imagePanel = Ext.create('Ext.panel.Panel', {
            title: '{s name=detail/sidebar/options/panel/image}Image configuration{/s}',
            layout: {
                align: 'stretch',
                type: 'vbox'
            },
            region: 'center',
            split: true,
            height: 200,
            minHeight: 200,
            tbar: me.createToolbar(),
            bodyPadding: 10,
            items: me.createImageElements()
        });

        me.items = [ me.propertiesPanel, me.imagePanel ];

        me.callParent(arguments);
    },

    /**
     * Defines additional events which will be
     * fired from the component
     *
     * @return bool
     */
    registerEvents:function () {
        this.addEvents(
            /**
             * Event will be fired when the user select an image in the listing.
             *
             * @event
             * @param [Ext.selection.DataViewModel] The selection data view model of the Ext.view.View
             * @param [Shopware.apps.Article.model.Media] The selected media
             */
            'mediaSelect',

            /**
             * Event will be fired when the user de select an image in the listing.
             *
             * @event
             * @param [Ext.selection.DataViewModel] The selection data view model of the Ext.view.View
             * @param [Shopware.apps.Article.model.Media] The selected media
             */
            'mediaDeselect',

            /**
             * Event will be fired when the user clicks the "preview image".
             *
             * @event
             */
            'markPreviewImage',

            /**
             * Event will be fired when the user clicks the "remove selected image".
             *
             * @event
             */
            'removeImage'
        );

        return true;
    },


    /**
     * creates the general form and layout
     *
     * @return [Array] computed form
     */
    createOptionsForm:function () {
        var me = this;
        return [
            {
                xtype: 'combo',
                name: 'template',
                queryMode: 'remote',
                fieldLabel: '{s name=detail/sidebar/options/field/template}Template{/s}',
                store: me.templateStore.load(),
                valueField: 'id',
                submitValue : true,
                emptyText: '{s name=detail/sidebar/options/field/template/empty_text}Standard{/s}',
                displayField: 'name'
            },
            {
                xtype:'datefield',
                fieldLabel:'{s name=detail/sidebar/options/field/displayDate}Display date{/s}',
                allowBlank:false,
                submitFormat: 'd.m.Y',
                required:true,
                name:'displayDate'
            },
            {
                xtype:'timefield',
                fieldLabel:'{s name=detail/sidebar/options/field/displayTime}Display time{/s}',
                allowBlank:false,
                submitFormat: 'H:i',
                required:true,
                name:'displayTime'
            },
            {
                xtype:'combobox',
                name:'categoryId',
                fieldLabel:'{s name=detail/sidebar/options/field/category}Category{/s}',
                store: me.categoryPathStore.load(),
                valueField:'id',
                editable:false,
                allowBlank:false,
                forceSelection:true,
                required:true,
                displayField:'name'
            },
            {
                xtype:'boxselect',
                name:'tags',
                fieldLabel:'{s name=detail/sidebar/options/field/tags}Tags{/s}',
                store:[],
                queryMode:'local',
                forceSelection: false,
                createNewOnEnter: true,
                createNewOnBlur: true,
                displayField:'name',
                valueField: 'id'
            }
        ]
    },

    /**
     * Creates the panel for the blog image upload. To upload the blog images, the container contains
     * a drag and drop zone and a file upload field.
     *
     * @return Ext.form.FieldSet
     */
    createImageElements: function() {
        var me = this;
        me.mediaStore = me.detailRecord.getMedia();
        // Media selection field
        me.mediaSelection = Ext.create('Shopware.MediaManager.MediaSelection', {
            buttonText: '{s name=detail/sidebar/options/button/select_image}Select images{/s}',
            name: 'media-manager-selection',
            multiSelect: true,
            flex:1,
            buttonConfig : {
                width:100
            },
            validTypes: me.getAllowedExtensions()
        });

        return [
            me.mediaSelection,
            me.createMediaView()
        ];
    },

    /**
     * Method to set the allowed file extension for the media manager
     * @return []
     */
    getAllowedExtensions : function() {
        return [ 'gif', 'png', 'jpeg', 'jpg', 'svg' ]
    },

    /**
     * Creates the template for the media view panel
     *
     * @return [object] generated Ext.XTemplate
     */
    createMediaViewTemplate: function() {
        var me = this;
        return new Ext.XTemplate(
                '{literal}<tpl for=".">',
                    '<div class="article-thumb-wrap small">',
                        '<div class="thumb">',
                            '<div class="inner-thumb"><img src="{path}" style="height: 70px; width: 90px;" />' +
                                '<tpl if="preview===true">',
                                    '<div class="preview"><span>{/literal}{s name=detail/sidebar/options/preview}Preview{/s}{literal}</span></div>' +
                                '</tpl>',
                            '</div>',
                        '</div>',
                    '</div>',
                '</tpl>',
                '<div class="x-clear"></div>{/literal}'
        );
    },

    /**
     * Creates the media listing based on an Ext.view.View (know as DataView)
     * and binds the "Media"-store to it
     *
     * @return [object] this.dataView - created Ext.view.View
     */
    createMediaView: function() {
        var me = this;

        me.dataView = Ext.create('Ext.view.View', {
            itemSelector: '.article-thumb-wrap',
            name: 'image-listing',
            emptyText: 'No Media found',
            multiSelect: true,
            autoScroll:true,
            flex:5,
            store: me.mediaStore,
            tpl: me.createMediaViewTemplate()
        });

        me.dataView.getSelectionModel().on('select', function (dataViewModel, media) {
            me.fireEvent('mediaSelect', dataViewModel, media, me.previewButton, me.removeButton);
        });
        me.dataView.getSelectionModel().on('deselect', function (dataViewModel, media) {
            me.fireEvent('mediaDeselect', dataViewModel, media, me.previewButton, me.removeButton);
        });

        return me.dataView;
    },

    /**
     * Creates the toolbar for the media listing.
     *
     * @return [object] created Ext.toolbar.Toolbar
     */
    createToolbar: function() {
        var me = this;

        // The preview button, marks the selected image in the listing as preview.
        // The event will be handled in the media controller
        me.previewButton = Ext.create('Ext.button.Button', {
            text: '{s name=detail/sidebar/options/button/preview_image}Mark as preview{/s}',
            action: 'previewImage',
            disabled: true,
            iconCls: 'sprite-camera-lens',
            handler: function() {
                me.fireEvent('markPreviewImage');
            }
        });

        // The remove button, removes the selected item from the image listing.
        me.removeButton = Ext.create('Ext.button.Button', {
            text:'{s name=detail/sidebar/options/button/delete_image}Remove selected images{/s}',
            action: 'removeImage',
            disabled: true,
            iconCls:'sprite-minus-circle-frame',
            handler: function() {
                me.fireEvent('removeImage');
            }
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            items: [
                me.previewButton,
                { xtype:'tbspacer', width: 12 },
                me.removeButton
            ]
        });
    }
});
//{/block}
