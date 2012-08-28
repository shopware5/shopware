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
 * @package    ModelManager
 * @subpackage Controller
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - ModelManager
 *
 * This is the main controller for the model manager backend module.
 */
Ext.define('Shopware.apps.ModelManager.controller.Main', {

    /**
     * Extend from the standard ExtJS 4
     * @string
     */
    extend: 'Ext.app.Controller',
    /**
     * Required views for controller
     * @array
     */
    views: [ 'main.Window', 'main.Grid' ],
    /**
     * Required stores for controller
     * @array
     */
    stores: [ 'Tables', 'Code' ],
    /**
     * Required models for controller
     * @array
     */
    models: [ 'Tables', 'Code'],

    /**
     * called on initialistation
     */
    init: function() {
        var me = this;

        me.getView('main.Window').create();

        me.control({
            'model-manager-main-window button[action=createModels]': {
                click: me.createModels
            },
            'model-manager-main-window textfield[name=searchfield]': {
                change: me.searchTables
            }
        });

        me.callParent(arguments);
    },
    /**
     * iterates over the selected tables, handing their names to the store
     * which will in turn load the code for that table
     * generates a tab for each table, containing the generated code
     * @param btn
     */
    createModels: function(btn) {

        var me = this;
        var win = btn.up('window');
        var grid = win.down('grid');
        var tabPanel = win.down('tabpanel');
        var selModel = grid.getSelectionModel();
        var selection = selModel.getSelection();
        var codeStore = me.getStore('Code');

        Ext.each(selection, function(element) {
            codeStore.load({
                params: {
                    tableName: element['data']['name']
                },
                callback: function(records) {
                    var tab = Ext.create('Shopware.apps.ModelManager.view.main.Tab', {
                        record : records[0]
                    });
                    tabPanel.add(tab);
                }
            });
        });
    },
    /**
     * provides basic searchability for the center grid
     * @param field
     */
    searchTables: function(field) {
        var me = this;
        var tablesStore = me.getStore('Tables');
        var string = Ext.String.trim(field.getValue());

        tablesStore.currentPage = 1;

        tablesStore.getProxy().extraParams = {
            filter: string
        };

        tablesStore.load();

        return true;
    }
});
