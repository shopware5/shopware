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
 * @package    Workshop
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/workshop/view/main}

/**
 * Shopware UI - Media Manager Album Tree
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */
Ext.define('Shopware.apps.Workshop.view.resource.Tree', {
	extend: 'Ext.tree.Panel',
    alias: 'widget.workshop-resource-tree',
    region: 'west',
    width: 220,
    rootVisible: false,
    singleExpand: true,

    /**
     * Indicates if the toolbars should be created
     * @boolean
     */
    createToolbars: true,


    /**
     * Initializes the component and sets the toolbars
     * and the neccessary event listener
     *
     * @return void
     */
    initComponent: function() {
        var me = this;
        // Set column model and selection model
        me.columns = me.createColumns();
        me.bbar = me.createActionToolbar();
        me.callParent(arguments);
    },

    /**
     * Creates the action toolbar which includes the "add right"
     * and "delete right" buttons.
     *
     * @return [object] generated Ext.toolbar.Toolbar
     */
    createActionToolbar: function() {
        this.addBtn = Ext.create('Ext.button.Button', {
            text: 'Add',
            action: 'workshop-resource-tree-add'
        });
        this.editBtn = Ext.create('Ext.button.Button', {
            text: 'Edit',
            action: 'workshop-resource-tree-edit'
        });

        this.deleteBtn = Ext.create('Ext.button.Button', {
            text: 'Remove',
            action: 'workshop-resource-tree-delete',
            disabled: true
        });

        return Ext.create('Ext.toolbar.Toolbar', {
            height: 29,
            items: [
                this.addBtn,
                this.editBtn,
                this.deleteBtn
            ]
        });
    },

    /**
     * Creates the column model for the TreePanel
     *
     * @return [array] columns - generated columns
     */
    createColumns: function() {
        var me = this;

        var columns = [{
            xtype: 'treecolumn',
            text: 'Resources',
            flex: 2,
            sortable: true,
            dataIndex: 'name'
        }];

        return columns;
    }

});