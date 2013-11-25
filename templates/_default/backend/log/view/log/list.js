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
 * @package    Log
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/log/main}

/**
 * Shopware UI - Log view list
 *
 * This grid contains all logs and its information.
 */
//{block name="backend/log/view/log/list"}
Ext.define('Shopware.apps.Log.view.log.List', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.grid.Panel',
    border: 0,

    ui: 'shopware-ui',

    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('log-main-list')
    * @string
    */
    alias: 'widget.log-main-list',
    /**
    * The window uses a border layout, so we need to set
    * a region for the grid panel
    * @string
    */
    region: 'center',
    /**
    * The view needs to be scrollable
    * @string
    */
    autoScroll: true,

    /**
    * Sets up the ui component
    * @return void
    */
    initComponent: function() {
        var me = this;
		me.registerEvents();
		me.selModel = me.getGridSelModel();
		me.store = me.logStore;
		me.toolbar = me.getToolbar();
		me.columns = me.getColumns();
		me.dockedItems = [];
		me.dockedItems.push(me.toolbar);

		// Add paging toolbar to the bottom of the grid panel
		me.dockedItems.push({
			dock: 'bottom',
			xtype: 'pagingtoolbar',
			displayInfo: true,
			store: me.store
		});
		me.callParent(arguments);
    },

	/**
	 * Creates the toolbar
	 *
	 * @return [object] Ext.toolbar.Toolbar
	 */
	getToolbar: function(){
		var items = [];
		/*{if {acl_is_allowed privilege=delete}}*/
		items.push(Ext.create('Ext.button.Button',{
			iconCls: 'sprite-minus-circle',
			text: '{s name=toolbar/deleteMarkedEntries}Delete marked entries{/s}',
			disabled: true,
			action: 'deleteMultipleLogs'
		}));
		/*{/if}*/
		items.push('->');
		items.push({
			xtype: 'combo',
			store: Ext.create('Shopware.apps.Log.store.Users'),
			valueField:'id',
			displayField:'name',
			emptyText: '{s name=toolbar/filterField}Filter by{/s}'
		});
		items.push({
			xtype: 'tbspacer',
			width: 6
		});

		return Ext.create('Ext.toolbar.Toolbar', {
			dock: 'top',
			ui: 'shopware-ui',
			items: items
		});
	},

    /**
     * Creates the selectionModel of the grid with a listener to enable the delete-button
     */
    getGridSelModel: function(){
		return Ext.create('Ext.selection.CheckboxModel',{
			listeners: {
				selectionchange: function(sm, selections) {
					var owner = this.view.ownerCt,
						btn = owner.down('button[action=deleteMultipleLogs]');

					//If no log is marked
					if(btn) {
						btn.setDisabled(selections.length == 0);
					}
				}
			}
		});
    },

    /**
     *  Creates the columns
	 *
	 *  @return array columns Contains all columns
     */
    getColumns: function(){
        var me = this;

        var columns = [{
			header: '{s name=grid/column_date}Date{/s}',
			dataIndex: 'date',
			flex: 1,
			xtype: 'datecolumn',
			renderer: me.renderDate
		},{
			header: '{s name=grid/column_user}User{/s}',
			dataIndex: 'user',
			flex: 1
		}, {
			header: '{s name=grid/column_module}Module{/s}',
			dataIndex: 'key',
			flex: 1
		},{
			header: '{s name=grid/column_text}Text{/s}',
			dataIndex: 'text',
			flex: 1
		}
		/*{if {acl_is_allowed privilege=delete}}*/
        ,
		{
			header: '{s name=grid/actioncolumn}Options{/s}',
			xtype: 'actioncolumn',
			renderer: me.renderActionColumn
		}
		/*{/if}*/
		];

        return columns;
    },

	/**
	 * Renders the date
	 *
	 * @param value
	 * @return [date] value Contains the date
	 */
	renderDate: function(value){
		return Ext.util.Format.date(value) + ' ' + Ext.util.Format.date(value, timeFormat);
	},

	/**
	 * Renders the action-column
	 *
	 * @param value Contains the clicked value
	 * @param metaData Contains the metaData
	 * @param model Contains the selected model
	 * @param rowIndex Contains the rowIndex of the selection
	 * @return [object] Ext.DomHelper
	 */
	renderActionColumn: function(value, metaData, model, rowIndex){
		var data = [];

		data.push(Ext.DomHelper.markup({
			tag:'img',
			'class': 'x-action-col-icon sprite-minus-circle',
			tooltip: '{s name=grid/actioncolumn/buttonTooltip}Delete log{/s}',
			cls:'sprite-minus-circle',
			onclick: "Ext.getCmp('" + this.id + "').fireEvent('deleteColumn', " + rowIndex + ");"
		}));

		return data;
	},

	/**
	 * Defines additional events which will be
	 * fired from the component
	 *
	 * @return void
	 */
	registerEvents:function () {
		this.addEvents(
			/**
			 * Event will be fired when the user clicks the delete icon in the
			 * action column
			 *
			 * @event deleteColumn
			 * @param [integer] rowIndex - Row index of the selection
			 */
			'deleteColumn'
		)
	}
});
//{/block}