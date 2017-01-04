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
//{block name="backend/media_manager/view/media/grid"}
Ext.define('Shopware.apps.MediaManager.view.media.Grid', {
    extend: 'Ext.grid.Panel',
    border: 0,
    bodyBorder: 0,
    alias: 'widget.mediamanager-media-grid',
    /**
     * Selected preview size in px
     * @Number
     * @default 16
     */
    selectedPreviewSize: 16,

    /**
     * Used snippets in this component
     * @object
     */
    snippets: {
        column: {
            'preview': '{s name=grid/column/preview}Preview{/s}',
            'created': '{s name=grid/column/created}Upload date{/s}',
            'name': '{s name=grid/column/name}File name{/s}',
            'width': '{s name=grid/column/width}Image width{/s}',
            'height': '{s name=grid/column/height}Image height{/s}',
            'type': '{s name=grid/column/type}File type{/s}'
        },
        types: {
            'video': '{s name=grid/types/video}Video{/s}',
            'music': '{s name=grid/types/music}Music{/s}',
            'archive': '{s name=grid/types/archive}Archive{/s}',
            'pdf': '{s name=grid/types/pdf}PDF{/s}',
            'image': '{s name=grid/types/image}Image{/s}',
            'unknown': '{s name=grid/types/unknown}Unknown{/s}'
        }
    },

    /**
     * Initializes the component and sets the neccessary
     * toolbars and items.
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.addEvents('showDetail');

        me.columns = me.createColumns();
        me.store = me.mediaStore;

        me.viewConfig = {
            plugins: {
                ptype: 'gridviewdragdrop',
                ddGroup: 'media-tree-dd',
                enableDrop: false
            }
        };

        // Set a checkbox model as the selection model for the grid
        me.selModel = Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                scope: me,
                selectionchange: function(grid, selection) {
                    me.fireEvent('showDetail', grid, selection);
                }
            }
        });

        // Grid plugins
        me.plugins = [
            Ext.create('Ext.grid.plugin.RowEditing', {
                clicksToEdit: 2
            })
        ];

        me.callParent(arguments);
    },

    /**
     * Creates the columns for the list view.
     *
     * @returns { Array }
     */
    createColumns: function() {
        var me = this;

        return [{
            dataIndex: 'thumbnail',
            header: me.snippets.column.preview,
            width: 50,
            align: 'center',
            renderer: me.previewRenderer,
            sortable: false
        }, {
            dataIndex: 'created',
            header: me.snippets.column.created,
            renderer: me.dateRenderer,
            flex: 1
        }, {
            dataIndex: 'name',
            header: me.snippets.column.name,
            flex: 3,
            renderer: me.nameRenderer,
            editor: {
                xtype: 'textfield',
                allowBlank: false
            }
        }, {
            dataIndex: 'width',
            header: me.snippets.column.width,
            hidden: true,
            flex: 1,
            renderer: me.pixelRenderer
        }, {
            dataIndex: 'height',
            header:  me.snippets.column.height,
            hidden: true,
            flex: 1,
            renderer: me.pixelRenderer
        }, {
            dataIndex: 'type',
            header: me.snippets.column.type,
            flex: 1,
            renderer: me.typeRenderer
        }]
    },

    /**
     * Renders the preview column. If the entry is an image, the image will be rendered. Otherwise
     * the renderer renders an item (using a `div` box).
     *
     * @param { String } value - The value of the column
     * @param { Object } tdStyle - The style of the `td` element
     * @param { Shopware.apps.MediaManager.model.Media } record - The used record
     * @returns { String } Formatted output
     */
    previewRenderer: function(value, tdStyle, record) {
        var me = this,
            type = record.get('type').toLowerCase(),
            value = value + '?' + new Date().getTime(),
            result;

        switch(type) {
           case 'video':
               result = '<div class="sprite-blue-document-film" style="height:16px; width:16px;display:inline-block"></div>';
               break;
           case 'music':
               result = '<div class="sprite-blue-document-music" style="height:16px; width:16px;display:inline-block"></div>';
               break;
           case 'archive':
               result = '<div class="sprite-blue-document-zipper" style="height:16px; width:16px;display:inline-block"></div>';
               break;
           case 'pdf':
               result = '<div class="sprite-blue-document-pdf-text" style="height:16px; width:16px;display:inline-block"></div>';
               break;
           case 'image':
               result = Ext.String.format('<div class="small-preview-image"><img src="[0]" style="max-width:[1]px;max-height:[1]px" alt="[2]" /></div>', value, me.selectedPreviewSize, record.get('name'));
               break;
           default:
               result = '<div class="sprite-blue-document-text" style="height:16px; width:16px;display:inline-block"></div>';
               break;
       }

        return result;

    },

    /**
     * Renders the name column of the list view.
     *
     * @param { String } value - The value of the column
     * @param { Object } tdStyle - The style of the `td` element
     * @param { Shopware.apps.MediaManager.model.Media } record - The used record
     * @returns { String } Formatted output
     */
    nameRenderer: function(value, tdStyle, record) {
        value = Ext.String.format('[0].[1]', value, record.get('extension'));
        return value;
    },

    /**
     * Renders the date column of the list view.
     *
     * @param { String } value - The value of the column
     * @returns { String } Formatted output
     */
    dateRenderer: function(value) {
        return Ext.util.Format.date(value);
    },

    /**
     * Renders the width and height of the list view. If the value is empty,
     * an minus ("-") will be returned.
     *
     * @param { String } value - The value of the column
     * @returns { String }
     */
    pixelRenderer: function(value) {
        if(value) {
            return value + 'px';
        }
        return '-';
    },

    /**
     * Renders the type column of the list view. The value will
     * be replaced with a snippet.
     *
     * @param { String } value - The value of the column
     * @returns { String } Formatted output
     */
    typeRenderer: function(value) {
        var me = this, result = '', type = value.toLowerCase();

        switch(type) {
            case 'video':
                result = me.snippets.types[type];
                break;
            case 'music':
                result = me.snippets.types[type];
                break;
            case 'archive':
                result = me.snippets.types[type];
                break;
            case 'pdf':
                result = me.snippets.types[type];
                break;
            case 'image':
                result = me.snippets.types[type];
                break;
            default:
                result = me.snippets.types['unknown'];
                break;
        }
        return result;
    }
});
//{/block}
