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
 * @package    Category
 * @subpackage Main
 * @version    $Id$
 * @author shopware AG
 */

/* {namespace name=backend/category/main} */

/**
 * Shopware Controller - category management controller
 *
 * The category management controller handles the initialisation of the mainWindow and takes care
 * of most of the communication to and from the server.
 */
//{block name="backend/category/controller/main"}
Ext.define('Shopware.apps.Category.controller.Main', {
    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
    extend: 'Ext.app.Controller',

    /**
     * Contains the main window of this sub-application.
     * @object
     */
    mainWindow: null,

    /**
     * Define references for the different parts of our application. The
     * references are parsed by ExtJS and Getter methods are automatically created.
     *
     * @array
     */
    refs: [
        { ref: 'mainWindow', selector: 'category-main-window' },
        { ref: 'settingsForm', selector: 'category-category-tabs-settings' }
    ],

    /**
     * Default root node ID
     * @integer
     */
    defaultRootNodeId : 1,

    /**
     * Translations
     * @Object
     */
    snippets : {
        onSaveChangesSuccess    : '{s name=settings/save_success}Changes have been saved successfully.{/s}',
        onSaveChangesError      : '{s name=settings/save_error}An error has occurred while saving the changes.{/s}',
        growlMessage            : '{s name=window/main_title}{/s}'
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

        me.subApplication.defaultRootNodeId = me.defaultRootNodeId;

        me.subApplication.treeStore =  me.subApplication.getStore('Tree');
        me.subApplication.custeromGroupsStore =  me.subApplication.getStore('CustomerGroups');
        me.subApplication.DetailStore =  me.subApplication.getStore('Detail');
        me.subApplication.templateStore =  me.subApplication.getStore('Template');

        // Stores for the product assignment
        me.subApplication.availableProductsStore = me.subApplication.getStore('AvailableProducts');
        me.subApplication.assignedProductsStore = me.subApplication.getStore('AssignedProducts');

        me.subApplication.treeStore.getProxy().extraParams = {
            node:me.defaultRootNodeId
        };
        me.subApplication.treeStore.load();
        me.subApplication.templateStore.load();

        me.mainWindow = me.getView('main.Window').create({
            templateStore:me.subApplication.templateStore,
            treeStore:me.subApplication.treeStore
        });

        me.control({
            'category-tab-custom-listing': {
                'saveCategory': me.saveCategory
            },
            // Save button
            'category-main-window':{
                'saveDetail' : me.onSaveSettings
            }
        });
    },

    saveCategory: function(category, callback) {
        var me = this,
            form = me.getMainWindow().formPanel.getForm();

        if (!form.isValid()) {
            return;
        }

        form.updateRecord(category);

        category.save({
            callback: Ext.bind(callback)
        });
    },

    /**
     * Event listener method which will be fired when the user
     * clicks the "save"-button in every window.
     *
     * @param [object] btn - pressed Ext.button.Button
     * @event click
     * @return void
     */
    onSaveSettings: function (button, event) {
        var me = this,
            window = me.getMainWindow(),
            form = window.formPanel.getForm(),
            categoryModel = form.getRecord(),
            selectedNode = me.getController("Tree").getSelectedNode(),
            parentNode = selectedNode.parentNode || selectedNode;

        me.saveCategory(categoryModel, function(self, operation) {
            if (operation.success) {
                me.getSettingsForm().attributeForm.saveAttribute(categoryModel.get('id'));

                Shopware.Notification.createGrowlMessage('', me.snippets.onSaveChangesSuccess, me.snippets.growlMessage);
                me.subApplication.treeStore.load({ node: parentNode });
            } else {
                var rawData = self.proxy.reader.rawData;
                if (rawData.message) {
                    Shopware.Notification.createGrowlMessage('',me.snippets.onSaveChangesError + '<br>' +  rawData.message, me.snippets.growlMessage);
                } else {
                    Shopware.Notification.createGrowlMessage('', me.snippets.onSaveChangesError, me.snippets.growlMessage);
                }
            }
        });
    }
});
//{/block}
