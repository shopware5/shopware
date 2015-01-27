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
 * @package    UserManager
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/user_manager/view/main}

/**
 * Shopware Backend - View for role-grid
 *
 * todo@all: Documentation
 */
//{block name="backend/user_manager/view/roles/list"}
Ext.define('Shopware.apps.UserManager.view.roles.List', {
	extend: 'Ext.grid.Panel',
	alias: 'widget.usermanager-roles-list',
	region: 'center',
	autoScroll: true,
    height: '100%',
    selType: 'rowmodel',

    createDockedToolBar: function(){
          return [{
                dock: 'bottom',
                xtype: 'pagingtoolbar',
                displayInfo: true,
                store: this.roleStore
          }];
    },

    /**
     * Initialize the view components
     *
     * @return void
     */
	initComponent: function() {
		var me = this;
        me.store = this.roleStore;
        me.dockedItems = this.createDockedToolBar();
        me.plugins = Ext.create('Ext.grid.plugin.RowEditing', {
            clicksToEdit: 1
        });
        me.rowEditing = me.plugins;

        me.on('edit', me.onEditRow, me);

		// Define the columns and renderers
		this.columns = [
		{
			header: '{s name=roleslist/colname}Name{/s}',
			dataIndex: 'name',
			flex: 1,
            field: 'textfield'
		}, {
			header: '{s name=roleslist/coldescription}Description{/s}',
			dataIndex: 'description',
			flex: 1,
            field: 'textfield'
		}, {
			header: '{s name=roleslist/colsource}Source{/s}',
			dataIndex: 'source',
			flex: 1
		}, {
            xtype: 'booleancolumn',
            header: '{s name=roleslist/colactive}Enabled{/s}',
            dataIndex: 'enabled',
            flex: 1,
            editor: {
                xtype: 'checkbox',
                inputValue: 'true',
                uncheckedValue: 'false'
            }
        },
        {
            xtype: 'booleancolumn',
            header: '{s name=roleslist/coladmin}Admin{/s}',
            dataIndex: 'admin',
            flex: 1,
            editor: {
                xtype: 'checkbox',
                inputValue: 'true',
                uncheckedValue: 'false'
            }
        },
        /* {if {acl_is_allowed privilege=delete}} */
        {
			xtype: 'actioncolumn',
			width: 50,
			items: [{
				iconCls: 'sprite-minus-circle',
				cls: 'delete',
				tooltip: '{s name=roleslist/colactiondelete}Delete this role{/s}',
                handler:function (view, rowIndex, colIndex, item) {
                    me.fireEvent('deleteRole', view, rowIndex, colIndex, item);
                }
			}]
		}
        /* {/if} */];


		// Toolbar
		this.toolbar = Ext.create('Ext.toolbar.Toolbar', {
			dock: 'top',
            ui: 'shopware-ui',
        /* {if {acl_is_allowed privilege=create}} */
		    items: [{
				iconCls: 'sprite-plus-circle',
				text: '{s name=roleslist/addrole}Add role{/s}',
				action: 'addRole'
			}
			]
        /* {/if} */
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
        Shopware.Notification.createGrowlMessage('{s name=user/Success}Successful{/s}', '{s name=roles_list/updatedSuccesfully}Role has been updated{/s}', '{s name="user/userManager"}User Manager{/s}');
    }
});
//{/block}
