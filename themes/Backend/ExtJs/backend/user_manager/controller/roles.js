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
 * @subpackage Controller
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/user_manager/view/main}

/**
 * Shopware UI - User Manager roles controller
 *
 * todo@all: Documentation
 */
//{block name="backend/user_manager/controller/roles"}
Ext.define('Shopware.apps.UserManager.controller.Roles', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'RolesGrid', selector: 'usermanager-roles-list' }
    ],

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {

        var me = this;
        me.control({
            'button[action=addRole]': {
                click: me.onAddRole
            },
            'usermanager-roles-list': {
                deleteRole: me.onDeleteRole
            }
        });
    },
    /**
     * Delete a role from store
     * @param view
     * @param rowIndex - index of role in store
     */
    onDeleteRole: function(view,rowIndex){
        var me = this,
        roleStore = me.getStore('Roles'),
        message,
        record = roleStore.getAt(rowIndex);

        message = Ext.String.format('{s name=roles_list/messageDeleteRole}Are you sure you want to delete the role [0]?{/s}', record.data.name);
        Ext.MessageBox.confirm('{s name=roles_list/titleDeleteRole}Delete role{/s}', message, function (response){
            if (response !== 'yes')  return false;

            Shopware.app.Application.fireEvent('Shopware.ValidatePassword', function() {
                record.destroy({
                    success: function () {
                        roleStore.load();
                        Shopware.Notification.createGrowlMessage('{s name=user/Success}Successful{/s}', '{s name=roles_list/deletedSuccesfully}Role has been deleted{/s}', '{s name="user/userManager"}User Manager{/s}');
                    },
                    failure: function () {
                        Shopware.Notification.createGrowlMessage('{s name=user/Error}Error{/s}', '{s name=roles_list/deletedError}An error has occured while deleting role{/s}', '{s name="user/userManager"}User Manager{/s}');
                    }
                });
            });
        });
    },
    /**
     * Add a new role to role management grid
     */
    onAddRole: function(){
        var me = this,
            newRole,
            grid = me.getRolesGrid(),
            roles = me.getStore('Roles');

        grid.rowEditing.cancelEdit();

        newRole = me.getModel('Roles').create({
            name: '',
            description: '',
            source: 'custom',
            enabled: true,
            admin: false,
            parentId: null
        });

        roles.insert(0, newRole);
        grid.rowEditing.startEdit(0, 0);
    }
});
//{/block}
