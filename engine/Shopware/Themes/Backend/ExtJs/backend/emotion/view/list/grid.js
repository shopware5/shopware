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
 * @package    UserManager
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/emotion/list/grid}

/**
 * Shopware UI - Emotion Toolbar
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/emotion/list/grid"}
Ext.define('Shopware.apps.Emotion.view.list.Grid', {
	extend: 'Ext.grid.Panel',
    alias: 'widget.emotion-list-grid',
    /**
     * Initializes the component and builds up the main interface
     *
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.store = Ext.create('Shopware.apps.Emotion.store.List').load();
        me.registerEvents();
        me.columns = me.createColumns();
        me.bbar = me.createPagingToolbar();
        me.selModel = me.createSelectionModel();

        me.callParent(arguments);
    },

    registerEvents: function() {
        this.addEvents(
            'deleteemotion',
            'selectionChange',
            'editemotion',
            'duplicateemotion',
            'preview'
        )
    },

    createSelectionModel: function() {
        var me = this;

        return Ext.create('Ext.selection.CheckboxModel', {
            listeners:{
                // Unlocks the save button if the user has checked at least one checkbox
                selectionchange:function (sm, selections) {
                    me.fireEvent('selectionChange', selections);
                }
            }
        });
    },

    createPagingToolbar: function() {
        var me = this,
            toolbar = Ext.create('Ext.toolbar.Paging', {
            store: me.store
        });

        return toolbar;
    },

    createColumns: function() {
        var me = this;
        var columns = [{
            header: '{s name=grid/column/category_name}Category name{/s}',
            dataIndex: 'emotions.categoriesNames',
            flex: 2,
            renderer: me.categoryColumn,
            sortable: false
        }, {
            header: '{s name=grid/column/name}Name{/s}',
            dataIndex: 'emotions.name',
            flex: 2,
            renderer: me.nameColumn
        }, {
            header: '{s name=grid/column/type}Type{/s}',
            flex: 2,
            tdCls: 'emotion-device-column',
            renderer: me.typeColumn
        }, {
            xtype: 'datecolumn',
            header: '{s name=grid/column/date}Last edited{/s}',
            dataIndex: 'emotions.modified',
            flex: 2,
            renderer: me.modifiedColumn
        }, {
            header: '{s name=grid/column/active}Active{/s}',
            dataIndex: 'emotions.status',
            width: 50,
            sortable: false,
            renderer: me.statusColumn
        },
        {
            xtype: 'actioncolumn',
            header: '{s name=grid/column/action}Actions{/s}',
            width: 80,
            border: 0,
            sortable: false,
            items: [
			/*{if {acl_is_allowed privilege=delete}}*/
			{
                iconCls: 'sprite-minus-circle',
                tooltip:'{s name=list/action_column/delete}Delete shopping world{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('deleteemotion', record);
                }
            },
			/*{/if}*/
			/*{if {acl_is_allowed privilege=update}}*/
			{
                iconCls: 'sprite-pencil',
                tooltip:'{s name=list/action_column/edit}Edit shopping world{/s}',
                handler: function(view, rowIndex, colIndex) {
                    me.fireEvent('editemotion', me, view, rowIndex, colIndex);
                }
            },
            /*{/if}*/
            {
                iconCls: 'sprite-globe--arrow',
                tooltip:'{s name=list/action_column/preview}Preview shopping world{/s}',
                handler: function(view, rowIndex) {
                    var listStore = view.getStore(),
                        deviceId = listStore.getAt(rowIndex).get('device'),
                        emotionId = listStore.getAt(rowIndex).get('id'),
                        emotionName = listStore.getAt(rowIndex).get('name');

                    me.fireEvent('preview', emotionId, emotionName, deviceId);
                }
            }
			]
        },
        me.createCopyDropdown()
        ];

        return columns;
    },

    /**
     * Creates the copy split button with the
     * device copy options.
     */
    createCopyDropdown: function() {
        var me = this;
        return {
            xtype: 'buttoncolumn',
                width: 50,
            header: '',
            sortable: false,
            borderLeftWidth: 0,
            iconCls: 'sprite-document-copy',
            buttonText: '',
            tooltip: 'Einkaufswelt kopieren',
            handler: function (view, rowIndex, colIndex) {
            var listStore = view.getStore();
            var record = listStore.getAt(rowIndex);
            var device = record.get('device');

            me.fireEvent('duplicateemotion', me, record, device);
        },
            stopSelection: true,        //don't select record on button click
                items: [
            {
                iconCls: 'sprite-imac-icon',
                text: '{s name="list/action_column/copy_desktop"}Als Desktop Einkaufswelt{/s}',
                handler: function (item, scope) {
                    var record = scope.record;
                    me.fireEvent('duplicateemotion', me, record, 0);
                }
            },
            {
                iconCls: 'sprite-ipad-icon',
                text: '{s name="list/action_column/copy_tablet"}Als Tablet Einkaufswelt{/s}',
                handler: function (item, scope) {
                    var record = scope.record;
                    me.fireEvent('duplicateemotion', me, record, 1);
                }
            },
            {
                iconCls: 'sprite-iphone-icon',
                text: '{s name="list/action_column/copy_mobile"}Als mobile Einkaufswelt{/s}',
                handler: function (item, scope) {
                    var record = scope.record;
                    me.fireEvent('duplicateemotion', me, record, 2);
                }
            }
        ]
        }
    },

    /** 
     * Column renderer function for the category name column.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    categoryColumn: function(value, metaData, record) {
        var names = record.get('categoriesNames');

        if (names.length) {
            return '<strong>' + names + '</strong>';
        } else {
            return '<strong>{s name=grid/render/no_category}No category selected{/s}</strong>';
        }
    },

    /**
     * Column renderer function for the emotion name column.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    nameColumn: function(value, metaData, record) {
        return record.get('name');
    },


    /**
     * Column renderer function for the emotion type column.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    typeColumn: function(value, metaData, record) {
        if(!record) {
            return false;
        }

        var type = '{s name=grid/renderer/emotion}Emotion{/s}',
            device = '<div class="sprite-imac-icon" style="width: 16px; height: 16px; display: inline-block; margin-right:5px" title="Nur für Desktop Computer sichtbar">&nbsp;</div>';

        // Type detection
        if(record.get('isLandingPage')) {
            type = '{s name=grid/renderer/landingpage}Landingpage{/s}'
        }

        // Device detection
        if(record.get('device') == 1) {
            device = '<div class="sprite-ipad-icon" style="width: 16px; height: 16px; display: inline-block; margin-right:5px" title="Nur für Tablets sichtbar">&nbsp;</div>';
        } else if(record.get('device') == 2) {
            device = '<div class="sprite-iphone-icon" style="width: 16px; height: 16px; display: inline-block; margin-right:5px" title="Nur für mobile Geräte sichtbar">&nbsp;</div>';
        }

        return device + type;
    },

    /**
     * Column renderer function for the modified column
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    modifiedColumn: function(value, metaData, record) {
       return Ext.util.Format.date(record.get('modified')) + ' ' + Ext.util.Format.date(record.get('modified'), 'H:i:s');
    },

    /**
     * Column renderer function for the emotion status column.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    statusColumn: function(value, metaData, record) {
        if (record.get('active')) {
            return '<div class="sprite-ui-check-box"  style="width: 25px; height: 25px">&nbsp;</div>';
        } else {
            return '<div class="sprite-cross-small" style="width: 25px; height: 25px">&nbsp;</div>';
        }
    }
});
//{/block}
