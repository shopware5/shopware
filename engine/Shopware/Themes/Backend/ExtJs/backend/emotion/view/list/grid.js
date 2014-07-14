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
            flex: 1,
            renderer: function(view, meta, record) {
                if(!record) {
                    return false;
                }

                if(record.get('isLandingPage')) {
                    return '{s name=grid/renderer/landingpage}Landingpage{/s}'
                } else {
                    return '{s name=grid/renderer/emotion}Emotion{/s}'
                }
            }
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
            xtype: 'actioncolumn',
            header: '{s name=grid/column/action}Actions{/s}',
            width: 75,
            items: [
			/*{if {acl_is_allowed privilege=delete}}*/
			{
                iconCls: 'sprite-minus-circle',
                handler: function (view, rowIndex, colIndex, item, opts, record) {
                    me.fireEvent('deleteemotion', record);
                }
            },
			/*{/if}*/
			/*{if {acl_is_allowed privilege=update}}*/
			{
                iconCls: 'sprite-pencil',
                handler: function(view, rowIndex, colIndex) {
                    me.fireEvent('editemotion', me, view, rowIndex, colIndex);
                }
            }
            /*{/if}*/
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
    }



});
//{/block}