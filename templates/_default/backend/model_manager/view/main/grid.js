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
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - ModelManager
 *
 * Defines the center grid, colums and checkboxmodel.
 * Also adds a paging toolbar to page through the entries.
 * This grid will hold all tables in the tablesStore.
 */
Ext.define('Shopware.apps.ModelManager.view.main.Grid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.model-manager-table-grid',
    ui : 'shopware-ui',
    store: 'Tables',
    /**
     * called on initialisation
     */
    initComponent: function() {
        this.dockedItems = this.initDockedItems();
        this.selModel = this.initSelModel();
        this.columns = this.initColumns();
        this.dockedItems = Ext.clone(this.dockedItems);
        this.callParent(arguments);
    },
    /**
     * get a checkbox selection model, so tables can be selected
     * enable/disable the necessary buttons upon selection
     */
    initSelModel: function() {
        var me = this;
        return Ext.create('Ext.selection.CheckboxModel', {
            listeners: {
                selectionchange: function(selModel) {
                    var button = me.up('window').down('button[action=createModels]');
                    button.setDisabled(selModel.getCount() < 1 );
                }
            }
        });
    },
    /**
     * get the columns
     */
    initColumns: function() {
        return [
            {
                header: 'Table Name',
                flex: 1,
                dataIndex: 'name',
                sortable: false,
                hideable: false,
                resizable: false
            }
        ];
    },
    /**
     * get the paging toolbar
     */
    initDockedItems: function() {
        return [
            {
                dock: 'bottom',
                xtype: 'pagingtoolbar',
                displayInfo: false,
                store: 'Tables',
                activePage: 2
            }
        ]
    }
});