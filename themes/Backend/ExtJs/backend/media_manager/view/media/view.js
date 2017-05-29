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
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/media_manager/view/main}

/**
 * Shopware UI - Media Manager Media View
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/media_manager/view/media/view"}
Ext.define('Shopware.apps.MediaManager.view.media.View', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.mediamanager-media-view',
    style: 'background: #fff',
    border: false,
    bodyBorder: false,
    layout: 'border',
    region: 'center',
    createInfoPanel: true,
    createDeleteButton: true,
    createMediaQuantitySelection: true,
    deleteBtn: null,
    selectedLayout: 'grid',

    snippets: {
        noMediaFound: '{s name=noMediaFound}No Media found{/s}',
        uploadDataDragDrop: '{s name=uploadDataDragDrop}Upload your Data via <strong>Drag & Drop</strong> here{/s}',
        noAdditionalInfo: '{s name=noAdditionalInfo}No additional informations found{/s}',
        moreInfoTitle:'{s name=moreInfoTitle}More information{/s}',
        mediaInfo: {
            name: '{s name=mediaInfo/name}Name:{/s}',
            uploadedon: '{s name=mediaInfo/uploadedOn}Uploaded on:{/s}',
            type: '{s name=mediaInfo/type}Type:{/s}',
            resolution: '{s name=mediaInfo/resolution}Resolution:{/s}',
            adress: '{s name=mediaInfo/adress}Adress:{/s}',
            mediaLink: '{s name=mediaInfo/mediaLink}Link to media{/s}'
        },
        formatTypes: {
            video: '{s name=formatTypes/video}-video{/s}',
            music: '{s name=formatTypes/music}-music{/s}',
            archive: '{s name=formatTypes/archive}-archive{/s}',
            pdf: '{s name=formatTypes/pdf}PDF-document{/s}',
            graphic: '{s name=formatTypes/graphic}-graphic{/s}',
            vector: '{s name=formatTypes/vector}-vector{/s}',
            unknown: '{s name=formatTypes/unknown}unknown file{/s}'
        },
        fieldsText:{
            searchField: '{s name=fieldsText/searchField}Search media...{/s}',
            deleteButton: '{s name=fieldsText/deleteButton}delete marked file(s){/s}',
            addButton: '{s name=fieldsText/addButton}add more files{/s}',
            itemsPerSite: '{s name=fieldsText/itemsPerSite}items per site{/s}',
            itemCount: '{s name=fieldsText/itemCount}items{/s}'
        }
    },

    /**
     * Initializes the component and sets the necessary
     * toolbars and items.
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        // Create toolbars
        me.tbar = me.createActionToolbar();
        me.bbar = me.createPagingToolbar();

        // Are we're having file extensions which should filter the store?
        if(me.validTypes) {
            var proxy = me.mediaStore.getProxy();
            proxy.extraParams.validTypes = me.setValidTypes();
        }

        me.cardContainer = Ext.create('Ext.panel.Panel', {
            layout: 'card',
            activeItem: 0,
            region: 'center',
            unstyled: true,
            style: 'background: #fff',
            items: [{
                xtype: 'container',
                style: 'overflow-y: scroll',
                items: [
                /* {if {acl_is_allowed privilege=upload}} */
                    me.createDropZone(),
                /* {/if} */
                    me.createMediaView()
                ]
            }, {
                xtype: 'mediamanager-media-grid',
                mediaStore: me.mediaStore
            }]
        });

        // Create the items of the container
        me.items = [ me.cardContainer ];

        if(me.createInfoPanel) {
            var infoPnl = me.createInfoPanel();
            me.items.push(infoPnl);
        }

        // Add additonal events
        me.addEvents('editLabel', 'changePreviewSize');
        me.callParent(arguments);
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
            types = me.validTypes,
            filters = '';

        Ext.each(types, function(typ) {
            filters += typ + '|';
        });
        filters = filters.substr(0, filters.length-1);

        return filters;
    },

    /**
     * Creates the template for the media view panel
     *
     * @return { object } generated Ext.XTemplate
     */
    createMediaViewTemplate: function() {
        var me = this;

        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="thumb-wrap" id="{name}">',

                // If the type is image, then show the image
                '<tpl if="this.isImage(type, extension)">',
                '<div class="thumb">',
                    '<div class="inner-thumb"><img src="{thumbnail}" title="{name}" /></div>',
                '</div>',
                '</tpl>',

                // All other types should render an icon
                '<tpl if="!this.isImage(type, extension)">',
                    '<div class="thumb icon">',
                        '<div class="icon-{[values.type.toLowerCase()]}">&nbsp;</div>',
                    '</div>',
                '</tpl>',
                '<span class="x-editable">{[Ext.util.Format.ellipsis(values.name, 9)]}.{extension}</span></div>',
            '</tpl>',
            '<div class="x-clear"></div>{/literal}',
            {
                /**
                 * Member function of the template to check if a certain file is an image.
                 *
                 * @param { string }type
                 * @param { string } extension
                 * @returns { boolean }
                 */
                isImage: function(type, extension) {
                    return me._isImage(type, extension);
                }
            }
        )
    },

    /**
     * Creates the media listing based on an Ext.view.View (known as DataView)
     * and binds the "Media"-store to it
     *
     * @return { object } this.dataView - created Ext.view.View
     */
    createMediaView: function() {
        var me = this;

        var multiSelect = true;
        if(Ext.isBoolean(me.selectionMode)) {
            multiSelect = me.selectionMode;
        }

        me.dataView = Ext.create('Ext.view.View', {
            itemSelector: '.thumb-wrap',
            emptyText: '<div class="empty-text"><span>'+me.snippets.noMediaFound+'</span></div>',
            multiSelect: multiSelect,
            store: me.mediaStore,
            tpl: me.createMediaViewTemplate(),
            listeners: {
                scope: me,
                render: me.initializeMediaDragZone
            },
            plugins: [
                Ext.create('Ext.ux.DataView.DragSelector'),
                Ext.create('Ext.ux.DataView.LabelEditor', {
                    dataIndex: 'name',
                    listeners: {
                        scope: me.dataView,
                        complete: function(editor, value) {
                            me.fireEvent('editLabel', this, editor, value);
                        }
                    }
                })
            ]
        });

        // Set event listeners for the selection model to lock/unlock the delete button
        me.dataView.getSelectionModel().on({
            'select': {
                fn: me.onSelectMedia,
                scope: me
            },
            'deselect': {
                fn: me.onLockDeleteButton,
                scope: me
            }
        });

        return me.dataView;
    },

    /**
     * Creates a new upload drop zone which uploads the dropped files
     * to the server and adds them to the active album
     *
     * @return { object } this.mediaDropZone - created Shopware.app.FileUpload
     */
    createDropZone: function() {
        var me = this;

        me.mediaDropZone = Ext.create('Shopware.app.FileUpload', {
            requestURL: '{url controller="mediaManager" action="upload"}',
            hideOnLegacy: true,
            showInput: false,
            checkType: false,
            checkAmount: false,
            enablePreviewImage: false,
            dropZoneText: me.snippets.uploadDataDragDrop,
            height: 100
        });

        return this.mediaDropZone;
    },

    /**
     * Creates the XTemplate for the information panel
     *
     * Note that the template has different member methods
     * which are only callable in the actual template.
     *
     * @return { object } generated Ext.XTemplate
     */
    createInfoPanelTemplate: function() {
        var me = this;
        return new Ext.XTemplate(
            '{literal}<tpl for=".">',
                '<div class="media-info-pnl">',

                    // If the type is image, then show the image
                    '<tpl if="this.isImage(type, extension)">',
                        '<div class="thumb">',
                            '<div class="inner-thumb"><img src="{thumbnail}" title="{name}" /></div>',
                        '</div>',
                    '</tpl>',

                    // All other types should render an icon
                    '<tpl if="!this.isImage(type, extension)">',
                        '<div class="thumb icon">',
                            '<div class="icon-{[values.type.toLowerCase()]}">&nbsp;</div>',
                        '</div>',
                    '</tpl>',
                    '<div class="base-info">',
                        '<p>',
                            '<strong>Download:</strong>',
                            '<a class="link" target="_blank" href="{/literal}{url controller=MediaManager action=download}{literal}?mediaId={id}" title="{name}">{name}</a>',
                        '</p>',
                        '<p>',
                            '<strong>'+me.snippets.mediaInfo.name+'</strong>',
                            '<input type="text" disabled="disabled" value="{name}" />',
                        '</p>',
                        '<p>',
                            '<strong>'+me.snippets.mediaInfo.uploadedon+'</strong>',
                            '<span>{[this.formatDate(values.created)]}</span>',
                        '</p>',
                        '<p>',
                            '<strong>'+me.snippets.mediaInfo.type+'</strong>',
                            '<span>{[this.formatDataType(values.type, values.extension)]}</span>',
                        '</p>',
                        '<tpl if="width">',
                            '<p>',
                                '<strong>'+me.snippets.mediaInfo.resolution+'</strong>',
                                '<span>{width} x {height} Pixel</span>',
                            '</p>',
                        '</tpl>',

                        '<tpl if="this.allowInBrowserRendering(extension)">',
                            '<p>',
                                '<strong>'+me.snippets.mediaInfo.adress+'</strong>',
                                '<a class="link" target="_blank" href="{path}" title="{name}">'+ me.snippets.mediaInfo.mediaLink +'</a>',
                            '</p>',
                        '</tpl>',
                    '</div>',
                '</div>',
            '</tpl>{/literal}',
            {
                /**
                 * Checks if this file type is allowed to be rendered inside of the browser
                 *
                 * @param { string } extension
                 * @returns { boolean }
                 */
                allowInBrowserRendering: function(extension) {
                    return !Ext.Array.contains(['svg'], extension.toLowerCase());
                },

                /**
                 * Member function of the template to check if a certain file is an image
                 *
                 * @param { string } type
                 * @param { string } extension
                 * @returns { boolean }
                 */
                isImage: function(type, extension) {
                    return me._isImage(type, extension);
                },

                /**
                 * Member function of the template which formats a date string
                 *
                 * @param { string } value - Date string in the following format: Y-m-d H:i:s
                 * @return { string } formatted date string
                 */
                formatDate: function(value) {
                    return Ext.util.Format.date(value);
                },

                /**
                 * Formats the output type
                 *
                 * @param { string } type - Type of the media
                 * @param { string } extension - File extension of the media
                 */
                formatDataType: function(type, extension) {
                    var result = '';

                    extension = extension.toUpperCase();
                    switch(type) {
                        case 'VIDEO':
                            result = extension + me.snippets.formatTypes.video;
                            break;
                        case 'MUSIC':
                            result = extension + me.snippets.formatTypes.music;
                            break;
                        case 'ARCHIVE':
                            result = extension + me.snippets.formatTypes.archive;
                            break;
                        case 'PDF':
                            result = me.snippets.formatTypes.pdf;
                            break;
                        case 'IMAGE':
                            result = extension + me.snippets.formatTypes.graphic;
                            break;
                        case 'VECTOR':
                            result = extension + me.snippets.formatTypes.vector;
                            break;
                        default:
                            result = me.snippets.formatTypes.unknown;
                            break;
                    }
                    return result;
                }
            }
        )
    },

    /**
     * Creates a new panel which displays additional information
     * about the selected media.
     *
     * @return { object } this.infoPanel - generated Ext.panel.Panel
     */
    createInfoPanel: function() {
        var me = this;

        me.attributeButton = Ext.create('Shopware.attribute.Button', {
            table: 's_media_attributes',
            width: 185,
            margin: '0 0 0 10'
        });

        me.infoView = Ext.create('Ext.view.View', {
            cls: 'outer-media-info-pnl',
            border: 0,
            bodyBorder: false,
            emptyText: me.snippets.noAdditionalInfo,
            tpl: me.createInfoPanelTemplate(),
            itemSelector: '.copy-image-path',
            width: 190,
            renderData: []
        });

        me.infoPanel = Ext.create('Ext.panel.Panel', {
            title: me.snippets.moreInfoTitle,
            layout: 'auto',
            cls: Ext.baseCSSPrefix + 'more-info',
            style: 'background: #fff',
            collapsible: true,
            autoScroll:true,
            region: 'east',
            width: 210,
            items: [ me.infoView, me.attributeButton ]
        });

        return me.infoPanel;
    },

    /**
     * Creates the action toolbar for the media view. The toolbar
     * contains 2 buttons ("add item" and "delete marked items")
     * and a search field to filter the media view.
     *
     * @return { object } created Ext.toolbar.Toolbar
     */
    createActionToolbar: function() {
        var me = this;
        /* {if {acl_is_allowed privilege=create}} */
        if(Ext.isIE) {
            me.addBtn = Ext.create('Shopware.app.FileUpload', {
                requestURL: '{url controller="mediaManager" action="upload"}',
                padding: '6 0 0',
                fileInputConfig: {
                    buttonOnly: true,
                    width: 190,
                    buttonText : me.snippets.fieldsText.addButton,
                    buttonConfig : {
                        iconCls:'sprite-plus-circle'
                    }
                }
            });
        } else {
            me.addBtn = Ext.create('Ext.form.field.File', {
                buttonOnly: true,
                width: 190,
                buttonText : me.snippets.fieldsText.addButton,
                listeners: {
                    scope: this,

                    /**
                     * Enable multi selection on the file upload button
                     *
                     * @param { object } btn - rendered Ext.button.Button
                     * @return void
                     */
                    afterrender: function(btn) {
                        btn.fileInputEl.dom.multiple = true;
                    }
                },
                buttonConfig : {
                    iconCls:'sprite-plus-circle'
                }
            });
        }
        /* {/if} */

        var searchField = Ext.create('Ext.form.field.Text', {
            emptyText: me.snippets.fieldsText.searchField,
            cls: 'searchfield',
            width: 175,
            enableKeyEvents: true,
            checkChangeBuffer: 500,
            action: 'mediamanager-media-view-search'
        });

        var toolbar = Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            items: [
        /* {if {acl_is_allowed privilege=create}} */
                this.addBtn
        /* {/if} */
            ]
        });

        /* {if {acl_is_allowed privilege=delete}} */
        if(this.createDeleteButton) {
            this.deleteBtn = Ext.create('Ext.button.Button', {
                text: me.snippets.fieldsText.deleteButton,
                iconCls:'sprite-minus-circle-frame',
                action: 'mediamanager-media-view-delete',
                disabled: true
            });

            toolbar.add(
                this.deleteBtn
            );
        }
        /* {/if} */

        toolbar.add({
            showText: true,
            xtype: 'cycle',
            prependText: '{s name=toolbar/view}Display as{/s} ',
            action: 'mediamanager-media-view-layout',
            menu: {
                items: [{
                    text: '{s name=toolbar/view_chart}Grid{/s}',
                    layout: 'table',
                    iconCls: 'sprite-application-icon-large'
                },{
                    text: '{s name=toolbar/view_table}Table{/s}',
                    layout: 'grid',
                    checked: true,
                    iconCls: 'sprite-application-table'
                }]
            }
        });

        toolbar.add(
            '->',
            searchField,
            { xtype: 'tbspacer', width: 6 }
        );

        return toolbar;
    },

    /**
     * Creates the paging toolbar for the media view.
     *
     * @return { object } generated Ext.toolbar.Toolbar
     */
    createPagingToolbar: function() {
        var me = this;

        var pageSize = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: me.snippets.fieldsText.itemsPerSite,
            labelWidth: 110,
            cls: Ext.baseCSSPrefix + 'page-size',
            queryMode: 'local',
            width: 210,
            listeners: {
                scope: me,
                select: me.onChangeMediaQuantity
            },
            store: Ext.create('Ext.data.Store', {
                fields: [ 'value', 'name' ],
                data: [
                    { value: '20', name: '20 '+me.snippets.fieldsText.itemCount },
                    { value: '40', name: '40 '+me.snippets.fieldsText.itemCount },
                    { value: '60', name: '60 '+me.snippets.fieldsText.itemCount },
                    { value: '80', name: '80 '+me.snippets.fieldsText.itemCount },
                    { value: '100', name: '100 '+me.snippets.fieldsText.itemCount },
                    { value: '150', name: '150 '+me.snippets.fieldsText.itemCount },
                    { value: '200', name: '200 '+me.snippets.fieldsText.itemCount },
                    { value: '250', name: '250 '+me.snippets.fieldsText.itemCount }
                ]
            }),
            displayField: 'name',
            valueField: 'value'
        });
        pageSize.setValue(me.mediaStore.pageSize + '');

        var toolbar = Ext.create('Ext.toolbar.Paging', {
            store: me.mediaStore
        });

        if(me.createMediaQuantitySelection) {
            toolbar.add('->', pageSize, { xtype: 'tbspacer', width: 6 });
        }

        // Create the data for the preview image size
        var imageSizeData = [], i = 1;
        for( ; i < 9; i++) {
            var size = 16 * i;
            imageSizeData.push({ value: size, name: size + 'x' + size + 'px' });
        }

        // Preview image size selection, especially for the list view
        me.imageSize = Ext.create('Ext.form.field.ComboBox', {
            fieldLabel: 'Preview-Größe',
            queryMode: 'local',
            labelWidth: 90,
            width: 190,
            hidden: true,
            displayField: 'name',
            valueField: 'value',
            store: Ext.create('Ext.data.Store', {
                fields: [ 'value', 'name' ],
                data: imageSizeData
            }),
            listeners: {
                scope: me,
                change: function(field, newValue, value) {
                    me.fireEvent('changePreviewSize', field, newValue, value);
                }
            }
        });
        me.imageSize.setValue(16);
        toolbar.add(me.imageSize, { xtype: 'tbspacer', width: 6 });
        return toolbar;

    },

    /**
     * Event listener method which fires when the user
     * selects a media in the media view.
     *
     * Updates the information panel on the right hand and
     * unlocks the "delete media(s)" button.
     *
     * @event select
     * @param { object } rowModel - Associated Ext.selection.RowModel from the Ext.view.View
     * @return void
     */
    onSelectMedia: function(rowModel) {
        var me = this,
            record = rowModel.getLastSelected();

        me.onUnlockDeleteButton();
        if(me.infoView) {
            me.infoView.update(record.data);
            me.attributeButton.setRecord(record);
        }
    },

    /**
     * Unlocks the "delete media(s)" button in the top toolbar
     *
     * @return void
     */
    onUnlockDeleteButton: function() {
        if(this.deleteBtn) {
            this.deleteBtn.setDisabled(false);
        }
    },

    /**
     * Event listener method which fires when the user
     * deselects a media in the media view.
     *
     * Unlocks the "delete media(s)" button in the top toolbar
     *
     * @event deselect
     * @return void
     */
    onLockDeleteButton: function(rowModel) {
        if(this.deleteBtn) {
            this.deleteBtn.setDisabled(!rowModel.getSelection().length);
        }
    },

    /**
     * Event listener method which fires when the user selects
     * a entry in the "media per page"-combo box.
     *
     * @event select
     * @param { object } combo - Ext.form.field.ComboBox
     * @param { array } records - Array of selected entries
     * @return void
     */
    onChangeMediaQuantity: function(combo, records) {
        var record = records[0],
            me = this;

        me.mediaStore.pageSize = record.get('value');
        me.mediaStore.loadPage(1);
    },

    /**
     * Event listener method which fires when the media
     * view is rendered.
     *
     * Initializes the drag zone for the media view.
     *
     * @event render
     * @param { object } view - Associated Ext.view.View
     * @return void
     */
    initializeMediaDragZone: function(view) {
        var me = this;
        var selModel = view.getSelectionModel();

        view.dragZone = Ext.create('Ext.dd.DragZone', view.getEl(), {
            ddGroup: 'media-tree-dd',

            /**
             * Called when a mousedown occurs in this container. Looks in Ext.dd.Registry for a valid target
             * to drag based on the mouse down. Override this method to provide your own lookup logic
             * (e.g. finding a child by class name). Make sure your returned object has a "ddel" attribute (with an HTML Element) for other functions to work.
             *
             * @private
             * @param { object } e - Ext.EventImplObj
             * @return { object } dragData
             */
            getDragData: function(e) {
                var sourceEl = e.getTarget(view.itemSelector, 10), d;

                if (sourceEl) {
                    var selected = selModel.getSelection(),
                        record = view.getRecord(sourceEl);

                    if(!selected.length) {
                        selModel.select(record);
                        selected = selModel.getSelection();
                    }
                    /**
                     * Re initial the plugin to fix the drag selector zone
                     */
                    var dragSelector = view.plugins[0];
                    dragSelector.reInit();

                    d = sourceEl.cloneNode(true);
                    d.id = Ext.id();

                    // todo@stp - show list of the selected files in the drag object
                    return view.dragData = {
                        sourceEl: sourceEl,
                        repairXY: Ext.fly(sourceEl).getXY(),
                        ddel: d,
                        mediaModels: selected
                    };
                }
            },

            /**
             * Force the media manager to be always at the front in the ZIndexManager.
             *
             * @private
             * @return void
             */
            onStartDrag: function() {
                var win = view.up('window');

                Ext.WindowManager.bringToFront(win);
            },

            /**
             * Called before a repair of an invalid drop to get the XY to animate to. By default returns the XY of this.dragData.ddel
             * @return [array] - The xy location (e.g. [100, 200])
             */
            getRepairXY: function() {
                return this.dragData.repairXY;
            }
        });
    },

    /**
     * Simple function to check if a certain file is an image.
     *
     * @param { string }type
     * @param { string } extension
     * @returns { boolean }
     *
     * @private
     */
    _isImage: function (type, extension) {
        return type === 'IMAGE' && !Ext.Array.contains(['tif', 'tiff'], extension);
    }
});
//{/block}
