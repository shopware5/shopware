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
 * @package    Supplier
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

/**
 * Shopware UI - Supplier Details
 *
 * This file represents the main window view
 */
//{block name="backend/supplier/view/main"}
Ext.define('Shopware.apps.Supplier.view.Main', {
    extend : 'Enlight.app.Window',
    layout : 'border',
    alias : 'widget.supplierGrid',
    width : 800,
    height : '90%',
    maximizable : true,
    minimizable: true,
    stateful : true,
    stateId : 'suppliersList',
    border : 0,
    title : '{s name=window_title}Supplier management{/s}',

    initComponent: function() {
        var me = this;

        me.items = [{
            xtype: 'supplier-main-toolbar',
            region: 'north'
        }, {
            xtype: 'supplier-main-list',
            region: 'center',
            supplierStore: me.supplierStore
        }, {
            xtype: 'supplier-main-detail',
            region: 'east'
        }];

        me.callParent(arguments);
    }
});
//{/block}
