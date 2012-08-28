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
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Media Manager Main Controller
 *
 * This file contains the business logic for the User Manager module. The module
 * handles the whole administration of the backend users.
 */

Ext.define('Shopware.apps.Workshop.controller.Resource', {

    /**
     * Extend from the standard ExtJS 4 controller
     * @string
     */
	extend: 'Ext.app.Controller',

    views: [ 'resource.Tree', 'resource.Window' ],

    stores: [ 'Resources' ],

    models: [ 'Resource', 'Privilege' ],


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
            'workshop-resource-window button[action=workshop-resource-save]': {
                click: me.onSaveResource
            },
            'workshop-resource-tree button[action=workshop-resource-tree-add]': {
                click: me.onAddResource
            },
            'workshop-resource-tree button[action=workshop-resource-tree-edit]': {
                click: me.onEditResource
            },
            'workshop-resource-tree button[action=workshop-resource-tree-delete]': {
                click: me.onDeleteResource
            }
        });

        me.callParent(arguments);
    },

    onSaveResource: function(btn) {
        var me = this,
            window = btn.up('window'),
            grid = window.down('gridpanel'),
            form = window.down('form'),
            values = form.getForm().getValues(),
            record = form.getRecord(),
            privStore = grid.getStore(),
            privileges = privStore.data.items;

        var data = Ext.Object.merge(record.data, values);
        data['privileges'] = privileges;
        var model = me.getModel('Resource').create(data);
        model.save();
        window.destory();
    },

    onAddResource: function(btn) {
        var me = this,
            model = me.getModel('Resource').create();

        me.getView('resource.Window').create({
            record: model
        });

    },

    onEditResource: function(btn) {
        var me = this,
            tree = btn.up('window').down('treepanel');

        var selModel = tree.getSelectionModel();
        var selected = selModel.getSelection();

        if (selected.length == 0) {
            return;
        }
        var model = selected[0];

        if (model.get('leaf') == true) {
            model = model.parentNode;
        }

        me.getView('resource.Window').create({
            record: model
        });
    },

    onDeleteResource: function(btn) {
        var me = this;
    }

});