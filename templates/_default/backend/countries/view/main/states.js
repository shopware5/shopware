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
 * @package    Countries
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/countries/view/main}

/**
 * Shopware Backend - View for state-grid
 *
 * todo@all: Documentation
 */
//{block name="backend/countries/view/main"}
Ext.define('Shopware.apps.Countries.view.main.States', {
	extend: 'Ext.grid.Panel',
	alias: 'widget.country-states',
    autoScroll:true,
    selType: 'rowmodel',

    createDockedToolBar: function(){
          return [{
                dock: 'bottom',
                xtype: 'pagingtoolbar',
                displayInfo: true,
                store: this.stateStore
          }];
    },

    /**
     * Initialize the view components
     *
     * @return void
     */
	initComponent: function() {
		var me = this;
        me.store = me.stateStore;
        me.dockedItems = this.createDockedToolBar();
        me.plugins = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1
        });
        me.rowEditing = me.plugins;

        me.on('edit', me.onEditRow, me);

		// Define the columns and renderers
		this.columns = [
		{
			header: '{s name=stateslist/colname}Name{/s}',
			dataIndex: 'name',
			flex: 1,
            field: 'textfield'
		},
        {
            header: '{s name=stateslist/colkey}Key/Shortcode{/s}',
            dataIndex: 'shortCode',
            flex: 1,
            field: 'textfield'
        },
        {
			header: '{s name=stateslist/coldescription}Position{/s}',
			dataIndex: 'position',
			flex: 1,
            field: 'textfield'
		}, {
            xtype: 'booleancolumn',
            header: '{s name=stateslist/colactive}Enabled{/s}',
            dataIndex: 'active',
            flex: 1,
            editor: {
                xtype: 'checkbox',
                inputValue: 'true',
                uncheckedValue: 'false'
            }
        },
        {
			xtype: 'actioncolumn',
			width: 50,
			items: [{
				iconCls: 'sprite-minus-circle',
				cls: 'delete',
				tooltip: '{s name=stateslist/colactiondelete}Delete this state.{/s}',
                handler:function (view, rowIndex, colIndex, item) {
                    me.fireEvent('deleteState', view, rowIndex, colIndex, item);
                }
			}]
		}];


		// Toolbar
		this.toolbar = Ext.create('Ext.toolbar.Toolbar', {
			dock: 'top',
            ui: 'shopware-ui',
		    items: [{
				iconCls: 'sprite-plus-circle',
				text: '{s name=stateslist/addstate}Add state{/s}',
				action: 'addState'
			}
			]
		});


		this.dockedItems = Ext.clone(this.dockedItems);
		this.dockedItems.push(this.toolbar);

		this.callParent();
    },

    /**
     * Event listener method which will be fired when the user
     * edits a row in the role grid with the built-in row
     * editor.
     *
     * Saves the edited record to the store.
     *
     * @event edit
     * @param [object] editor
     * @return void
     */
    onEditRow: function(editor, event) {
        var store = event.store;

        editor.grid.setLoading(true);
        store.sync({
            callback: function() {
                editor.grid.setLoading(false);
            }
        });
    }
});
//{/block}