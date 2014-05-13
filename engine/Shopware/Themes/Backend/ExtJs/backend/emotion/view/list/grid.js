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

    deviceWidth: {
        desktop: 1260,
        tablet: 1024,
        mobile: 768
    },

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
            'duplicateemotion'
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
            renderer: me.typeColumn
        }, {
            header: '{s name=grid/column/container_width}Container width{/s}',
            dataIndex: 'emotions.containerWidth',
            flex: 1,
            renderer: me.containerWidthColumn
        }, {
            xtype: 'datecolumn',
            header: '{s name=grid/column/date}Last edited{/s}',
            dataIndex: 'emotions.modified',
            flex: 1,
            renderer: me.modifiedColumn
        }, {
            header: '{s name=grid/column/active}Active{/s}',
            dataIndex: 'emotions.status',
            flex: 1,
            renderer: me.statusColumn
        }, {
            xtype: 'actioncolumn',
            header: '{s name=grid/column/action}Actions{/s}',
            width: 160,
            items: [
			/*{if {acl_is_allowed privilege=delete}}*/
			{
                iconCls: 'sprite-minus-circle',
                tooltip:'{s name=list/action_column/edit}Delete shopping world{/s}',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('deleteemotion', record);
                }
            },
			/*{/if}*/
			/*{if {acl_is_allowed privilege=update}}*/
			{
                iconCls: 'sprite-pencil',
                tooltip:'{s name=list/action_column/delete}Edit shopping world{/s}',
                handler: function(view, rowIndex, colIndex) {
                    me.fireEvent('editemotion', me, view, rowIndex, colIndex);
                }
            },
            /*{/if}*/
            {
                iconCls: 'sprite-television',
                tooltip:'{s name=list/action_column/copy_desktop}Copy shopping world for desktop devices{/s}',
                handler: function(view, rowIndex, colIndex) {
                    me.fireEvent('duplicateemotion', me, view, rowIndex, colIndex, 0);
                }
            },
            {
                iconCls: 'sprite-e-book-reader',
                tooltip:'{s name=list/action_column/copy_tablet}Copy shopping world for tablet devices{/s}',
                handler: function(view, rowIndex, colIndex) {
                    me.fireEvent('duplicateemotion', me, view, rowIndex, colIndex, 1);
                }
            },
            {
                iconCls: 'sprite-mobile-phone-off',
                tooltip:'{s name=list/action_column/copy_mobile}Copy shopping world for mobile devices{/s}',
                handler: function(view, rowIndex, colIndex) {
                    me.fireEvent('duplicateemotion', me, view, rowIndex, colIndex, 2);
                }
            },
            {
                iconCls: 'sprite-binocular--arrow',
                tooltip:'{s name=list/action_column/preview}Preview shopping world{/s}',
                handler: function(view, rowIndex, colIndex, record) {

                    var listStore = view.getStore(),
                        deviceId = listStore.getAt(rowIndex).get('device'),
                        emotionId = listStore.getAt(rowIndex).get('id'),
                        width = me.deviceWidth.desktop;

                    if(deviceId == 1) {
                        width = me.deviceWidth.tablet;
                    } else if(deviceId == 2) {
                        width = me.deviceWidth.mobile;
                    }

                    new Ext.Window({
                        title : "Einkaufswelten Vorschau",
                        width : width,
                        height: '90%',
                        layout : 'fit',
                        items : [{
                            xtype : "component",
                            autoEl : {
                                tag : "iframe",
                                src : '{url module=frontend controller=emotion action=preview}/?emotionId=' + emotionId
                            }
                        }]
                    }).show();

                }
            }
			]
        }];

        return columns;
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
            device = '<div class="sprite-television" style="width: 16px; height: 16px; display: inline-block" title="Nur für Desktop Computer sichtbar">&nbsp;</div>';

        // Type detection
        if(record.get('isLandingPage')) {
            type = '{s name=grid/renderer/landingpage}Landingpage{/s}'
        }

        // Device detection
        if(record.get('device') == 1) {
            device = '<div class="sprite-e-book-reader" style="width: 16px; height: 16px; display: inline-block" title="Nur für Tablets sichtbar">&nbsp;</div>';
        } else if(record.get('device') == 2) {
            device = '<div class="sprite-mobile-phone-off" style="width: 25px; height: 16px; display: inline-block" title="Nur für mobile Geräte sichtbar">&nbsp;</div>';
        }

        return type + '&nbsp;&nbsp;' + device;
    },

    /**
     * Column renderer function for the category name column.
     * @param [string] value    - The field value
     * @param [string] metaData - The model meta data
     * @param [string] record   - The whole data model
     */
    containerWidthColumn: function(value, metaData, record) {
        return Ext.String.format('[0]px', record.get('containerWidth'));
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
            return '<div class="sprite-tick-small"  style="width: 25px; height: 25px">&nbsp;</div>';
        } else {
            return '<div class="sprite-cross-small" style="width: 25px; height: 25px">&nbsp;</div>';
        }
    }
});
//{/block}