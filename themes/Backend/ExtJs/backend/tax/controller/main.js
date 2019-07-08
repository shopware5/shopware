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
 * Shopware UI - User Manager Main Controller
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 *
 * @link http://www.shopware.de/
 * @license http://www.shopware.de/license
 * @package tax
 * @subpackage Controller/Main
 */
//{namespace name=backend/tax/view/main}
//{block name="backend/tax/controller/main"}
Ext.define('Shopware.apps.Tax.controller.Main', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',
    refs:[
        { ref:'navigationTree', selector:'tax-tree' },
        { ref:'rulesGrid', selector:'tax-rules' },
        { ref:'deleteGroupButton', selector:'tax-tree button[action=onDeleteGroup]' }
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
            'tax-tree': {
                itemclick: me.onItemClick,
                itemdblclick: me.onItemDblClick
            },
            'button[action=addRule]': {
                click: me.onAddRule
            },
            'tax-rules': {
                deleteRule: me.onDeleteRule
            },
            'button[action=onCreateGroup]': {
                click: me.onCreateGroup
            },
            'button[action=onDeleteGroup]': {
                click: me.onDeleteGroup
            }
        });

        me.mainWindow = me.getView('main.Window').create({
            treeStore: me.getStore('Groups'),
            ruleStore: me.getStore('Rules'),
            subApplication: me
        });

        me.appContent = me.mainWindow.appContent;
    },
    onCreateGroup: function(){
        var me = this;
        Ext.MessageBox.prompt('Name', 'Name for new group:', function(result, value){
           if (result !== 'ok') {
               return;
           }
           Ext.Ajax.request({
               url: '{url controller="Tax" action="updateGroup"}',
               params: {
                   id: 0,
                   name: value
               },
               success: function(response){
                   // Update tree-node text
                   this.getNavigationTree().store.load();
                   Shopware.Msg.createGrowlMessage('','{s name=groupList/renameSuccessfully}Group has been created{/s}', '{s name=window_title}{/s}');},
               failure: function(){
                   Shopware.Msg.createGrowlMessage('', '{s name=groupList/renameError}Error while creating group{/s}', '{s name=window_title}{/s}');
               },
               scope:this
           });
       }, this, false, null);
    },
    onDeleteGroup: function(){
        var me = this;
        var tree = me.getNavigationTree();
        var record = tree.getSelectionModel().getSelection()[0];

        message = Ext.String.format('{s name=tree/messageDeleteGroup}Do you really want to delete the tax group [0]?{/s}', record.data.text);
        Ext.MessageBox.confirm('{s name=tree/titleDeleteGroup}Delete tax group{/s}', message, function (response){
         if (response !== 'yes')  return false;
         record.destroy({
             success : function () {
                 Shopware.Msg.createGrowlMessage('','{s name=tree/deletedSuccessfully}Tax group was deleted{/s}', '{s name=window_title}{/s}')
             },
             failure : function () {
                 Shopware.Msg.createGrowlMessage('', '{s name=tree/deletedError}Error while deleting tax group{/s}', '{s name=window_title}{/s}');
             }
         });
        });
    },
    onDeleteRule: function(view,rowIndex){
        var me = this,
        rulesStore = me.getStore('Rules'),
        message,
        record = rulesStore.getAt(rowIndex);

        message = Ext.String.format('{s name=ruleslist/messageDeleteRule}Do you really want to delete the rule [0]?{/s}', record.data.name);
        Ext.MessageBox.confirm('{s name=ruleslist/titleDeleteRuleDelete}Delete rule{/s}', message, function (response){
         if (response !== 'yes')  return false;
         record.destroy({
             success : function () {
                 rulesStore.load();
                 Shopware.Msg.createGrowlMessage('','{s name=ruleslist/deletedSuccessfully}Rule was deleted{/s}', '{s name=window_title}{/s}');
             },
             failure : function () {
                 Shopware.Msg.createGrowlMessage('', '{s name=ruleslist/deletedError}An error has occured while deleting rule{/s}', '{s name=window_title}{/s}');
             }
         });
        });
    },
    onAddRule: function(){
        var me = this;
        var grid = me.getRulesGrid();

        grid.rowEditing.cancelEdit();
        var ruleStore = me.getStore('Rules');
        var newRule = me.getModel('Rules').create(
          {
              name: 'Enter name...'
          }
        );
        ruleStore.insert(0,newRule);
        grid.rowEditing.startEdit(0, 0);
    },
    onItemDblClick: function(tree,record){
        Ext.MessageBox.prompt('Name', 'Change name:', function(result, value){
            if (result !== 'ok') {
                return;
            }
            Ext.Ajax.request({
                url: '{url controller="Tax" action="updateGroup"}',
                params: {
                    id: record.data.id,
                    name: value
                },
                success: function(response){
                    // Update tree-node text
                    this.getNavigationTree().getRootNode().findChild('id',record.data.id,true).set('text',value);
                    Shopware.Msg.createGrowlMessage('','{s name=groupList/renameSuccessfully}Group was renamed{/s}', '{s name=window_title}{/s}');},
                failure: function(){
                    Shopware.Msg.createGrowlMessage('', '{s name=groupList/renameError}Error while renaming group{/s}', '{s name=window_title}{/s}');
                },
                scope:this
            });
        }, this,false,record.data.text);
    },
    onItemClick: function(item,record) {
        var me = this;

        me.getDeleteGroupButton().enable();
        var groupId = record.data.id;

        // Load & enable country properties form
        me.getStore('Rules').getProxy().extraParams = {
            groupId: groupId
        };

        me.getStore('Rules').load({
            callback:function (records) {
               me.getRulesGrid().enable();
            }
        });
    }
});
//{/block}
