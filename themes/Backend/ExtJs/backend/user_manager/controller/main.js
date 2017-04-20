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
 * Shopware UI - User Manager Main Controller
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
//{block name="backend/user_manager/controller/main"}
Ext.define('Shopware.apps.UserManager.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * Example: { ref : 'grid', selector : 'grid' } transforms to this.getGrid();
     *          { ref : 'addBtn', selector : 'button[action=add]' } transforms to this.getAddBtn()
     *
     * @object
     */
    refs: [
        { ref: 'globalToolbar', selector: 'usermanager-main-toolbar' },
        { ref: 'navigation', selector: 'usermanager-main-navigation' }
    ],

    /**
     * Contains the component snippets
     * @object
     */
    snippets: {
        information1: '{s name=resource/information_1}In this area, you will be displayed all defined resources and privileges in the beginning.{/s}',
        information2: '{s name=resource/information_2}You can select a defined role via the combobox in the toolbar.{/s}',
        information3: '{s name=resource/information_3}After the selection of a role, the view updates itself and all resources and privileges that have been approved for this role will be marked active (Checkbox active).{/s}',
        information4: '{s name=resource/information_4}To change the approval of the role, you have to activate or deactivate the desired knot and click the button Assign selected privileges to selected role.{/s}'
    },

    /**
     * Creates the necessary event listener for this
     * specific controller and opens a new Ext.window.Window
     * to display the subapplication
     *
     * @return void
     */
    init: function() {

        var me = this;

        // Binds the neccessary event listeners
        me.control({
            'usermanager-main-navigation button[action=open-roles-view]': {
                click: me.onOpenRolesView
            },
            'usermanager-main-navigation button[action=open-rules-view]': {
                click: me.onOpenRulesView
            },
            'usermanager-main-navigation button[action=open-user-view]': {
                click: me.onOpenUserView
            }
        });

        me.mainWindow = me.getView('main.Window').create({
            userStore: me.subApplication.getStore('User')
        });

        me.appContent = me.mainWindow.appContent;

        me.callParent(arguments);
    },

    /**
     * Event listener which replaces the main viewport with
     * the roles administration grid
     *
     * @return void
     */
    onOpenRolesView: function() {
        var me = this;
        Ext.suspendLayouts();
        me.appContent.removeAll();
        me.appContent.add(me.getView('roles.List').create({ roleStore: me.getStore('Roles')}));
        Ext.resumeLayouts(true);
    },

    /**
     * Event listener which replaces the main viewport with
     * the resource/privilege management tree-grid
     */
    onOpenRulesView: function() {
       var me = this;

        Ext.suspendLayouts();
        me.appContent.removeAll();
        me.appContent.add(me.getRulesInformation() , me.getView('rules.Tree').create({ ruleStore: me.getStore('Rules')}));
        Ext.resumeLayouts(true);
     },

    /**
     * Creates the container which contains the rules information.
     *
     * @return Ext.container.Container
     */
    getRulesInformation: function() {
        var me = this;

        return Ext.create('Ext.container.Container', {
            bodyPadding: 5,
            region: 'north',
            width: '100%',
            style: 'color: #999; font-style: italic; margin: 0 0 15px 0; background: #fff; padding: 15px;',
            html: me.snippets.information1 + ' ' + me.snippets.information2 + ' ' + me.snippets.information3 + ' ' + me.snippets.information4
        });
    },

    /**
     * Event that catches clicks on user-listing button
     */
    onOpenUserView: function() {
        var me = this;

        Ext.suspendLayouts();
        me.appContent.removeAll();
        me.appContent.add(me.getView('user.List').create({ userStore: me.getStore('User')}));
        Ext.resumeLayouts(true);

    }
});
//{/block}
