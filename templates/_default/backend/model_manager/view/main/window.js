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
 * Defines the main window, its contents and a toolbar with the controls.
 */
Ext.define('Shopware.apps.ModelManager.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.model-manager-main-window',
    layout: 'border',
    border: 0,
    width: 800,
    height: 610,
    autoShow: true,
    maximizable: true,
    minimizable: true,
    stateful: true,
    stateId: 'modelManager',
    title: 'Model Manager',

    /**
     * called on initialisation
     */
    initComponent: function() {
        var me = this;
        me.tbar = me.getToolbar();
        me.items = me.getItems();
        me.callParent(arguments);
    },
    /**
     * get the tabpanel and the grid
     */
    getItems: function() {
        return [
            {
                xtype: 'tabpanel',
                region: 'east',
                flex: 2
            },
            {
                xtype: 'model-manager-table-grid',
                region: 'center'
            }
        ]
    },
    /**
     * create a toolbar with button and a searchfield
     */
    getToolbar: function() {
        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            region: 'north',
            items: [
                {
                    xtype: 'button',
                    text: 'Model erstellen',
                    action: 'createModels',
                    iconCls: 'sprite-light-bulb-code',
                    disabled: true
                },
                '->',
                {
                    xtype: 'textfield',
                    name: 'searchfield',
                    cls: 'searchfield',
                    width: 170,
                    enableKeyEvents: true,
                    emptyText : 'Suche',
                    checkChangeBuffer: 500
                }
            ]
        });
    }
});